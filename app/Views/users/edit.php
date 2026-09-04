<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Edit Akses - <?= esc($user['username']) ?></h6>
    </div>
    <div class="card-body">
        <form action="<?= site_url('users/' . $user['id'] . '/update') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="fullname" class="form-control" value="<?= esc($user['fullname'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label>Tim</label>
                <select name="team_id" class="form-control">
                    <option value="">-- Tanpa Tim --</option>
                    <?php foreach ($teams as $team) : ?>
                        <option value="<?= $team['id'] ?>" <?= ($user['team_id'] ?? null) == $team['id'] ? 'selected' : '' ?>>
                            <?= esc($team['name']) ?><?= $team['code'] === 'IE' ? ' (akses semua menu)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-secondary">User di tim <b>IE</b> otomatis dapet akses ke semua menu (super akses).</small>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="profile_active" id="profile_active" value="1" <?= ! empty($user['profile_active']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="profile_active">Profil Aktif</label>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="account_active" id="account_active" value="1" <?= ! empty($user['active']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="account_active">Akun Bisa Login</label>
            </div>

            <button type="submit" class="btn bg-gradient-primary">Simpan</button>
            <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>