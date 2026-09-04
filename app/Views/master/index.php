<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<ul class="nav nav-pills nav-fill mb-4 bg-white border-radius-lg p-2 shadow-sm">
    <?php foreach ($menuConfig as $slug => $cfg) : ?>
        <li class="nav-item">
            <a class="nav-link <?= $slug === $type ? 'active bg-gradient-primary text-white' : 'text-dark' ?>" href="<?= site_url('master/' . $slug) ?>">
                <?= esc($cfg['label']) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><?= esc($label) ?></h6>
        <a href="<?= site_url('master/' . $type . '/create') ?>" class="btn btn-sm bg-gradient-primary mb-0">
            <i class="ni ni-fat-add me-1"></i> Tambah
        </a>
    </div>
    <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <?php foreach ($fields as $field) : ?>
                            <?php if ($field['type'] === 'textarea') continue; ?>
                            <th class="text-xs text-secondary text-uppercase"><?= esc($field['label']) ?></th>
                        <?php endforeach; ?>
                        <th class="text-xs text-secondary text-uppercase text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)) : ?>
                        <tr>
                            <td colspan="99" class="text-center text-secondary py-4">Belum ada data.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $row) : ?>
                        <tr>
                            <?php foreach ($fields as $field) : ?>
                                <?php if ($field['type'] === 'textarea') continue; ?>
                                <td class="text-sm">
                                    <?php if ($field['type'] === 'color') : ?>
                                        <span class="badge" style="background-color: <?= esc($row[$field['name']] ?? '#ccc') ?>">&nbsp;&nbsp;&nbsp;</span>
                                        <span class="ms-1"><?= esc($row[$field['name']] ?? '-') ?></span>
                                    <?php elseif ($field['type'] === 'switch') : ?>
                                        <?= ! empty($row[$field['name']]) ? '<span class="badge bg-gradient-success">Aktif</span>' : '<span class="badge bg-gradient-secondary">Nonaktif</span>' ?>
                                    <?php else : ?>
                                        <?= esc($row[$field['name']] ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-end">
                                <a href="<?= site_url('master/' . $type . '/' . $row['id'] . '/edit') ?>" class="btn btn-link text-dark px-2 mb-0">
                                    <i class="ni ni-ruler-pencil"></i>
                                </a>
                                <form action="<?= site_url('master/' . $type . '/' . $row['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus data ini?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-link text-danger px-2 mb-0">
                                        <i class="ni ni-fat-remove"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>