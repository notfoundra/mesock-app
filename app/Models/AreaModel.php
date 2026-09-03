<?php
// app/Models/AreaModel.php
namespace App\Models;

use CodeIgniter\Model;

class AreaModel extends Model
{
    protected $table            = 'areas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = ['name', 'code', 'group_area', 'description', 'is_active'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'code' => 'required|max_length[30]|is_unique[areas.code,id,{id}]',
    ];
}
