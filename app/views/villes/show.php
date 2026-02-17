<?php ob_start(); ?>

<div class="mb-4">
    <a href="/villes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row g-4">
    <!-- Informations de la ville -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Informations
            </div>
            <div class="card-body">
                <h4><?= htmlspecialchars($ville['nom']) ?></h4>
                <p class="text-muted mb-3">
                    <i class="bi bi-map me-1"></i><?= htmlspecialchars($ville['region_nom']) ?>
                </p>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Population sinistrée</span>
                    <strong><?= number_format($ville['population_sinistree']) ?></strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total besoins</span>
                    <strong><?= $stats['total_besoins'] ?? 0 ?></strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted text-success">Satisfaits</span>
                    <span class="badge bg-success"><?= $stats['besoins_satisfaits'] ?? 0 ?></span>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted text-warning">Partiels</span>
                    <span class="badge bg-warning"><?= $stats['besoins_partiels'] ?? 0 ?></span>
                </div>
                
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted text-danger">En attente</span>
                    <span class="badge bg-danger"><?= $stats['besoins_en_attente'] ?? 0 ?></span>
                </div>
            </div>
            <div class="card-footer">
                <a href="/villes/edit/<?= $ville['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
            </div>
        </div>
    </div>
    
    <!-- Liste des besoins -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clipboard-check me-2"></i>Besoins de la ville</span>
                <a href="/besoins/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus me-1"></i>Ajouter
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($besoins)): ?>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Catégorie</th>
                            <th>Besoin</th>
                            <th>Reçu</th>
                            <th>Reste</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($besoins as $besoin): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($besoin['article_nom']) ?></strong></td>
                            <td><span class="badge bg-light text-dark"><?= htmlspecialchars($besoin['categorie_nom']) ?></span></td>
                            <td><?= number_format($besoin['quantite_necessaire'], 2) ?> <?= $besoin['unite'] ?></td>
                            <td class="text-success"><?= number_format($besoin['quantite_recue'], 2) ?> <?= $besoin['unite'] ?></td>
                            <td class="<?= $besoin['reste_a_recevoir'] > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($besoin['reste_a_recevoir'], 2) ?> <?= $besoin['unite'] ?>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <?php if ($besoin['statut'] != 'satisfait'): ?>
                                <a href="/distributions/create/<?= $besoin['id'] ?>" class="btn btn-success btn-sm" title="Distribuer">
                                    <i class="bi bi-truck"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Aucun besoin enregistré pour cette ville
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
