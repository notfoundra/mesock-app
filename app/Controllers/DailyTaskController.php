<?php

namespace App\Controllers;

use App\Models\DailyTaskModel;
use App\Models\EvidenceCategoryModel;
use App\Models\ProjectCommentModel;
use App\Models\ProjectEvidenceModel;
use App\Models\TaskTemplateModel;
use App\Models\TeamModel;
use App\Models\UserProfileModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class DailyTaskController extends BaseController
{
    public function index()
    {
        $request = service('request');
        $date    = $request->getGet('date') ?: date('Y-m-d');

        $dailyModel = new DailyTaskModel();
        $dailyModel->generateForDate($date); // safety net kalau cron belum jalan

        $profileModel = new UserProfileModel();
        $profile      = $profileModel->getByUserId(auth()->id());
        $myTeamId     = $profile['team_id'] ?? null;

        $isSuper = is_super_team();
        $teamId  = $isSuper ? $request->getGet('team_id') : $myTeamId;

        return view('tasks/daily', [
            'title'          => 'Checklist Harian',
            'date'           => $date,
            'tasks'          => $dailyModel->getByDateWithTeam($date, $teamId ?: null),
            'isSuperTeam'    => $isSuper,
            'teams'          => $isSuper ? (new TeamModel())->where('is_active', 1)->findAll() : [],
            'selectedTeamId' => $teamId,
        ]);
    }
    public function history()
    {
        $request = service('request');

        $profileModel = new UserProfileModel();
        $profile      = $profileModel->getByUserId(auth()->id());
        $myTeamId     = $profile['team_id'] ?? null;

        $isSuper = is_super_team();

        $filters = [
            'team_id'   => $isSuper ? $request->getGet('team_id') : $myTeamId,
            'date_from' => $request->getGet('date_from'),
            'date_to'   => $request->getGet('date_to'),
            'is_done'   => $request->getGet('is_done'),
            'keyword'   => $request->getGet('keyword'),
        ];

        $dailyModel = new DailyTaskModel();
        $history    = $dailyModel->getHistory($filters, 20);

        $commentCounts = (new ProjectCommentModel())->countByDailyTaskIds(array_column($history, 'id'));

        return view('tasks/daily_history', [
            'title'         => 'History Checklist Harian',
            'history'       => $history,
            'pager'         => $dailyModel->pager,
            'filters'       => $filters,
            'commentCounts' => $commentCounts,
            'isSuperTeam'   => $isSuper,
            'teams'         => (new TeamModel())->where('is_active', 1)->findAll(),
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
        $request      = service('request');
        $dailyModel = new DailyTaskModel();
        $task       = $dailyModel->find($id);

        if (! $task) {
            throw PageNotFoundException::forPageNotFound();
        }

        (new ProjectCommentModel())->insert([
            'daily_task_id' => $id,
            'user_id'       => auth()->id(),
            'comment' => clean_comment_html($request->getPost('comment')),
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
    public function exportPdf()
    {
        $request = service('request');
        $date    = $request->getGet('date') ?: date('Y-m-d');

        $profileModel = new UserProfileModel();
        $profile      = $profileModel->getByUserId(auth()->id());
        $myTeamId     = $profile['team_id'] ?? null;

        $isSuper = is_super_team();
        $teamId  = $isSuper ? $request->getGet('team_id') : $myTeamId;

        $dailyModel = new DailyTaskModel();
        $tasks      = $dailyModel->getByDateWithTeam($date, $teamId ?: null);

        $commentModel  = new ProjectCommentModel();
        $evidenceModel = new ProjectEvidenceModel();

        $taskComments  = [];
        $taskEvidences = [];

        foreach ($tasks as $t) {
            $taskComments[$t['id']]  = $commentModel->getByDailyTask($t['id']);
            $taskEvidences[$t['id']] = $evidenceModel->getByDailyTask($t['id']);
        }

        $teamName = null;
        if ($teamId) {
            $team     = (new TeamModel())->find($teamId);
            $teamName = $team['name'] ?? null;
        }

        $html = view('tasks/daily_pdf_report', [
            'date'          => $date,
            'teamName'      => $teamName,
            'tasks'         => $tasks,
            'taskComments'  => $taskComments,
            'taskEvidences' => $taskEvidences,
            'generatedAt'   => date('d M Y H:i'),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', realpath(FCPATH));

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $filename = 'Checklist-Harian-' . ($teamName ? preg_replace('/[^A-Za-z0-9\-]/', '-', $teamName) . '-' : '') . $date . '.pdf';

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output())
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
