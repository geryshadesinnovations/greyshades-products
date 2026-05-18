<?php
/**
 * @var array<int,array> $sections
 * @var array<string,array> $trees       map sectionCode => nested category tree
 * @var array $filters
 */
$selectedCat = isset($filters['category_id']) ? (int) $filters['category_id'] : 0;
$selectedSec = $filters['section_code'] ?? null;

$renderNode = function (array $node, int $depth = 0) use (&$renderNode, $selectedCat) {
    $active = $selectedCat === (int) $node['id'];
    $hasChildren = !empty($node['children']);
    ?>
    <li class="tree-item <?= $hasChildren ? 'has-children' : '' ?>">
        <div class="tree-row">
            <?php if ($hasChildren): ?>
            <button class="tree-toggle" data-toggle="tree" type="button" aria-label="Toggle">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            <?php else: ?><span class="tree-spacer"></span><?php endif; ?>
            <a class="tree-link <?= $active ? 'active' : '' ?>"
               href="?<?= http_build_query(array_filter(['section'=>$_GET['section']??null,'category'=>$node['id']])) ?>">
                <?= e($node['name']) ?>
            </a>
        </div>
        <?php if ($hasChildren): ?>
        <ul class="tree-children" <?= $depth >= 1 ? 'hidden' : '' ?>>
            <?php foreach ($node['children'] as $child) $renderNode($child, $depth + 1); ?>
        </ul>
        <?php endif; ?>
    </li>
    <?php
};
?>
<aside class="sidebar">
    <div class="sidebar-section">
        <a class="sidebar-home <?= empty($filters['section_code']) && empty($filters['category_id']) ? 'active' : '' ?>" href="<?= url('/dashboard') ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            All media
        </a>
    </div>

    <?php foreach ($sections as $s): ?>
    <div class="sidebar-section">
        <h4 class="sidebar-title">
            <a href="?section=<?= e($s['code']) ?>" class="<?= ($selectedSec === $s['code'] && !$selectedCat) ? 'active' : '' ?>">
                <?php if ($s['code'] === 'graphics'): ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg>
                <?php else: ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <?php endif; ?>
                <?= e($s['name']) ?>
            </a>
        </h4>
        <ul class="tree">
            <?php foreach ($trees[$s['code']] ?? [] as $node) $renderNode($node, 0); ?>
        </ul>
    </div>
    <?php endforeach; ?>
</aside>
