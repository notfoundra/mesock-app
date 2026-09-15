<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingModel extends Model
{
    protected $table            = 'meetings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = ['project_id', 'title', 'meeting_date', 'problem', 'expected_outcome', 'notes', 'created_by'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'title'        => 'required|max_length[200]',
        'meeting_date' => 'required|valid_date',
    ];

    public function withDetails()
    {
        return $this->select('
                meetings.*,
                projects.project_code, projects.title as project_title,
                user_profiles.fullname as creator_name
            ')
            ->join('projects', 'projects.id = meetings.project_id', 'left')
            ->join('user_profiles', 'user_profiles.user_id = meetings.created_by', 'left');
    }

    public function getFiltered(array $filters = [], int $perPage = 12)
    {
        $builder = $this->withDetails();

        if (! empty($filters['project_id'])) {
            $builder->where('meetings.project_id', $filters['project_id']);
        }
        if (! empty($filters['date_from'])) {
            $builder->where('meetings.meeting_date >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('meetings.meeting_date <=', $filters['date_to']);
        }
        if (! empty($filters['keyword'])) {
            $builder->like('meetings.title', $filters['keyword']);
        }

        return $builder->orderBy('meetings.meeting_date', 'DESC')->paginate($perPage);
    }
}
