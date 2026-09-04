<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\AreaModel;
use App\Models\PriorityModel;
use App\Models\ProjectCategoryModel;
use App\Models\ProjectModel;
use App\Models\ProjectStatusModel;
use App\Models\TeamModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $projectModel = new ProjectModel();
        $statusModel  = new ProjectStatusModel();

        $statuses = $statusModel->orderBy('sort_order', 'ASC')->findAll();
        $board    = $projectModel->getKanbanData();

        foreach ($board as $statusId => $projects) {
            foreach ($projects as $i => $project) {
                $board[$statusId][$i]['is_overdue'] = $projectModel->isOverdue($project);
            }
        }

        $allProjects = empty($board) ? [] : array_merge(...array_values($board));

        return view('dashboard/index', [
            'title'         => 'Dashboard',
            'statuses'      => $statuses,
            'board'         => $board,
            'totalProjects' => count($allProjects),
            'overdueCount'  => count(array_filter($allProjects, static fn($p) => $p['is_overdue'])),
            'doneCount'     => count(array_filter($allProjects, static fn($p) => ($p['status_slug'] ?? '') === 'done')),
            'avgProgress'   => $allProjects ? round(array_sum(array_column($allProjects, 'progress')) / count($allProjects), 1) : 0,
            'teams'         => (new TeamModel())->where('is_active', 1)->findAll(),
            'areas'         => (new AreaModel())->where('is_active', 1)->findAll(),
            'categories'    => (new ProjectCategoryModel())->where('is_active', 1)->findAll(),
            'priorities'    => (new PriorityModel())->orderBy('sort_order', 'ASC')->findAll(),
        ]);
    }

    public function updateStatus()
    {
        $request   = service('request');
        $projectId = (int) $request->getPost('project_id');
        $statusId  = (int) $request->getPost('status_id');

        $projectModel = new ProjectModel();
        $statusModel  = new ProjectStatusModel();
        $logModel     = new ActivityLogModel();

        $project = $projectModel->find($projectId);
        $status  = $statusModel->find($statusId);

        if (! $project || ! $status) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ]);
        }

        $updateData = ['status_id' => $statusId];

        if ($status['slug'] === 'done') {
            $updateData['progress']     = 100;
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        }

        $projectModel->update($projectId, $updateData);

        $oldStatus = $statusModel->find($project['status_id']);

        $logModel->record(
            $projectId,
            auth()->id(),
            'Mengubah status project',
            $oldStatus['name'] ?? '-',
            $status['name']
        );

        return $this->response->setJSON([
            'success'  => true,
            'csrfHash' => csrf_hash(), // token baru buat drag berikutnya
        ]);
    }

    public function quickCreate()
    {
        $request     = service('request');
        $statusModel = new ProjectStatusModel();
        $firstStatus = $statusModel->orderBy('sort_order', 'ASC')->first();

        $projectModel = new ProjectModel();

        $data = [
            'project_code' => $request->getPost('project_code'),
            'title'        => $request->getPost('title'),
            'team_id'      => $request->getPost('team_id'),
            'area_id'      => $request->getPost('area_id'),
            'category_id'  => $request->getPost('category_id'),
            'priority_id'  => $request->getPost('priority_id'),
            'status_id'    => $firstStatus['id'] ?? null,
            'due_date'     => $request->getPost('due_date') ?: null,
            'created_by'   => auth()->id(),
        ];

        if (! $projectModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $projectModel->errors());
        }

        $newId = $projectModel->getInsertID();
        (new ActivityLogModel())->record($newId, auth()->id(), 'Membuat project baru', null, $data['title']);

        session()->setFlashdata('success', 'Project berhasil dibuat.');

        return redirect()->to('/dashboard');
    }
}
