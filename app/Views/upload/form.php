<?php
/**
 * @var array $sections
 * @var array $trees
 * @var array $occasions
 * @var int $maxMb
 * @var array $allowed
 */
use App\Core\Csrf;
$this->extend('layouts/app');

$renderCatTree = function ($nodes, $depth = 0) use (&$renderCatTree) {
    foreach ($nodes as $n) {
        echo '<label class="cat-pick" style="padding-left:' . ($depth * 16) . 'px"><input type="checkbox" name="categories[]" value="' . (int) $n['id'] . '"> ' . e($n['name']) . '</label>';
        if (!empty($n['children'])) $renderCatTree($n['children'], $depth + 1);
    }
};
?>
<div class="upload-page">
    <div class="upload-header">
        <h1>Upload media</h1>
        <p class="muted">Drop files anywhere. Max <?= (int) $maxMb ?> MB per file. Duplicate files (same hash) are auto-detected.</p>
    </div>

    <div class="upload-grid">
        <form id="upload-form" class="upload-dropzone glass" enctype="multipart/form-data" method="post" action="<?= url('/upload') ?>">
            <?= Csrf::field() ?>

            <div id="drop-area" class="drop-area">
                <input type="file" id="file-input" name="file" accept="<?= e(implode(',', $allowed)) ?>" hidden>
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                <h3>Drag &amp; drop a file</h3>
                <p>or <button type="button" class="link-btn" id="browse-btn">browse from your device</button></p>
                <p class="muted small">MP4 · PNG · JPG · WEBP · PDF · PPT · PPTX</p>
                <div id="file-info" class="file-info" hidden></div>
                <div id="progress-wrap" class="progress" hidden><div id="progress-bar"></div></div>
                <div id="upload-result" class="upload-result" hidden></div>
            </div>
        </form>

        <aside class="upload-meta glass">
            <h3>Details</h3>
            <label><span>Title</span><input form="upload-form" type="text" name="title" placeholder="Auto-fills from filename"></label>
            <label><span>Description</span><textarea form="upload-form" name="description" rows="3"></textarea></label>
            <label><span>Keywords</span><input form="upload-form" type="text" name="keywords"></label>
            <label><span>Tags (comma separated)</span><input form="upload-form" type="text" name="tags_csv" placeholder="awareness, campaign, 2026"></label>

            <fieldset>
                <legend>Section</legend>
                <?php foreach ($sections as $i => $s): ?>
                <label class="cat-pick"><input form="upload-form" type="radio" name="section" value="<?= e($s['code']) ?>" <?= $i === 0 ? 'checked' : '' ?>> <?= e($s['name']) ?></label>
                <?php endforeach; ?>
            </fieldset>

            <fieldset>
                <legend>Categories</legend>
                <?php foreach ($sections as $s): ?>
                    <details open>
                        <summary><?= e($s['name']) ?></summary>
                        <div class="cat-tree-pick"><?php $renderCatTree($trees[$s['code']] ?? []); ?></div>
                    </details>
                <?php endforeach; ?>
            </fieldset>

            <fieldset>
                <legend>Occasions (multi-select)</legend>
                <div class="occasion-grid">
                <?php foreach ($occasions as $code => $g): ?>
                    <details>
                        <summary><?= e($g['name']) ?></summary>
                        <?php foreach ($g['items'] as $o): ?>
                        <label class="cat-pick"><input form="upload-form" type="checkbox" name="occasions[]" value="<?= (int) $o['id'] ?>"> <?= e($o['name']) ?></label>
                        <?php endforeach; ?>
                    </details>
                <?php endforeach; ?>
                </div>
            </fieldset>

            <fieldset>
                <legend>Permissions</legend>
                <label class="cat-pick"><input form="upload-form" type="checkbox" name="is_downloadable" value="1"> Allow downloads</label>
            </fieldset>
        </aside>
    </div>
</div>

<?php $this->section('scripts'); ?>
<script src="<?= asset('js/upload.js') ?>" defer></script>
<?php $this->endSection(); ?>
