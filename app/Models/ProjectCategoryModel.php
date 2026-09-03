<?php
// app/Models/ProjectCategoryModel.php
namespace App\Models;

use CodeIgniter\Model;

class ProjectCategoryModel extends Model
{
    protected $table            = 'project_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = ['name', 'color', 'icon', 'sort_order', 'is_active'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name' => 'required|max_length[100]',
    ];
}
