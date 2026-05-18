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

// Render category tree as collapsible accordion cards
$renderCatAccordion = function ($nodes, $depth = 0) use (&$renderCatAccordion) {
    foreach ($nodes as $n) {
        $hasChildren = !empty($n['children']);
        if ($hasChildren && $depth === 0) {
            // Top-level category = accordion card
            echo '<div class="cat-section-card">';
            echo '<button type="button" class="cat-section-header" data-accordion>';
            echo '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>';
            echo ' <span>' . e($n['name']) . '</span>';
            echo '</button>';
            echo '<div class="cat-section-body">';
            echo '<label class="cat-pick"><input form="upload-form" type="checkbox" name="categories[]" value="' . (int)$n['id'] . '"> <strong>All ' . e($n['name']) . '</strong></label>';
            $renderCatAccordion($n['children'], $depth + 1);
            echo '</div></div>';
        } elseif ($hasChildren) {
            // Nested parent - show as sub-group
            echo '<div style="margin-left:' . (($depth - 1) * 12) . 'px; margin-top:.35rem">';
            echo '<label class="cat-pick" style="font-weight:600"><input form="upload-form" type="checkbox" name="categories[]" value="' . (int)$n['id'] . '"> ' . e($n['name']) . '</label>';
            $renderCatAccordion($n['children'], $depth + 1);
            echo '</div>';
        } else {
            echo '<label class="cat-pick" style="padding-left:' . (($depth - 1) * 12) . 'px"><input form="upload-form" type="checkbox" name="categories[]" value="' . (int)$n['id'] . '"> ' . e($n['name']) . '</label>';
        }
    }
};
?>
<div class="upload-page">
    <div class="upload-header">
        <h1>Upload media</h1>
        <p class="muted">Drop files anywhere. Max <?= (int) $maxMb ?> MB per file. Duplicate files (same hash) are auto-detected.</p>
    </div>

    <div class="upload-grid">
        <!-- Left: drop zone + preview -->
        <div class="upload-left">
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

            <!-- Media preview before upload -->
            <div id="upload-preview" class="upload-preview">
                <video id="preview-video" controls style="display:none; max-width:100%; max-height:300px;"></video>
                <img id="preview-image" style="display:none; max-width:100%; max-height:300px;" alt="Preview">
                <div id="preview-pdf" class="pdf-preview-msg" style="display:none">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M13 2v7h7"/></svg>
                    <p>PDF file selected — preview available after upload.</p>
                </div>
                <div id="preview-ppt" class="ppt-preview-msg" style="display:none">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    <p>Presentation file selected — first-slide preview generated after upload.</p>
                </div>
            </div>
        </div>

        <!-- Right: metadata & settings -->
        <aside class="upload-meta glass">
            <!-- Section 1: Basic Info -->
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    Basic information
                </div>
                <div class="form-section-body">
                    <label><span>Title</span><input form="upload-form" type="text" name="title" placeholder="Auto-fills from filename"></label>
                    <label><span>Description</span><textarea form="upload-form" name="description" rows="3"></textarea></label>
                    <label><span>Keywords</span><input form="upload-form" type="text" name="keywords" placeholder="Separate with commas"></label>
                    <label><span>Tags</span><input form="upload-form" type="text" name="tags_csv" placeholder="awareness, campaign, 2026"></label>
                </div>
            </div>

            <!-- Section 2: Section & Categories -->
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Section &amp; Categories
                </div>
                <div class="form-section-body">
                    <fieldset>
                        <legend>Section</legend>
                        <?php foreach ($sections as $i => $s): ?>
                        <label class="cat-pick"><input form="upload-form" type="radio" name="section" value="<?= e($s['code']) ?>" <?= $i === 0 ? 'checked' : '' ?>> <?= e($s['name']) ?></label>
                        <?php endforeach; ?>
                    </fieldset>

                    <?php foreach ($sections as $s): ?>
                    <div class="cat-group">
                        <h4 style="margin:0 0 .5rem;font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em"><?= e($s['name']) ?></h4>
                        <?php $renderCatAccordion($trees[$s['code']] ?? []); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Section 3: Occasions -->
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    Occasions
                </div>
                <div class="form-section-body">
                    <?php foreach ($occasions as $code => $g): ?>
                    <div class="cat-section-card">
                        <button type="button" class="cat-section-header" data-accordion>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                            <span><?= e($g['name']) ?></span>
                        </button>
                        <div class="cat-section-body">
                            <?php foreach ($g['items'] as $o): ?>
                            <label class="cat-pick"><input form="upload-form" type="checkbox" name="occasions[]" value="<?= (int) $o['id'] ?>"> <?= e($o['name']) ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Section 4: Settings -->
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Settings
                </div>
                <div class="form-section-body">
                    <label class="cat-pick"><input form="upload-form" type="checkbox" name="is_downloadable" value="1"> Allow downloads</label>
                </div>
            </div>

            <!-- Sticky upload button -->
            <div class="upload-submit-wrap">
                <button type="button" id="upload-submit-btn" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                    Upload file
                </button>
            </div>
        </aside>
    </div>
</div>

<?php $this->section('scripts'); ?>
<script src="<?= asset('js/upload.js') ?>" defer></script>
<?php $this->endSection(); ?>
