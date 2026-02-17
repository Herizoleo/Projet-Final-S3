<?php ob_start(); ?>

<div class="mb-4">
    <a href="/besoins" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row g-4">
    <!-- Détails du besoin -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Détails du Besoin
            </div>
            <div class="card-body">
                <h5><?= htmlspecialchars($besoin['article_nom']) ?></h5>
                <span class="badge bg-light text-dark mb-3">
                    <?= htmlspecialchars($besoin['categorie_nom']) ?>
                </span>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Ville</span>
                    <strong><?= htmlspecialchars($besoin['ville_nom']) ?></strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Région</span>
                    <span><?= htmlspecialchars($besoin['region_nom']) ?></span>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Quantité nécessaire</span>
                    <strong><?= number_format($besoin['quantite_necessaire'], 2) ?> <?= $besoin['unite'] ?></strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Quantité reçue</span>
                    <span class="text-success"><?= number_format($besoin['quantite_recue'], 2) ?> <?= $besoin['unite'] ?></span>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Reste à recevoir</span>
                    <span class="<?= $besoin['reste'] > 0 ? 'text-danger fw-bold' : 'text-success' ?>">
                        <?= number_format($besoin['reste'], 2) ?> <?= $besoin['unite'] ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Statut</span>
                    <?php
                    $badgeClass = 'bg-danger';
                    $statusText = 'En attente';
                    if ($besoin['statut'] == 'satisfait') {
                        $badgeClass = 'bg-success';
                        $statusText = 'Satisfait';
                    } elseif ($besoin['statut'] == 'partiel') {
                        $badgeClass = 'bg-warning';
                        $statusText = 'Partiel';
                    }
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                </div>
                
                <!-- Barre de progression -->
                <div class="mt-3">
                    <?php 
                    $pourcentage = $besoin['quantite_necessaire'] > 0 
                        ? min(100, ($besoin['quantite_recue'] / $besoin['quantite_necessaire']) * 100) 
                        : 0;
                    $progressClass = 'bg-danger';
                    if ($pourcentage >= 100) $progressClass = 'bg-success';
                    elseif ($pourcentage >= 50) $progressClass = 'bg-warning';
                    ?>
                    <small class="text-muted">Progression: <?= number_format($pourcentage, 1) ?>%</small>
                    <div class="progress mt-1">
                        <div class="progress-bar <?= $progressClass ?>" style="width: <?= $pourcentage ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?php if ($besoin['statut'] != 'satisfait'): ?>
                <a href="/distributions/create/<?= $besoin['id'] ?>" class="btn btn-success btn-sm">
                    <i class="bi bi-truck me-1"></i>Distribuer
                </a>
                <?php if (isset($prix_unitaire) && $prix_unitaire): ?>
                <a href="/achats/create/<?= $besoin['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-cart-check me-1"></i>Acheter
                </a>
                <?php endif; ?>
                <?php endif; ?>
                <a href="/besoins/edit/<?= $besoin['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
            </div>
        </div>
    </div>
    
    <!-- Historique des distributions -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-truck me-2"></i>Historique des Distributions
            </div>
            <div class="card-body p-0">
                <?php if (!empty($distributions)): ?>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Quantité</th>
                            <th>Donateur</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($distributions as $dist): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($dist['date_distribution'])) ?></td>
                            <td><strong><?= number_format($dist['quantite'], 2) ?> <?= $besoin['unite'] ?></strong></td>
                            <td><?= htmlspecialchars($dist['donateur'] ?? 'Anonyme') ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($dist['notes'] ?? '-') ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-truck fs-3 d-block mb-2"></i>
                    Aucune distribution pour ce besoin
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Historique des achats -->
        <?php if (isset($prix_unitaire) && $prix_unitaire): ?>
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cart-check me-2"></i>Historique des Achats
            </div>
            <div class="card-body p-0">
                <?php if (!empty($achats)): ?>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Quantité</th>
                            <th>Montant</th>
                            <th>Donateur (Argent)</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($achats as $achat): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($achat['date_achat'])) ?></td>
                            <td><strong><?= number_format($achat['quantite'], 2) ?> <?= $besoin['unite'] ?></strong></td>
                            <td class="text-primary"><?= number_format($achat['montant_total'], 0, ',', ' ') ?> Ar</td>
                            <td><?= htmlspecialchars($achat['donateur'] ?? 'Anonyme') ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($achat['notes'] ?? '-') ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-cart fs-3 d-block mb-2"></i>
                    Aucun achat pour ce besoin
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
