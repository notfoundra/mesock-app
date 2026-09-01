<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ActivityLogController extends BaseController
{
    public function index()
    {
        return view('placeholder', ['title' => 'Audit Sistem']);
    }
}
