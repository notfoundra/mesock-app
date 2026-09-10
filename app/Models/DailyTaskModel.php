<?php

namespace App\Models;

use CodeIgniter\Model;

class DailyTaskModel extends Model
{
    protected $table            = 'daily_tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields = ['template_id', 'team_id', 'title', 'description', 'task_date', 'is_done', 'done_by', 'done_at'];
    protected $validationRules = [
        'template_id' => 'required|is_natural_no_zero',
        'team_id'     => 'required|is_natural_no_zero',
        'title'       => 'required',
        'task_date'   => 'required|valid_date',
    ];

    public function getByTeamAndDate(int $teamId, string $date): array
    {
        return $this->where('team_id', $teamId)->where('task_date', $date)->orderBy('id', 'ASC')->findAll();
    }

    public function markDone(int $id, int $userId): bool
    {
        return $this->update($id, ['is_done' => true, 'done_by' => $userId, 'done_at' => date('Y-m-d H:i:s')]);
    }

    public function markUndone(int $id): bool
    {
        return $this->update($id, ['is_done' => false, 'done_by' => null, 'done_at' => null]);
    }

    /**
     * Generate daily_tasks dari semua template aktif untuk tanggal tertentu.
     * Idempotent — aman dipanggil berkali-kali, gak bakal duplikat (unique key template_id+task_date).
     */
    public function generateForDate(string $date): int
    {
        $templateModel = new TaskTemplateModel();
        $templates     = $templateModel->where('is_active', 1)->findAll();
        $created       = 0;

        foreach ($templates as $template) {
            $exists = $this->where('template_id', $template['id'])->where('task_date', $date)->first();

            if ($exists) {
                continue;
            }

            $this->insert([
                'template_id' => $template['id'],
                'team_id'     => $template['team_id'],
                'title'       => $template['title'],
                'description' => $template['description'] ?? null,
                'task_date'   => $date,
                'is_done'     => false,
            ]);

            $created++;
        }

        return $created;
    }
    public function getByDateWithTeam(string $date, ?int $teamId = null): array
    {
        $builder = $this->select('daily_tasks.*, teams.name as team_name')
            ->join('teams', 'teams.id = daily_tasks.team_id', 'left')
            ->where('daily_tasks.task_date', $date);

        if ($teamId) {
            $builder->where('daily_tasks.team_id', $teamId);
        }

        return $builder->orderBy('teams.name', 'ASC')->orderBy('daily_tasks.id', 'ASC')->findAll();
    }

    public function getHistory(array $filters = [], int $perPage = 20)
    {
        $builder = $this->select('
            daily_tasks.*,
            teams.name as team_name,
            user_profiles.fullname as done_by_name
        ')
            ->join('teams', 'teams.id = daily_tasks.team_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = daily_tasks.done_by', 'left');

        if (! empty($filters['team_id'])) {
            $builder->where('daily_tasks.team_id', $filters['team_id']);
        }
        if (! empty($filters['date_from'])) {
            $builder->where('daily_tasks.task_date >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('daily_tasks.task_date <=', $filters['date_to']);
        }
        if (isset($filters['is_done']) && $filters['is_done'] !== '') {
            $builder->where('daily_tasks.is_done', $filters['is_done']);
        }
        if (! empty($filters['keyword'])) {
            $builder->like('daily_tasks.title', $filters['keyword']);
        }

        return $builder->orderBy('daily_tasks.task_date', 'DESC')->orderBy('daily_tasks.id', 'DESC')->paginate($perPage);
    }
}
