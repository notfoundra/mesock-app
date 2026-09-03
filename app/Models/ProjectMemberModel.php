<?php
// app/Models/ProjectMemberModel.php
namespace App\Models;

use CodeIgniter\Model;

class ProjectMemberModel extends Model
{
    protected $table            = 'project_members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = ['project_id', 'user_id', 'role'];

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'user_id'    => 'required|is_natural_no_zero',
        'role'       => 'required|in_list[PIC,Reviewer]',
    ];

    public function getMembers(int $projectId): array
    {
        return $this->select('project_members.*, user_profiles.fullname, user_profiles.avatar')
            ->join('user_profiles', 'user_profiles.user_id = project_members.user_id', 'left')
            ->where('project_id', $projectId)
            ->findAll();
    }
}
