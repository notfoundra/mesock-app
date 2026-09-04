<?php

namespace App\Controllers;

use App\Models\TeamModel;
use App\Models\UserProfileModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class UserManagementController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $users = $db->table('users u')
            ->select('u.id, u.username, u.active, ai.secret as email, up.fullname, up.team_id, up.is_active as profile_active, t.name as team_name, t.code as team_code')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('user_profiles up', 'up.user_id = u.id', 'left')
            ->join('teams t', 't.id = up.team_id', 'left')
            ->orderBy('u.id', 'ASC')
            ->get()
            ->getResultArray();

        return view('users/index', [
            'title' => 'User & Akses',
            'users' => $users,
        ]);
    }

    public function edit(int $userId)
    {
        $db = db_connect();

        $user = $db->table('users u')
            ->select('u.id, u.username, u.active, up.fullname, up.team_id, up.is_active as profile_active')
            ->join('user_profiles up', 'up.user_id = u.id', 'left')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();

        if (! $user) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('users/edit', [
            'title' => 'Edit Akses - ' . $user['username'],
            'user'  => $user,
            'teams' => (new TeamModel())->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function update(int $userId)
    {
        $request      = service('request');
        $profileModel = new UserProfileModel();
        $db           = db_connect();

        $profile = $profileModel->getByUserId($userId);

        $data = [
            'user_id'   => $userId,
            'fullname'  => $request->getPost('fullname') ?: ('User #' . $userId),
            'team_id'   => $request->getPost('team_id') ?: null,
            'is_active' => $request->getPost('profile_active') ? 1 : 0,
        ];

        if ($profile) {
            $profileModel->update($profile['id'], $data);
        } else {
            $profileModel->insert($data);
        }

        $db->table('users')->where('id', $userId)->update([
            'active' => $request->getPost('account_active') ? 1 : 0,
        ]);

        session()->setFlashdata('success', 'Akses user berhasil diperbarui.');

        return redirect()->to('/users');
    }
}
