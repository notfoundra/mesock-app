<?php
// app/Models/ProjectStatusModel.php
namespace App\Models;

use CodeIgniter\Model;

class ProjectStatusModel extends Model
{
    protected $table            = 'project_statuses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = ['name', 'slug', 'color', 'sort_order'];

    protected $validationRules = [
        'name' => 'required|max_length[50]',
        'slug' => 'required|max_length[50]|is_unique[project_statuses.slug,id,{id}]',
    ];
}
