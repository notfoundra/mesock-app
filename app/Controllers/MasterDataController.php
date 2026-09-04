<?php

namespace App\Controllers;

use App\Models\AreaModel;
use App\Models\EvidenceCategoryModel;
use App\Models\PriorityModel;
use App\Models\ProjectCategoryModel;
use App\Models\ProjectStatusModel;
use App\Models\TeamModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class MasterDataController extends BaseController
{
    private function config(): array
    {
        return [
            'teams' => [
                'label'  => 'Teams',
                'model'  => TeamModel::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama Tim', 'type' => 'text', 'required' => true],
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text', 'required' => true],
                    ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea'],
                    ['name' => 'color', 'label' => 'Warna', 'type' => 'color'],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number'],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'switch'],
                ],
            ],
            'areas' => [
                'label'  => 'Areas',
                'model'  => AreaModel::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama Area', 'type' => 'text', 'required' => true],
                    ['name' => 'code', 'label' => 'Kode', 'type' => 'text', 'required' => true],
                    ['name' => 'group_area', 'label' => 'Grup Area', 'type' => 'text', 'required' => true],
                    ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea'],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'switch'],
                ],
            ],
            'categories' => [
                'label'  => 'Kategori Project',
                'model'  => ProjectCategoryModel::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama Kategori', 'type' => 'text', 'required' => true],
                    ['name' => 'color', 'label' => 'Warna', 'type' => 'color'],
                    ['name' => 'icon', 'label' => 'Icon (nucleo class)', 'type' => 'text'],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number'],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'switch'],
                ],
            ],
            'priorities' => [
                'label'  => 'Prioritas',
                'model'  => PriorityModel::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama Prioritas', 'type' => 'text', 'required' => true],
                    ['name' => 'color', 'label' => 'Warna', 'type' => 'color'],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number'],
                ],
            ],
            'statuses' => [
                'label'  => 'Status Project',
                'model'  => ProjectStatusModel::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama Status', 'type' => 'text', 'required' => true],
                    ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'required' => true],
                    ['name' => 'color', 'label' => 'Warna', 'type' => 'color'],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number'],
                ],
            ],
            'evidence-categories' => [
                'label'  => 'Kategori Evidence',
                'model'  => EvidenceCategoryModel::class,
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama Kategori', 'type' => 'text', 'required' => true],
                    ['name' => 'color', 'label' => 'Warna', 'type' => 'color'],
                ],
            ],
        ];
    }

    private function getConfigOrFail(string $type): array
    {
        $config = $this->config();

        if (! isset($config[$type])) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $config[$type];
    }

    private function collectPostData(array $fields): array
    {
        $request = service('request');
        $data    = [];

        foreach ($fields as $field) {
            $value = $request->getPost($field['name']);

            if ($field['type'] === 'switch') {
                $data[$field['name']] = $value ? 1 : 0;
            } elseif ($field['type'] === 'number') {
                $data[$field['name']] = ($value === null || $value === '') ? 1 : (int) $value;
            } else {
                $data[$field['name']] = $value;
            }
        }

        return $data;
    }

    public function index(string $type = 'teams')
    {
        $allConfig = $this->config();
        $config    = $this->getConfigOrFail($type);
        $modelClass = $config['model'];
        $model      = new $modelClass();

        $fieldNames = array_column($config['fields'], 'name');
        $orderCol   = in_array('sort_order', $fieldNames, true) ? 'sort_order' : 'name';

        return view('master/index', [
            'title'      => 'Konfigurasi Sistem - ' . $config['label'],
            'type'       => $type,
            'menuConfig' => $allConfig,
            'label'      => $config['label'],
            'fields'     => $config['fields'],
            'rows'       => $model->orderBy($orderCol, 'ASC')->findAll(),
        ]);
    }

    public function create(string $type)
    {
        $config = $this->getConfigOrFail($type);

        return view('master/form', [
            'title'  => 'Tambah ' . $config['label'],
            'type'   => $type,
            'fields' => $config['fields'],
            'row'    => [],
            'isEdit' => false,
        ]);
    }

    public function store(string $type)
    {
        $config     = $this->getConfigOrFail($type);
        $modelClass = $config['model'];
        $model      = new $modelClass();

        $data = $this->collectPostData($config['fields']);

        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        session()->setFlashdata('success', $config['label'] . ' berhasil ditambahkan.');

        return redirect()->to('/master/' . $type);
    }

    public function edit(string $type, int $id)
    {
        $config     = $this->getConfigOrFail($type);
        $modelClass = $config['model'];
        $model      = new $modelClass();

        $row = $model->find($id);

        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('master/form', [
            'title'  => 'Edit ' . $config['label'],
            'type'   => $type,
            'fields' => $config['fields'],
            'row'    => $row,
            'isEdit' => true,
        ]);
    }

    public function update(string $type, int $id)
    {
        $config     = $this->getConfigOrFail($type);
        $modelClass = $config['model'];
        $model      = new $modelClass();

        $data = $this->collectPostData($config['fields']);

        if (! $model->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        session()->setFlashdata('success', $config['label'] . ' berhasil diperbarui.');

        return redirect()->to('/master/' . $type);
    }

    public function delete(string $type, int $id)
    {
        $config     = $this->getConfigOrFail($type);
        $modelClass = $config['model'];
        $model      = new $modelClass();

        $model->delete($id);

        session()->setFlashdata('success', $config['label'] . ' berhasil dihapus.');

        return redirect()->to('/master/' . $type);
    }
}
