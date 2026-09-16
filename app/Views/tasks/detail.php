<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger text-white"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<a href="<?= site_url('tasks/project/' . $project['id']) ?>" class="text-sm text-secondary mb-3 d-inline-block">&larr; Kembali ke Checklist</a>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><?= esc($task['title']) ?></h6>
        <p class="text-sm text-secondary mb-0">Project: <?= esc($project['title']) ?></p>
    </div>
    <div class="card-body">
        <p class="text-sm"><?= esc($task['description'] ?: '-') ?></p>
        <span class="badge <?= $task['is_done'] ? 'bg-gradient-success' : 'bg-gradient-secondary' ?>">
            <?= $task['is_done'] ? 'Selesai' : 'Belum Selesai' ?>
        </span>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Lampiran Gambar</h6>
    </div>
    <div class="card-body">
        <form action="<?= site_url('tasks/' . $task['id'] . '/evidence') ?>" method="post" enctype="multipart/form-data" class="row g-2 mb-3">
            <?= csrf_field() ?>
            <div class="col-md-4">
                <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.txt" required>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-control" required>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="caption" class="form-control" placeholder="Caption (opsional)">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn bg-gradient-primary w-100 mb-0">Upload</button>
            </div>
        </form>

        <div class="row">
            <?php foreach ($evidences as $ev) : ?>
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <a href="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>" target="_blank">
                            <?php if (is_image_mime($ev['mime_type'])) : ?>
                                <img src="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>" class="card-img-top" style="height:140px;object-fit:cover;">
                            <?php else : ?>
                                <div class="d-flex flex-column align-items-center justify-content-center bg-light" style="height:140px;">
                                    <i class="<?= file_icon_class($ev['mime_type']) ?>" style="font-size:2.5rem;"></i>
                                    <span class="text-xs text-secondary mt-1 px-2 text-truncate w-100 text-center">
                                        <?= esc($ev['original_name']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="card-body p-2">
                            <span class="badge mb-1" style="background-color: <?= esc($ev['category_color'] ?? '#ccc') ?>"><?= esc($ev['category_name'] ?? '-') ?></span>
                            <p class="text-xs mb-0"><?= esc($ev['caption'] ?: '-') ?></p>
                            <p class="text-xs text-secondary mb-0"><?= format_file_size((int) $ev['file_size']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($evidences)) : ?>
                <p class="text-secondary text-sm">Belum ada gambar.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Komentar</h6>
    </div>
    <div class="card-body">
        <form action="<?= site_url('tasks/' . $task['id'] . '/comment') ?>" method="post" class="mb-3">
            <?= csrf_field() ?>
            <textarea name="comment" id="commentEditor" class="form-control mb-2" rows="4" placeholder="Tulis komentar..."></textarea>
            <button type="submit" class="btn btn-sm bg-gradient-primary mb-0">Kirim</button>
        </form>

        <?php foreach ($comments as $c) : ?>
            <div class="mb-3">
                <p class="text-sm font-weight-bold mb-0"><?= esc($c['fullname'] ?? 'User') ?></p>
                <div class="text-sm mb-0 comment-body"><?= $c['comment'] ?></div>
                <p class="text-xs text-secondary mb-0"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></p>
            </div>
        <?php endforeach; ?>
        <?php if (empty($comments)) : ?>
            <p class="text-secondary text-sm">Belum ada komentar.</p>
        <?php endif; ?>
    </div>
</div>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/ckeditor/ckeditor.js') ?>"></script>
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
<?= $this->endSection() ?>