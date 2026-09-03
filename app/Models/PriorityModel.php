<?php
// app/Models/PriorityModel.php
namespace App\Models;

use CodeIgniter\Model;

class PriorityModel extends Model
{
    protected $table            = 'priorities';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false; // migration ini nggak punya created_at/updated_at

    protected $allowedFields = ['name', 'color', 'sort_order'];

    protected $validationRules = [
        'name' => 'required|max_length[50]',
    ];
}
