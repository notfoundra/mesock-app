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

    protected $allowedFields = ['project_id', 'user_id', 'parent_id', 'comment'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'user_id'    => 'required|is_natural_no_zero',
        'comment'    => 'required',
    ];

    public function getByProject(int $projectId): array
    {
        return $this->select('project_comments.*, user_profiles.fullname, user_profiles.avatar')
            ->join('user_profiles', 'user_profiles.user_id = project_comments.user_id', 'left')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
