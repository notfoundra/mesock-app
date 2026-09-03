<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<?php $errors = session()->getFlashdata('errors'); ?>
<?php if (! empty($errors)) : ?>
    <div class="alert alert-danger text-white">
        <ul class="mb-0 ps-3">
            <?php foreach ((array) $errors as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= site_url('login') ?>" method="post" role="form" class="text-start">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required autofocus>
    </div>
    <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <?php if (setting('Auth.sessionConfig')['allowRemembering'] ?? false) : ?>
        <div class="form-check form-switch d-flex align-items-center mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
            <label class="form-check-label mb-0 ms-2" for="remember">Ingat saya</label>
        </div>
    <?php endif; ?>

    <div class="text-center">
        <button type="submit" class="btn bg-gradient-primary w-100 my-2 mb-2">Masuk</button>
    </div>

    <?php if (setting('Auth.allowRegistration')) : ?>
        <p class="mt-4 text-sm text-center">
            Belum punya akun?
            <a href="<?= site_url('register') ?>" class="text-primary text-gradient font-weight-bold">Daftar</a>
        </p>
    <?php endif; ?>
</form>

<?= $this->endSection() ?>