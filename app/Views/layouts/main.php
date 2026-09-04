<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'App') ?> | ProjectTrack</title>

    <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('assets/img/apple-icon.png') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png') ?>">

    <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet"> -->
    <link rel="stylesheet" href="<?= base_url('assets/css/nucleo-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/nucleo-svg.css') ?>">
    <link id="pagestyle" href="<?= base_url('assets/css/soft-ui-dashboard.min.css') ?>" rel="stylesheet">

    <?= $this->renderSection('styles') ?>
</head>

<body class="g-sidenav-show bg-gray-100">

    <!-- SIDEBAR -->
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
        <div class="sidenav-header">
            <a class="navbar-brand m-0" href="<?= base_url('dashboard') ?>">
                <span class="ms-1 font-weight-bold text-white">ProjectTrack</span>
            </a>
        </div>
        <hr class="horizontal light mt-0 mb-2">
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">

                <?php
                $menu = [
                    ['url' => 'dashboard', 'icon' => 'ni-tv-2',       'label' => 'Dashboard'],
                    ['url' => 'projects',  'icon' => 'ni-folder-17',  'label' => 'Semua Project'],
                    ['url' => 'tasks',     'icon' => 'ni-check-bold', 'label' => 'Checklist Pekerjaan'],
                ];

                if (is_super_team()) {
                    $menu[] = ['url' => 'planning',      'icon' => 'ni-calendar-grid-58',  'label' => 'Deadline & Planning'];
                    $menu[] = ['url' => 'evidence',      'icon' => 'ni-image',             'label' => 'Bukti Pekerjaan'];
                    $menu[] = ['url' => 'master',        'icon' => 'ni-settings-gear-65',  'label' => 'Konfigurasi Sistem'];
                    $menu[] = ['url' => 'users',         'icon' => 'ni-single-02',         'label' => 'User & Akses'];
                    $menu[] = ['url' => 'activity-logs', 'icon' => 'ni-book-bookmark',     'label' => 'Audit Sistem'];
                }

                $currentPath = trim(current_url(true)->getPath(), '/');
                ?>

                <?php foreach ($menu as $item) : ?>
                    <?php $isActive = str_starts_with($currentPath, $item['url']); ?>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= $isActive ? 'active bg-gradient-primary' : '' ?>" href="<?= base_url($item['url']) ?>">
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni <?= esc($item['icon']) ?> text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1"><?= esc($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>

            </ul>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content position-relative border-radius-lg">

        <!-- TOPBAR -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <h6 class="font-weight-bolder mb-0"><?= esc($title ?? 'Dashboard') ?></h6>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <ul class="navbar-nav justify-content-end">
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link text-body font-weight-bold px-0" data-bs-toggle="dropdown">
                                <i class="fa fa-user me-sm-1"></i>
                                <span class="d-sm-inline d-none"><?= esc(auth()->user()->username ?? auth()->user()->email ?? 'User') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end px-2 py-3">
                                <li>
                                    <a class="dropdown-item border-radius-md" href="<?= base_url('logout') ?>">Logout</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- /TOPBAR -->
        <div class="container-fluid py-4">
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger text-white"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>

    </main>

    <script src="<?= base_url('assets/js/core/popper.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/core/bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/plugins/perfect-scrollbar.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/soft-ui-dashboard.min.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>