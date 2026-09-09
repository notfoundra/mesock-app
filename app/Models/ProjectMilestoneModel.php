<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectMilestoneModel extends Model
{
    protected $table            = 'project_milestones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = ['project_id', 'title', 'description', 'target_date', 'is_done', 'completed_at', 'sort_order'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'title'      => 'required|max_length[200]',
    ];

    public function getByProject(int $projectId): array
    {
        return $this->where('project_id', $projectId)
            ->orderBy('target_date', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function markDone(int $id): bool
    {
        return $this->update($id, ['is_done' => true, 'completed_at' => date('Y-m-d H:i:s')]);
    }

    public function markUndone(int $id): bool
    {
        return $this->update($id, ['is_done' => false, 'completed_at' => null]);
    }

    public function isOverdue(array $milestone): bool
    {
        if (empty($milestone['target_date']) || $milestone['is_done']) {
            return false;
        }

        return strtotime($milestone['target_date']) < strtotime(date('Y-m-d'));
    }
}
