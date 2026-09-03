<?php
// app/Models/ProjectModel.php
namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'project_code',
        'title',
        'description',
        'team_id',
        'area_id',
        'category_id',
        'priority_id',
        'status_id',
        'progress',
        'start_date',
        'due_date',
        'completed_at',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'project_code' => 'required|max_length[30]|is_unique[projects.project_code,id,{id}]',
        'title'        => 'required|max_length[200]',
        'team_id'      => 'required|is_natural_no_zero',
        'area_id'      => 'required|is_natural_no_zero',
        'category_id'  => 'required|is_natural_no_zero',
        'priority_id'  => 'required|is_natural_no_zero',
        'status_id'    => 'required|is_natural_no_zero',
        'due_date'     => 'permit_empty|valid_date',
    ];

    /**
     * Ambil project + nama-nama relasi (buat listing/kanban).
     */
    public function withDetails()
    {
        return $this->select('
                projects.*,
                teams.name as team_name, teams.color as team_color,
                areas.name as area_name,
                project_categories.name as category_name, project_categories.color as category_color,
                priorities.name as priority_name, priorities.color as priority_color,
                project_statuses.name as status_name, project_statuses.slug as status_slug, project_statuses.color as status_color
            ')
            ->join('teams', 'teams.id = projects.team_id', 'left')
            ->join('areas', 'areas.id = projects.area_id', 'left')
            ->join('project_categories', 'project_categories.id = projects.category_id', 'left')
            ->join('priorities', 'priorities.id = projects.priority_id', 'left')
            ->join('project_statuses', 'project_statuses.id = projects.status_id', 'left');
    }

    /**
     * Data siap pakai buat Kanban board, dikelompokkan per status_id.
     */
    public function getKanbanData(): array
    {
        $board = [];
        foreach ($this->withDetails()->orderBy('projects.due_date', 'ASC')->findAll() as $project) {
            $board[$project['status_id']][] = $project;
        }

        return $board;
    }

    public function isOverdue(array $project): bool
    {
        if (empty($project['due_date']) || ($project['status_slug'] ?? '') === 'done') {
            return false;
        }

        return strtotime($project['due_date']) < strtotime(date('Y-m-d'));
    }
}
