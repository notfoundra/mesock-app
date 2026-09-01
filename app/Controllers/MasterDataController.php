<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class MasterDataController extends BaseController
{
    public function index()
    {
        return view('placeholder', ['title' => 'Konfigurasi Sistem']);
    }
}
