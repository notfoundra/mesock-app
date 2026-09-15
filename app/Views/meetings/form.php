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
        <form action="<?= $isEdit ? site_url('meetings/' . $row['id'] . '/update') : site_url('meetings/store') ?>" method="post" class="row g-3">
            <?= csrf_field() ?>

            <div class="col-md-8">
                <label>Judul Meeting</label>
                <input type="text" name="title" class="form-control" value="<?= old('title') ?? esc($row['title'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label>Tanggal</label>
                <input type="date" name="meeting_date" class="form-control" value="<?= esc($row['meeting_date'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="col-md-12">
                <label>Terkait Project (opsional)</label>
                <select name="project_id" class="form-control">
                    <option value="">-- Nggak terkait project spesifik --</option>
                    <?php foreach ($projects as $p) : ?>
                        <option value="<?= $p['id'] ?>" <?= ($row['project_id'] ?? null) == $p['id'] ? 'selected' : '' ?>><?= esc($p['project_code'] . ' - ' . $p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label>Masalah yang Dibahas</label>
                <textarea name="problem" class="form-control ck-rich-editor" rows="5"><?= old('problem') ?? ($row['problem'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
                <label>Hasil yang Diharapkan</label>
                <textarea name="expected_outcome" class="form-control ck-rich-editor" rows="5"><?= old('expected_outcome') ?? ($row['expected_outcome'] ?? '') ?></textarea>
            </div>

            <div class="col-md-12">
                <label>Notulensi Lengkap</label>
                <textarea name="notes" class="form-control ck-rich-editor" rows="8"><?= old('notes') ?? ($row['notes'] ?? '') ?></textarea>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                <a href="<?= site_url('meetings') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.ck-rich-editor, #commentEditor').forEach(function(textarea) {
            if (textarea.ckeditorInstance) return; // biar nggak double-init

            ClassicEditor
                .create(textarea, {
                    toolbar: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'blockQuote', 'insertTable', '|',
                        'undo', 'redo',
                    ],
                })
                .then(function(editor) {
                    textarea.ckeditorInstance = editor;
                    const form = textarea.closest('form');
                    if (form && !form.dataset.ckSubmitBound) {
                        form.dataset.ckSubmitBound = '1';
                        form.addEventListener('submit', function() {
                            document.querySelectorAll('.ck-rich-editor, #commentEditor').forEach(function(ta) {
                                if (ta.ckeditorInstance) ta.ckeditorInstance.updateSourceElement();
                            });
                        });
                    }
                })
                .catch(function(error) {
                    console.error(error);
                });
        });
    });
</script>
<?= $this->endSection() ?>