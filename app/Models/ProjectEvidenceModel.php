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
        'category_id',
        'file_name',
        'original_name',
        'mime_type',
        'file_size',
        'caption',
        'uploaded_by',
    ];

    protected $validationRules = [
        'project_id'  => 'required|is_natural_no_zero',
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
}
