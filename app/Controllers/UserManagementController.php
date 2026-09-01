<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UserManagementController extends BaseController
{
    public function index()
    {
        return view('placeholder', ['title' => 'User & Akses']);
    }
}
