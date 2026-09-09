<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ProjectStatusModel;
use App\Models\TeamModel;

class PlanningController extends BaseController
{
    public function index()
    {
        $request = service('request');
        $teamId  = $request->getGet('team_id');

        $projectModel = new ProjectModel();
        $builder       = $projectModel->withDetails();

        if ($teamId) {
            $builder->where('projects.team_id', $teamId);
        }

        $projects = $builder->findAll();

        $events = [];

        foreach ($projects as $p) {
            if (empty($p['due_date'])) {
                continue;
            }

            $events[] = [
                'id'    => $p['id'],
                'title' => $p['project_code'] . ' - ' . $p['title'],
                'start' => $p['start_date'] ?: $p['due_date'],
                'end'   => date('Y-m-d', strtotime($p['due_date'] . ' +1 day')), // FullCalendar: end itu exclusive
                'color' => $p['status_color'] ?? '#5E72E4',
                'url'   => site_url('projects/' . $p['id']),
            ];
        }

        return view('planning/index', [
            'title'    => 'Deadline & Planning',
            'events'   => $events,
            'teams'    => (new TeamModel())->where('is_active', 1)->findAll(),
            'statuses' => (new ProjectStatusModel())->orderBy('sort_order', 'ASC')->findAll(),
            'filters'  => ['team_id' => $teamId ?? ''],
        ]);
    }
}
