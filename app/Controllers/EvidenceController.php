<?php

namespace App\Controllers;

use App\Models\EvidenceCategoryModel;
use App\Models\ProjectEvidenceModel;
use App\Models\ProjectModel;

class EvidenceController extends BaseController
{
    public function index()
    {
        $request = service('request');

        $filters = [
            'project_id'  => $request->getGet('project_id'),
            'category_id' => $request->getGet('category_id'),
            'source'      => $request->getGet('source'),
            'date_from'   => $request->getGet('date_from'),
            'date_to'     => $request->getGet('date_to'),
        ];

        $evidenceModel = new ProjectEvidenceModel();
        $evidences     = $evidenceModel->getFiltered($filters, 12);

        return view('evidence/index', [
            'title'      => 'Bukti Pekerjaan',
            'evidences'  => $evidences,
            'pager'      => $evidenceModel->pager,
            'filters'    => $filters,
            'projects'   => (new ProjectModel())->select('id, project_code, title')->findAll(),
            'categories' => (new EvidenceCategoryModel())->findAll(),
        ]);
    }

    public function delete(int $id)
    {
        $evidenceModel = new ProjectEvidenceModel();
        $evidence      = $evidenceModel->find($id);

        if ($evidence) {
            $filePath = FCPATH . 'uploads/evidence/' . $evidence['file_name'];

            if (is_file($filePath)) {
                unlink($filePath);
            }

            $evidenceModel->delete($id);
        }

        session()->setFlashdata('success', 'Bukti pekerjaan berhasil dihapus.');

        return redirect()->to('/evidence');
    }
}
