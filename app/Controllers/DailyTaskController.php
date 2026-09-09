<?php

namespace App\Controllers;

use App\Models\DailyTaskModel;
use App\Models\TaskTemplateModel;
use App\Models\TeamModel;
use App\Models\UserProfileModel;
use App\Models\EvidenceCategoryModel;
use App\Models\ProjectCommentModel;
use App\Models\ProjectEvidenceModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class DailyTaskController extends BaseController
{
    public function index()
    {
        $date = service('request')->getGet('date') ?: date('Y-m-d');

        $dailyModel = new DailyTaskModel();
        $dailyModel->generateForDate($date); // safety net kalau cron belum jalan

        $profileModel = new UserProfileModel();
        $profile      = $profileModel->getByUserId(auth()->id());
        $teamId       = $profile['team_id'] ?? null;

        $tasks = $teamId
            ? $dailyModel->getByTeamAndDate((int) $teamId, $date)
            : $dailyModel->where('task_date', $date)->findAll();

        return view('tasks/daily', [
            'title' => 'Checklist Harian',
            'date'  => $date,
            'tasks' => $tasks,
        ]);
    }

    public function toggle(int $id)
    {
        $dailyModel = new DailyTaskModel();
        $task       = $dailyModel->find($id);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }

        $task['is_done'] ? $dailyModel->markUndone($id) : $dailyModel->markDone($id, auth()->id());

        return redirect()->to('/tasks/daily?date=' . $task['task_date']);
    }

    public function templates()
    {
        $templateModel = new TaskTemplateModel();
        $teamModel     = new TeamModel();

        if (session()->getFlashdata('success')) {
            // ditampilkan di view
        }

        return view('tasks/templates', [
            'title'     => 'Template Task Harian',
            'templates' => $templateModel->getAllWithTeam(),
            'teams'     => $teamModel->where('is_active', 1)->findAll(),
        ]);
    }

    public function templateStore()
    {
        $templateModel = new TaskTemplateModel();
        $request       = service('request');

        $data = [
            'team_id'     => $request->getPost('team_id'),
            'title'       => $request->getPost('title'),
            'description' => $request->getPost('description'),
            'is_active'   => 1,
        ];

        if (! $templateModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $templateModel->errors());
        }

        session()->setFlashdata('success', 'Template berhasil ditambahkan.');

        return redirect()->to('/tasks/daily/templates');
    }

    public function templateToggle(int $id)
    {
        $templateModel = new TaskTemplateModel();
        $template      = $templateModel->find($id);

        if ($template) {
            $templateModel->update($id, ['is_active' => $template['is_active'] ? 0 : 1]);
        }

        return redirect()->to('/tasks/daily/templates');
    }

    public function templateDelete(int $id)
    {
        (new TaskTemplateModel())->delete($id);

        return redirect()->to('/tasks/daily/templates');
    }
    public function detail(int $id)
    {
        $dailyModel = new DailyTaskModel();
        $task       = $dailyModel->find($id);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }
        return view('tasks/daily_detail', [
            'title'      => 'Detail Task Harian - ' . $task['title'],
            'task'       => $task,
            'comments'   => (new ProjectCommentModel())->getByDailyTask($id),
            'evidences'  => (new ProjectEvidenceModel())->getByDailyTask($id),
            'categories' => (new EvidenceCategoryModel())->findAll(),
        ]);
    }

    public function storeComment(int $id)
    {
        $dailyModel = new DailyTaskModel();
        $task       = $dailyModel->find($id);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }

        (new ProjectCommentModel())->insert([
            'daily_task_id' => $id,
            'user_id'       => auth()->id(),
            'comment'       => service('request')->getPost('comment'),
        ]);

        return redirect()->to('/tasks/daily/' . $id . '/detail');
    }

    public function storeEvidence(int $id)
    {
        $dailyModel = new DailyTaskModel();
        $task       = $dailyModel->find($id);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }

        $request = service('request');
        $file    = $request->getFile('attachment');

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            session()->setFlashdata('error', 'File tidak valid.');
            return redirect()->to('/tasks/daily/' . $taskId . '/detail');
        }

        if (! in_array($file->getClientMimeType(), allowed_attachment_mimes(), true)) {
            session()->setFlashdata('error', 'Tipe file tidak didukung. Boleh gambar, PDF, Word, Excel, PowerPoint, atau ZIP.).');
            return redirect()->to('/tasks/daily/' . $taskId . '/detail');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            session()->setFlashdata('error', 'Ukuran file maksimal 10MB.');
            return redirect()->to('/tasks/daily/' . $taskId . '/detail');
        }

        $newName    = $file->getRandomName();
        $uploadPath = FCPATH . 'uploads/evidence';

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $newName);

        (new ProjectEvidenceModel())->insert([
            'daily_task_id' => $id,
            'category_id'   => $request->getPost('category_id'),
            'file_name'     => $newName,
            'original_name' => $file->getClientName(),
            'mime_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'caption'       => $request->getPost('caption'),
            'uploaded_by'   => auth()->id(),
        ]);

        session()->setFlashdata('success', 'Gambar berhasil diupload.');

        return redirect()->to('/tasks/daily/' . $id . '/detail');
    }
}
