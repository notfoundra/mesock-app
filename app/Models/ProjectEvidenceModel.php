<?php
// app/Models/ProjectEvidenceModel.php
namespace App\Models;

use CodeIgniter\Model;

class ProjectEvidenceModel extends Model
{
    protected $table            = 'project_evidences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'project_id',
        'task_id',
        'daily_task_id',
        'meeting_id',
        'category_id',
        'file_name',
        'original_name',
        'mime_type',
        'file_size',
        'caption',
        'uploaded_by',
    ];

    protected $validationRules = [
        'category_id' => 'required|is_natural_no_zero',
        'file_name'   => 'required|max_length[255]',
    ];

    public function getGallery(?int $projectId = null, ?int $categoryId = null): array
    {
        $builder = $this->select('
                project_evidences.*,
                evidence_categories.name as category_name, evidence_categories.color as category_color,
                projects.title as project_title,
                user_profiles.fullname as uploader_name
            ')
            ->join('evidence_categories', 'evidence_categories.id = project_evidences.category_id', 'left')
            ->join('projects', 'projects.id = project_evidences.project_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = project_evidences.uploaded_by', 'left');

        if ($projectId) {
            $builder->where('project_evidences.project_id', $projectId);
        }

        if ($categoryId) {
            $builder->where('project_evidences.category_id', $categoryId);
        }

        return $builder->orderBy('project_evidences.created_at', 'DESC')->findAll();
    }
    public function getByTask(int $taskId): array
    {
        return $this->select('
            project_evidences.*,
            evidence_categories.name as category_name, evidence_categories.color as category_color,
            user_profiles.fullname as uploader_name
        ')
            ->join('evidence_categories', 'evidence_categories.id = project_evidences.category_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = project_evidences.uploaded_by', 'left')
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
    public function getByDailyTask(int $dailyTaskId): array
    {
        return $this->select('
            project_evidences.*,
            evidence_categories.name as category_name, evidence_categories.color as category_color,
            user_profiles.fullname as uploader_name
        ')
            ->join('evidence_categories', 'evidence_categories.id = project_evidences.category_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = project_evidences.uploaded_by', 'left')
            ->where('daily_task_id', $dailyTaskId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
    public function getFiltered(array $filters = [], int $perPage = 12)
    {
        $builder = $this->select('
            project_evidences.*,
            evidence_categories.name as category_name, evidence_categories.color as category_color,
            projects.title as project_title, projects.project_code,
            daily_tasks.title as daily_task_title,
            user_profiles.fullname as uploader_name
        ')
            ->join('evidence_categories', 'evidence_categories.id = project_evidences.category_id', 'left')
            ->join('projects', 'projects.id = project_evidences.project_id', 'left')
            ->join('daily_tasks', 'daily_tasks.id = project_evidences.daily_task_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = project_evidences.uploaded_by', 'left');

        if (! empty($filters['project_id'])) {
            $builder->where('project_evidences.project_id', $filters['project_id']);
        }
        if (! empty($filters['category_id'])) {
            $builder->where('project_evidences.category_id', $filters['category_id']);
        }
        if (! empty($filters['source']) && $filters['source'] === 'project') {
            $builder->where('project_evidences.task_id IS NOT NULL');
        } elseif (! empty($filters['source']) && $filters['source'] === 'daily') {
            $builder->where('project_evidences.daily_task_id IS NOT NULL');
        }
        if (! empty($filters['date_from'])) {
            $builder->where('DATE(project_evidences.created_at) >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('DATE(project_evidences.created_at) <=', $filters['date_to']);
        }

        return $builder->orderBy('project_evidences.created_at', 'DESC')->paginate($perPage);
    }
    public function getByMeeting(int $meetingId): array
    {
        return $this->select('
            project_evidences.*,
            evidence_categories.name as category_name, evidence_categories.color as category_color,
            user_profiles.fullname as uploader_name
        ')
            ->join('evidence_categories', 'evidence_categories.id = project_evidences.category_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = project_evidences.uploaded_by', 'left')
            ->where('meeting_id', $meetingId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
