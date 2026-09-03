<?php
// app/Models/ActivityLogModel.php
namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = ['project_id', 'user_id', 'activity', 'old_value', 'new_value'];

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'user_id'    => 'required|is_natural_no_zero',
        'activity'   => 'required|max_length[150]',
    ];

    /**
     * Helper cepat dipanggil dari controller lain buat nyatet history.
     * Contoh: $activityLogModel->record($projectId, auth()->id(), 'Mengubah status', 'To Do', 'In Progress');
     */
    public function record(int $projectId, int $userId, string $activity, $oldValue = null, $newValue = null): bool
    {
        return (bool) $this->insert([
            'project_id' => $projectId,
            'user_id'    => $userId,
            'activity'   => $activity,
            'old_value'  => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value'  => is_array($newValue) ? json_encode($newValue) : $newValue,
        ]);
    }

    public function getByProject(int $projectId): array
    {
        return $this->select('activity_logs.*, user_profiles.fullname')
            ->join('user_profiles', 'user_profiles.user_id = activity_logs.user_id', 'left')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getRecent(int $limit = 20): array
    {
        return $this->select('activity_logs.*, user_profiles.fullname, projects.title as project_title')
            ->join('user_profiles', 'user_profiles.user_id = activity_logs.user_id', 'left')
            ->join('projects', 'projects.id = activity_logs.project_id', 'left')
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }
}
