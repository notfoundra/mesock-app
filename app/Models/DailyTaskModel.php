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

    protected $allowedFields = ['template_id', 'team_id', 'title', 'task_date', 'is_done', 'done_by', 'done_at'];

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
                'task_date'   => $date,
                'is_done'     => false,
            ]);

            $created++;
        }

        return $created;
    }
}
