<?php

namespace App\Controllers;

use App\Models\DailyTaskModel;
use App\Models\TaskTemplateModel;
use App\Models\TeamModel;
use App\Models\UserProfileModel;
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
}
