<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TaskController extends BaseController
{
    public function index()
    {
        return view('placeholder', ['title' => 'Checklist Pekerjaan']);
    }
}
