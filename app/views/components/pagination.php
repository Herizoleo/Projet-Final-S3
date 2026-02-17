<?php
/**
 * Composant de pagination réutilisable
 * Variables attendues: $pagination (array), $filters (array), $baseUrl (string)
 */
if (!isset($pagination) || $pagination['totalItems'] == 0) return;
?>

<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted small">
        Affichage <?= $pagination['startItem'] ?> - <?= $pagination['endItem'] ?> 
        sur <?= number_format($pagination['totalItems']) ?> résultat<?= $pagination['totalItems'] > 1 ? 's' : '' ?>
    </div>
    
    <?php if ($pagination['totalPages'] > 1): ?>
    <nav aria-label="Pagination">
        <ul class="pagination pagination-sm mb-0">
            <!-- Première page -->
            <li class="page-item <?= $pagination['currentPage'] == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= Pagination::buildUrl($baseUrl, $filters, 1) ?>" title="Première page">
                    <i class="bi bi-chevron-double-left"></i>
                </a>
            </li>
            
            <!-- Page précédente -->
            <li class="page-item <?= !$pagination['hasPrevious'] ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= Pagination::buildUrl($baseUrl, $filters, $pagination['currentPage'] - 1) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            
            <!-- Numéros de pages -->
            <?php foreach ($pagination['pages'] as $page): ?>
            <li class="page-item <?= $page == $pagination['currentPage'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= Pagination::buildUrl($baseUrl, $filters, $page) ?>"><?= $page ?></a>
            </li>
            <?php endforeach; ?>
            
            <!-- Page suivante -->
            <li class="page-item <?= !$pagination['hasNext'] ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= Pagination::buildUrl($baseUrl, $filters, $pagination['currentPage'] + 1) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            
            <!-- Dernière page -->
            <li class="page-item <?= $pagination['currentPage'] == $pagination['totalPages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= Pagination::buildUrl($baseUrl, $filters, $pagination['totalPages']) ?>" title="Dernière page">
                    <i class="bi bi-chevron-double-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>
