<?php

/**
 * Tailwind Pagination Template for CodeIgniter 4
 * File: app/Views/Pager/tailwind_pagination.php
 *
 * Fix: getCurrentPage() tidak ada di PagerRenderer CI4.
 *      Gunakan $pager->links() untuk iterasi, dan deteksi
 *      halaman aktif dari $link['active'].
 */

/** @var \CodeIgniter\Pager\PagerRenderer $pager */

$pager->setSurroundCount(2);

// Hitung current & total dari links (cara CI4 yang benar)
$links      = $pager->links();
$currentPage = 1;
foreach ($links as $link) {
    if ($link['active']) {
        $currentPage = (int) $link['title'];
        break;
    }
}
$totalPages = count(array_filter($links, fn($l) => is_numeric($l['title'])));
?>

<nav aria-label="Pagination" class="flex items-center justify-between flex-wrap gap-3">
    <div class="text-sm text-gray-500">
        Halaman <?= $currentPage ?> dari <?= $pager->getPageCount() ?>
    </div>
    <div class="flex items-center gap-1">

        <?php if ($pager->hasPreviousPage()): ?>
            <a href="<?= $pager->getPreviousPageURI() ?>"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Prev
            </a>
        <?php endif; ?>

        <?php foreach ($links as $link): ?>
            <?php if (! is_numeric($link['title'])) continue; ?>
            <?php if ($link['active']): ?>
                <span class="inline-flex items-center px-3 py-1.5 bg-blue-700 text-white rounded-lg text-sm font-semibold">
                    <?= $link['title'] ?>
                </span>
            <?php else: ?>
                <a href="<?= $link['uri'] ?>"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                    <?= $link['title'] ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($pager->hasNextPage()): ?>
            <a href="<?= $pager->getNextPageURI() ?>"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                Next
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        <?php endif; ?>

    </div>
</nav>