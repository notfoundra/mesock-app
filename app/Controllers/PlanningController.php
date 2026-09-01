<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PlanningController extends BaseController
{
    public function index()
    {
        return view('placeholder', ['title' => 'Deadline & Planning']);
    }
}
