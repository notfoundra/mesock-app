<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ProjectTaskModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class TaskController extends BaseController
{
    public function index()
    {
        $projectModel = new ProjectModel();

        return view('tasks/index', [
            'title'    => 'Checklist Pekerjaan',
            'projects' => $projectModel->withDetails()->orderBy('projects.due_date', 'ASC')->findAll(),
        ]);
    }

    public function project(int $projectId)
    {
        $projectModel = new ProjectModel();
        $taskModel    = new ProjectTaskModel();
        $project      = $projectModel->find($projectId);

        if (! $project) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('tasks/project', [
            'title'   => 'Checklist - ' . $project['title'],
            'project' => $project,
            'tasks'   => $taskModel->getTree($projectId),
        ]);
    }

    public function store(int $projectId)
    {
        $taskModel = new ProjectTaskModel();
        $request   = service('request');

        $data = [
            'project_id'  => $projectId,
            'parent_id'   => $request->getPost('parent_id') ?: null,
            'title'       => $request->getPost('title'),
            'description' => $request->getPost('description'),
            'order_no'    => (int) ($request->getPost('order_no') ?: 1),
        ];

        if (! $taskModel->insert($data)) {
            return redirect()->back()->with('errors', $taskModel->errors());
        }

        return redirect()->to('/tasks/project/' . $projectId);
    }

    public function toggle(int $taskId)
    {
        $taskModel = new ProjectTaskModel();
        $task      = $taskModel->find($taskId);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }

        $task['is_done'] ? $taskModel->markUndone($taskId) : $taskModel->markDone($taskId, auth()->id());

        return redirect()->to('/tasks/project/' . $task['project_id']);
    }

    public function delete(int $taskId)
    {
        $taskModel = new ProjectTaskModel();
        $task      = $taskModel->find($taskId);

        if ($task) {
            $taskModel->delete($taskId);
        }

        return redirect()->to('/tasks/project/' . ($task['project_id'] ?? ''));
    }
}
