<?php
// app/Models/TeamModel.php
namespace App\Models;

use CodeIgniter\Model;

class TeamModel extends Model
{
    protected $table            = 'teams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = ['name', 'code', 'description', 'color', 'sort_order', 'is_active'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'code' => 'required|max_length[30]|is_unique[teams.code,id,{id}]',
    ];
}
