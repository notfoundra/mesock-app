<?php
// app/Models/EvidenceCategoryModel.php
namespace App\Models;

use CodeIgniter\Model;

class EvidenceCategoryModel extends Model
{
    protected $table            = 'evidence_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = ['name', 'color'];

    protected $validationRules = [
        'name' => 'required|max_length[50]',
    ];
}
