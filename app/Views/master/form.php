<?= $this->extend('layouts/main') ?>
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

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><?= esc($title) ?></h6>
    </div>
    <div class="card-body">
        <form action="<?= $isEdit ? site_url('master/' . $type . '/' . $row['id'] . '/update') : site_url('master/' . $type . '/store') ?>" method="post">
            <?= csrf_field() ?>

            <?php foreach ($fields as $field) : ?>
                <div class="mb-3">
                    <label><?= esc($field['label']) ?><?= ! empty($field['required']) ? ' *' : '' ?></label>
                    <?php $value = old($field['name']) ?? ($row[$field['name']] ?? ''); ?>

                    <?php if ($field['type'] === 'textarea') : ?>
                        <textarea name="<?= esc($field['name']) ?>" class="form-control" rows="3"><?= esc($value) ?></textarea>
                    <?php elseif ($field['type'] === 'color') : ?>
                        <input type="color" name="<?= esc($field['name']) ?>" class="form-control form-control-color" value="<?= esc($value ?: '#5E72E4') ?>">
                    <?php elseif ($field['type'] === 'number') : ?>
                        <input type="number" name="<?= esc($field['name']) ?>" class="form-control" value="<?= esc($value ?: 1) ?>">
                    <?php elseif ($field['type'] === 'switch') : ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?= esc($field['name']) ?>" value="1" <?= ! empty($value) ? 'checked' : '' ?>>
                        </div>
                    <?php else : ?>
                        <input type="text" name="<?= esc($field['name']) ?>" class="form-control" value="<?= esc($value) ?>" <?= ! empty($field['required']) ? 'required' : '' ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn bg-gradient-primary">Simpan</button>
            <a href="<?= site_url('master/' . $type) ?>" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>