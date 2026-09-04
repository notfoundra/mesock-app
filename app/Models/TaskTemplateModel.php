<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskTemplateModel extends Model
{
    protected $table            = 'task_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = ['team_id', 'title', 'description', 'is_active'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'team_id' => 'required|is_natural_no_zero',
        'title'   => 'required|max_length[200]',
    ];

    public function getAllWithTeam(): array
    {
        return $this->select('task_templates.*, teams.name as team_name')
            ->join('teams', 'teams.id = task_templates.team_id', 'left')
            ->orderBy('teams.name', 'ASC')
            ->findAll();
    }
}
