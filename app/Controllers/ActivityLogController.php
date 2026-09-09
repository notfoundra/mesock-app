<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\ProjectModel;
use App\Models\UserProfileModel;

class ActivityLogController extends BaseController
{
    public function index()
    {
        $request = service('request');

        $filters = [
            'project_id' => $request->getGet('project_id'),
            'user_id'    => $request->getGet('user_id'),
            'date_from'  => $request->getGet('date_from'),
            'date_to'    => $request->getGet('date_to'),
        ];

        $logModel = new ActivityLogModel();
        $logs     = $logModel->getFiltered($filters, 20);

        return view('activity_logs/index', [
            'title'    => 'Audit Sistem',
            'logs'     => $logs,
            'pager'    => $logModel->pager,
            'filters'  => $filters,
            'projects' => (new ProjectModel())->select('id, project_code, title')->findAll(),
            'users'    => (new UserProfileModel())->findAll(),
        ]);
    }
}
