<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ProjectTaskModel;
use App\Models\ProjectCommentModel;
use App\Models\ProjectEvidenceModel;
use App\Models\EvidenceCategoryModel;
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

        $taskModel->recalculateProjectProgress($projectId); // baru

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

        $taskModel->recalculateProjectProgress($task['project_id']); // baru

        return redirect()->to('/tasks/project/' . $task['project_id']);
    }

    public function delete(int $taskId)
    {
        $taskModel = new ProjectTaskModel();
        $task      = $taskModel->find($taskId);

        if ($task) {
            $taskModel->delete($taskId);
            $taskModel->recalculateProjectProgress($task['project_id']); // baru
        }

        return redirect()->to('/tasks/project/' . ($task['project_id'] ?? ''));
    }
    public function detail(int $taskId)
    {
        $taskModel = new ProjectTaskModel();
        $task      = $taskModel->find($taskId);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }

        $project = (new ProjectModel())->find($task['project_id']);

        return view('tasks/detail', [
            'title'      => 'Detail Task - ' . $task['title'],
            'task'       => $task,
            'project'    => $project,
            'comments'   => (new ProjectCommentModel())->getByTask($taskId),
            'evidences'  => (new ProjectEvidenceModel())->getByTask($taskId),
            'categories' => (new EvidenceCategoryModel())->findAll(),
        ]);
    }

    public function storeComment(int $taskId)
    {
        $taskModel = new ProjectTaskModel();
        $task      = $taskModel->find($taskId);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }

        $request      = service('request');
        $commentModel = new ProjectCommentModel();

        $commentModel->insert([
            'project_id' => $task['project_id'],
            'task_id'    => $taskId,
            'user_id'    => auth()->id(),
            'comment'    => $request->getPost('comment'),
        ]);

        return redirect()->to('/tasks/' . $taskId . '/detail');
    }

    public function storeEvidence(int $taskId)
    {
        $taskModel = new ProjectTaskModel();
        $task      = $taskModel->find($taskId);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }

        $request = service('request');
        $file    = $request->getFile('attachment');

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            session()->setFlashdata('error', 'File tidak valid.');
            return redirect()->to('/tasks/' . $taskId . '/detail');
        }

        if (! in_array($file->getClientMimeType(), allowed_attachment_mimes(), true)) {
            session()->setFlashdata('error', 'Tipe file tidak didukung. Boleh gambar, PDF, Word, Excel, PowerPoint, atau ZIP.).');
            return redirect()->to('/tasks/' . $taskId . '/detail');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            session()->setFlashdata('error', 'Ukuran file maksimal 10MB.');
            return redirect()->to('/tasks/' . $taskId . '/detail');
        }

        $newName    = $file->getRandomName();
        $uploadPath = FCPATH . 'uploads/evidence';

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $newName);

        (new ProjectEvidenceModel())->insert([
            'project_id'    => $task['project_id'],
            'task_id'       => $taskId,
            'category_id'   => $request->getPost('category_id'),
            'file_name'     => $newName,
            'original_name' => $file->getClientName(),
            'mime_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'caption'       => $request->getPost('caption'),
            'uploaded_by'   => auth()->id(),
        ]);

        session()->setFlashdata('success', 'Gambar berhasil diupload.');

        return redirect()->to('/tasks/' . $taskId . '/detail');
    }
}
