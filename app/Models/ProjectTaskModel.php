<?php
// app/Models/ProjectTaskModel.php
namespace App\Models;

use CodeIgniter\Model;

class ProjectTaskModel extends Model
{
    protected $table            = 'project_tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'project_id',
        'parent_id',
        'title',
        'description',
        'order_no',
        'is_done',
        'done_by',
        'done_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'title'      => 'required|max_length[255]',
    ];

    public function getByProject(int $projectId): array
    {
        return $this->where('project_id', $projectId)
            ->orderBy('order_no', 'ASC')
            ->findAll();
    }

    public function markDone(int $taskId, int $userId): bool
    {
        return $this->update($taskId, [
            'is_done' => true,
            'done_by' => $userId,
            'done_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markUndone(int $taskId): bool
    {
        return $this->update($taskId, [
            'is_done' => false,
            'done_by' => null,
            'done_at' => null,
        ]);
    }
    public function getTree(int $projectId): array
    {
        $all      = $this->where('project_id', $projectId)->orderBy('order_no', 'ASC')->findAll();
        $tree     = [];
        $children = [];

        foreach ($all as $task) {
            if (empty($task['parent_id'])) {
                $tree[$task['id']] = $task;
                $tree[$task['id']]['subtasks'] = [];
            } else {
                $children[$task['parent_id']][] = $task;
            }
        }

        foreach ($tree as $id => &$task) {
            $task['subtasks'] = $children[$id] ?? [];
        }

        return array_values($tree);
    }
}
