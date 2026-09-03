<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login') ?> | ProjectTrack</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/nucleo-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/nucleo-svg.css') ?>">
    <link href="<?= base_url('assets/css/soft-ui-dashboard.min.css') ?>" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100"
            style="background-image: linear-gradient(195deg, rgba(20,23,39,.7), rgba(20,23,39,.7)), url('<?= base_url('assets/img/curved-images/curved14.jpg') ?>'); background-size: cover;">
            <span class="mask bg-gradient-dark opacity-6"></span>
            <div class="container my-auto">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card z-index-0 fadeIn3 fadeInBottom mt-8">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">ProjectTrack</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <?= $this->renderSection('content') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="<?= base_url('assets/js/core/popper.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/core/bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/soft-ui-dashboard.min.js') ?>"></script>
</body>

</html>