<?= $this->extend('layouts/auth') ?>


<?= $this->section('content') ?>

<?php
$errors = session()->getFlashdata('errors');
$teams  = (new \App\Models\TeamModel())->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
?>

<?php if (! empty($errors)) : ?>
    <div class="alert alert-danger text-white">
        <ul class="mb-0 ps-3">
            <?php foreach ((array) $errors as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= site_url('register') ?>" method="post" role="form" class="text-start">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Nama Lengkap</label>
        <input type="text" name="fullname" class="form-control" value="<?= old('fullname') ?>" required>
    </div>

    <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" value="<?= old('username') ?>" required>
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
    </div>

    <div class="mb-3">
        <label>Tim</label>
        <select name="team_id" class="form-control">
            <option value="">-- Pilih tim (bisa diatur admin nanti) --</option>
            <?php foreach ($teams as $team) : ?>
                <option value="<?= $team['id'] ?>" <?= old('team_id') == $team['id'] ? 'selected' : '' ?>>
                    <?= esc($team['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirm" class="form-control" required>
    </div>

    <div class="text-center">
        <button type="submit" class="btn bg-gradient-primary w-100 my-2 mb-2">Daftar</button>
    </div>

    <p class="mt-4 text-sm text-center">
        Sudah punya akun?
        <a href="<?= site_url('login') ?>" class="text-primary text-gradient font-weight-bold">Masuk</a>
    </p>
</form>

<?= $this->endSection() ?>