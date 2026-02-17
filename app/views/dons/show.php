<?php ob_start(); ?>

<div class="mb-4">
    <a href="/dons" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row g-4">
    <!-- Détails du don -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gift me-2"></i>Détails du Don
            </div>
            <div class="card-body">
                <h5><?= htmlspecialchars($don['article_nom']) ?></h5>
                <span class="badge bg-light text-dark mb-3">
                    <?= htmlspecialchars($don['categorie_nom']) ?>
                </span>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Quantité totale</span>
                    <strong><?= number_format($don['quantite_totale'], 2) ?> <?= $don['unite'] ?></strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Disponible</span>
                    <span class="badge bg-<?= $don['quantite_disponible'] > 0 ? 'success' : 'secondary' ?>">
                        <?= number_format($don['quantite_disponible'], 2) ?> <?= $don['unite'] ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Distribué</span>
                    <span class="text-info">
                        <?= number_format($don['quantite_totale'] - $don['quantite_disponible'], 2) ?> <?= $don['unite'] ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Donateur</span>
                    <span><?= htmlspecialchars($don['donateur'] ?? 'Anonyme') ?></span>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Date réception</span>
                    <span><?= date('d/m/Y', strtotime($don['date_reception'])) ?></span>
                </div>
                
                <?php if ($don['description']): ?>
                <div class="mt-3">
                    <span class="text-muted">Description:</span>
                    <p class="mt-1 small"><?= nl2br(htmlspecialchars($don['description'])) ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Barre de progression -->
                <div class="mt-3">
                    <?php 
                    $pourcentageUtilise = $don['quantite_totale'] > 0 
                        ? (($don['quantite_totale'] - $don['quantite_disponible']) / $don['quantite_totale']) * 100 
                        : 0;
                    ?>
                    <small class="text-muted">Utilisation: <?= number_format($pourcentageUtilise, 1) ?>%</small>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-info" style="width: <?= $pourcentageUtilise ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="/dons/edit/<?= $don['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <?php if ($don['quantite_disponible'] > 0 && $don['categorie_nom'] != 'Argent' && !empty($don['prix_unitaire'])): ?>
                <a href="/ventes/create/<?= $don['id'] ?>" class="btn btn-success btn-sm" id="btn-vendre">
                    <i class="bi bi-cash-coin me-1"></i>Vendre
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Historique des distributions -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Distributions de ce Don
            </div>
            <div class="card-body p-0">
                <?php if (!empty($distributions)): ?>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Ville</th>
                            <th>Article</th>
                            <th>Quantité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($distributions as $dist): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($dist['date_distribution'])) ?></td>
                            <td><?= htmlspecialchars($dist['ville_nom']) ?></td>
                            <td><?= htmlspecialchars($dist['article_nom']) ?></td>
                            <td><strong><?= number_format($dist['quantite'], 2) ?> <?= $don['unite'] ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-truck fs-1 d-block mb-2"></i>
                    Ce don n'a pas encore été distribué
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
