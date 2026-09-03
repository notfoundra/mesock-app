<?php
// app/Models/UserProfileModel.php
namespace App\Models;

use CodeIgniter\Model;

class UserProfileModel extends Model
{
    protected $table            = 'user_profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'user_id',
        'team_id',
        'employee_id',
        'fullname',
        'phone',
        'avatar',
        'position',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'user_id'  => 'required|is_natural_no_zero|is_unique[user_profiles.user_id,id,{id}]',
        'team_id' => 'permit_empty|is_natural_no_zero',
        'fullname' => 'required|max_length[150]',
    ];

    public function getByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }
}
