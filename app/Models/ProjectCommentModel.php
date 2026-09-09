<?php
// app/Models/ProjectCommentModel.php
namespace App\Models;

use CodeIgniter\Model;

class ProjectCommentModel extends Model
{
    protected $table            = 'project_comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields = ['project_id', 'task_id', 'daily_task_id', 'user_id', 'parent_id', 'comment'];

    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'comment' => 'required',
    ];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';



    public function getByProject(int $projectId): array
    {
        return $this->select('project_comments.*, user_profiles.fullname, user_profiles.avatar')
            ->join('user_profiles', 'user_profiles.user_id = project_comments.user_id', 'left')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
    public function getByTask(int $taskId): array
    {
        return $this->select('project_comments.*, user_profiles.fullname, user_profiles.avatar')
            ->join('user_profiles', 'user_profiles.user_id = project_comments.user_id', 'left')
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
    public function getByDailyTask(int $dailyTaskId): array
    {
        return $this->select('project_comments.*, user_profiles.fullname, user_profiles.avatar')
            ->join('user_profiles', 'user_profiles.user_id = project_comments.user_id', 'left')
            ->where('daily_task_id', $dailyTaskId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
