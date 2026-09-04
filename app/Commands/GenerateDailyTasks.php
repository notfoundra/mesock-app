<?php
// app/Commands/GenerateDailyTasks.php
namespace App\Commands;

use App\Models\DailyTaskModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerateDailyTasks extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'tasks:generate-daily';
    protected $description = 'Generate checklist harian dari task_templates aktif.';

    public function run(array $params)
    {
        $date    = $params[0] ?? date('Y-m-d');
        $model   = new DailyTaskModel();
        $created = $model->generateForDate($date);

        CLI::write("Generated {$created} daily task(s) for {$date}.", 'green');
    }
}
