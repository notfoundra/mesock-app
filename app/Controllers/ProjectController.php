<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\AreaModel;
use App\Models\PriorityModel;
use App\Models\ProjectCategoryModel;
use App\Models\ProjectCommentModel;
use App\Models\ProjectEvidenceModel;
use App\Models\ProjectMemberModel;
use App\Models\ProjectModel;
use App\Models\ProjectStatusModel;
use App\Models\TeamModel;
use App\Models\UserProfileModel;
use App\Models\ProjectMilestoneModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ProjectController extends BaseController
{
    public function index()
    {
        $request      = service('request');
        $projectModel = new ProjectModel();

        $teamId   = $request->getGet('team_id');
        $statusId = $request->getGet('status_id');
        $keyword  = $request->getGet('q');

        $builder = $projectModel->withDetails();

        if ($teamId) {
            $builder->where('projects.team_id', $teamId);
        }
        if ($statusId) {
            $builder->where('projects.status_id', $statusId);
        }
        if ($keyword) {
            $builder->groupStart()
                ->like('projects.title', $keyword)
                ->orLike('projects.project_code', $keyword)
                ->groupEnd();
        }

        $projects = $builder->orderBy('projects.due_date', 'ASC')->findAll();

        foreach ($projects as $i => $p) {
            $projects[$i]['is_overdue'] = $projectModel->isOverdue($p);
        }

        return view('projects/index', [
            'title'    => 'Semua Project',
            'projects' => $projects,
            'teams'    => (new TeamModel())->where('is_active', 1)->findAll(),
            'statuses' => (new ProjectStatusModel())->orderBy('sort_order', 'ASC')->findAll(),
            'filters'  => [
                'team_id'   => $teamId ?? '',
                'status_id' => $statusId ?? '',
                'q'         => $keyword ?? '',
            ],
        ]);
    }

    private function formData(): array
    {
        return [
            'teams'      => (new TeamModel())->where('is_active', 1)->findAll(),
            'areas'      => (new AreaModel())->where('is_active', 1)->findAll(),
            'categories' => (new ProjectCategoryModel())->where('is_active', 1)->findAll(),
            'priorities' => (new PriorityModel())->orderBy('sort_order', 'ASC')->findAll(),
            'statuses'   => (new ProjectStatusModel())->orderBy('sort_order', 'ASC')->findAll(),
        ];
    }

    public function create()
    {
        return view('projects/form', array_merge([
            'title'  => 'Tambah Project',
            'row'    => [],
            'isEdit' => false,
        ], $this->formData()));
    }

    public function store()
    {
        $request      = service('request');
        $projectModel = new ProjectModel();

        $data = [
            'project_code' => $request->getPost('project_code'),
            'title'        => $request->getPost('title'),
            'description'  => $request->getPost('description'),
            'team_id'      => $request->getPost('team_id'),
            'area_id'      => $request->getPost('area_id'),
            'category_id'  => $request->getPost('category_id'),
            'priority_id'  => $request->getPost('priority_id'),
            'status_id'    => $request->getPost('status_id'),
            'start_date'   => $request->getPost('start_date') ?: null,
            'due_date'     => $request->getPost('due_date') ?: null,
            'created_by'   => auth()->id(),
        ];

        if (! $projectModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $projectModel->errors());
        }

        $newId = $projectModel->getInsertID();
        (new ActivityLogModel())->record($newId, auth()->id(), 'Membuat project baru', null, $data['title']);

        session()->setFlashdata('success', 'Project berhasil dibuat.');

        return redirect()->to('/projects');
    }

    public function edit(int $id)
    {
        $projectModel = new ProjectModel();
        $row          = $projectModel->find($id);

        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('projects/form', array_merge([
            'title'  => 'Edit Project',
            'row'    => $row,
            'isEdit' => true,
        ], $this->formData()));
    }

    public function update(int $id)
    {
        $request      = service('request');
        $projectModel = new ProjectModel();
        $before       = $projectModel->find($id);

        $data = [
            'project_code' => $request->getPost('project_code'),
            'title'        => $request->getPost('title'),
            'description'  => $request->getPost('description'),
            'team_id'      => $request->getPost('team_id'),
            'area_id'      => $request->getPost('area_id'),
            'category_id'  => $request->getPost('category_id'),
            'priority_id'  => $request->getPost('priority_id'),
            'status_id'    => $request->getPost('status_id'),
            'start_date'   => $request->getPost('start_date') ?: null,
            'due_date'     => $request->getPost('due_date') ?: null,
        ];

        if (! $projectModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $projectModel->errors());
        }

        if ($before && $before['status_id'] != $data['status_id']) {
            $statusModel = new ProjectStatusModel();
            (new ActivityLogModel())->record(
                $id,
                auth()->id(),
                'Mengubah status project (edit form)',
                $statusModel->find($before['status_id'])['name'] ?? '-',
                $statusModel->find($data['status_id'])['name'] ?? '-'
            );
        }

        session()->setFlashdata('success', 'Project berhasil diperbarui.');

        return redirect()->to('/projects');
    }

    public function delete(int $id)
    {
        (new ProjectModel())->delete($id);

        session()->setFlashdata('success', 'Project berhasil dihapus.');

        return redirect()->to('/projects');
    }

    public function detail(int $id)
    {
        $projectModel = new ProjectModel();
        $project      = $projectModel->withDetails()->where('projects.id', $id)->first();

        if (! $project) {
            throw PageNotFoundException::forPageNotFound();
        }

        $project['is_overdue'] = $projectModel->isOverdue($project);

        $memberModel   = new ProjectMemberModel();
        $members       = $memberModel->getMembers($id);
        $memberUserIds = array_column($members, 'user_id');

        $availableUsers = array_filter(
            (new UserProfileModel())->findAll(),
            static fn($p) => ! in_array($p['user_id'], $memberUserIds, true)
        );

        $milestoneModel = new ProjectMilestoneModel();
        $milestones     = $milestoneModel->getByProject($id);

        foreach ($milestones as $i => $ms) {
            $milestones[$i]['is_overdue'] = $milestoneModel->isOverdue($ms);
        }

        return view('projects/detail', [
            'title'          => $project['title'],
            'project'        => $project,
            'members'        => $members,
            'availableUsers' => $availableUsers,
            'milestones'     => $milestones, // baru
            'evidences'      => (new ProjectEvidenceModel())->getGallery($id),
            'comments'       => (new ProjectCommentModel())->getByProject($id),
            'activities' => (new ActivityLogModel())->getByProject($id),
        ]);
    }

    public function addMember(int $projectId)
    {
        $request     = service('request');
        $memberModel = new ProjectMemberModel();

        $data = [
            'project_id' => $projectId,
            'user_id'    => $request->getPost('user_id'),
            'role'       => $request->getPost('role'),
        ];

        if (! $memberModel->insert($data)) {
            session()->setFlashdata('error', 'Gagal menambahkan member (mungkin sudah terdaftar).');
        } else {
            session()->setFlashdata('success', 'Member berhasil ditambahkan.');
        }

        return redirect()->to('/projects/' . $projectId);
    }

    public function removeMember(int $projectId, int $memberId)
    {
        (new ProjectMemberModel())->delete($memberId);

        session()->setFlashdata('success', 'Member berhasil dihapus.');

        return redirect()->to('/projects/' . $projectId);
    }
    public function addMilestone(int $projectId)
    {
        $request        = service('request');
        $milestoneModel = new ProjectMilestoneModel();

        $data = [
            'project_id'  => $projectId,
            'title'       => $request->getPost('title'),
            'description' => $request->getPost('description'),
            'target_date' => $request->getPost('target_date') ?: null,
            'sort_order'  => (int) ($request->getPost('sort_order') ?: 1),
        ];

        if (! $milestoneModel->insert($data)) {
            session()->setFlashdata('error', 'Gagal menambahkan milestone.');
        } else {
            session()->setFlashdata('success', 'Milestone berhasil ditambahkan.');
        }

        return redirect()->to('/projects/' . $projectId);
    }

    public function toggleMilestone(int $projectId, int $milestoneId)
    {
        $milestoneModel = new ProjectMilestoneModel();
        $milestone      = $milestoneModel->find($milestoneId);

        if ($milestone) {
            $milestone['is_done'] ? $milestoneModel->markUndone($milestoneId) : $milestoneModel->markDone($milestoneId);
        }

        return redirect()->to('/projects/' . $projectId);
    }

    public function deleteMilestone(int $projectId, int $milestoneId)
    {
        (new ProjectMilestoneModel())->delete($milestoneId);

        return redirect()->to('/projects/' . $projectId);
    }

    public function timeline(int $id)
    {
        $projectModel = new ProjectModel();
        $project      = $projectModel->withDetails()->where('projects.id', $id)->first();

        if (! $project) {
            throw PageNotFoundException::forPageNotFound();
        }

        $project['is_overdue'] = $projectModel->isOverdue($project);

        $milestoneModel = new ProjectMilestoneModel();
        $milestones     = $milestoneModel->getByProject($id);

        foreach ($milestones as $i => $ms) {
            $milestones[$i]['is_overdue'] = $milestoneModel->isOverdue($ms);
        }

        return view('projects/timeline', [
            'title'      => 'Timeline - ' . $project['title'],
            'project'    => $project,
            'milestones' => $milestones,
            'activities' => (new ActivityLogModel())->getByProject($id),
        ]);
    }
    public function storeComment(int $projectId)
    {
        (new ProjectCommentModel())->insert([
            'project_id' => $projectId,
            'user_id'    => auth()->id(),
            'comment'    => service('request')->getPost('comment'),
        ]);

        return redirect()->to('/projects/' . $projectId);
    }
}
