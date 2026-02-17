<?php ob_start(); ?>

<!-- Statistiques -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card primary">
            <h3><?= number_format($stats['total_argent_disponible'], 0, ',', ' ') ?> Ar</h3>
            <p>Argent Disponible</p>
            <i class="bi bi-cash-stack"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card info">
            <h3><?= $stats['total_villes'] ?></h3>
            <p>Villes Concernées</p>
            <i class="bi bi-geo-alt"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card success">
            <h3><?= $stats['total_dons'] ?></h3>
            <p>Dons Reçus</p>
            <i class="bi bi-gift"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card warning">
            <h3><?= $stats['total_distributions'] ?></h3>
            <p>Distributions</p>
            <i class="bi bi-truck"></i>
        </div>
    </div>
</div>

<!-- Statistiques Besoins -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card danger">
            <h3><?= $stats['besoins_en_attente'] ?></h3>
            <p>Besoins en Attente</p>
            <i class="bi bi-hourglass-split"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card secondary">
            <h3><?= $stats['besoins_partiels'] ?></h3>
            <p>Partiellement Satisfaits</p>
            <i class="bi bi-pie-chart"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <h3><?= $stats['besoins_satisfaits'] ?></h3>
            <p>Besoins Satisfaits</p>
            <i class="bi bi-check-circle"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <h3><?= number_format($stats['montant_achats'], 0, ',', ' ') ?></h3>
            <p>Ar en Achats</p>
            <i class="bi bi-cart-check"></i>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Liste des Villes avec Besoins -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt me-2"></i>Villes et Besoins</span>
                <a href="/besoins/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus me-1"></i>Nouveau Besoin
                </a>
            </div>
            <div class="card-body p-0">
                <div class="accordion" id="accordionVilles">
                    <?php foreach ($villes as $index => $ville): ?>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#ville<?= $ville['id'] ?>">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <div>
                                        <strong><?= htmlspecialchars($ville['ville_nom']) ?></strong>
                                        <small class="text-muted ms-2"><?= htmlspecialchars($ville['region_nom']) ?></small>
                                        <?php if ($ville['total_achats'] > 0): ?>
                                        <span class="badge bg-info ms-2">
                                            <i class="bi bi-cart-check me-1"></i><?= number_format($ville['total_achats'], 0, ',', ' ') ?> Ar
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?php if ($ville['besoins_en_attente'] > 0): ?>
                                            <span class="badge bg-danger"><?= $ville['besoins_en_attente'] ?> en attente</span>
                                        <?php endif; ?>
                                        <?php if ($ville['besoins_partiels'] > 0): ?>
                                            <span class="badge bg-warning"><?= $ville['besoins_partiels'] ?> partiel</span>
                                        <?php endif; ?>
                                        <?php if ($ville['besoins_satisfaits'] > 0): ?>
                                            <span class="badge bg-success"><?= $ville['besoins_satisfaits'] ?> satisfait</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="ville<?= $ville['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                             data-bs-parent="#accordionVilles">
                            <div class="accordion-body p-0">
                                <?php if (!empty($detailsParVille[$ville['id']])): ?>
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
                                        <?php foreach ($detailsParVille[$ville['id']] as $besoin): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($besoin['article_nom']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars($besoin['categorie_nom']) ?></span>
                                            </td>
                                            <td><?= number_format($besoin['quantite_necessaire'], 2) ?> <?= $besoin['unite'] ?></td>
                                            <td class="text-success"><?= number_format($besoin['quantite_recue'], 2) ?> <?= $besoin['unite'] ?></td>
                                            <td class="<?= $besoin['reste'] > 0 ? 'text-danger' : 'text-success' ?>">
                                                <?= number_format($besoin['reste'], 2) ?> <?= $besoin['unite'] ?>
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
                                                <a href="/achats/create/<?= $besoin['id'] ?>" class="btn btn-info btn-sm" title="Acheter">
                                                    <i class="bi bi-cart-plus"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="/besoins/<?= $besoin['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Détails">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Aucun besoin enregistré pour cette ville
                                    <div class="mt-2">
                                        <a href="/besoins/create" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-plus me-1"></i>Ajouter un besoin
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Colonne latérale -->
    <div class="col-lg-4">
        <!-- Dons récents -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-gift me-2"></i>Dons Récents</span>
                <a href="/dons/create" class="btn btn-success btn-sm">
                    <i class="bi bi-plus"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($donsRecents)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($donsRecents as $don): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($don['article_nom']) ?></strong>
                            <br>
                            <small class="text-muted">
                                <?= number_format($don['quantite_totale'], 2) ?> <?= $don['unite'] ?>
                                <?php if ($don['donateur']): ?>
                                - <?= htmlspecialchars($don['donateur']) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <span class="badge bg-<?= $don['quantite_disponible'] > 0 ? 'success' : 'secondary' ?>">
                            <?= number_format($don['quantite_disponible'], 2) ?> dispo
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    Aucun don enregistré
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="/dons" class="btn btn-outline-primary btn-sm">Voir tous les dons</a>
            </div>
        </div>
        
        <!-- Distributions récentes -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-truck me-2"></i>Distributions Récentes
            </div>
            <div class="card-body p-0">
                <?php if (!empty($distributionsRecentes)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($distributionsRecentes as $dist): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($dist['ville_nom']) ?></strong>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($dist['date_distribution'])) ?></small>
                        </div>
                        <small>
                            <?= number_format($dist['quantite'], 2) ?> <?= $dist['unite'] ?> de <?= htmlspecialchars($dist['article_nom']) ?>
                        </small>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-truck fs-3 d-block mb-2"></i>
                    Aucune distribution
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="/distributions" class="btn btn-outline-primary btn-sm">Voir tout l'historique</a>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
