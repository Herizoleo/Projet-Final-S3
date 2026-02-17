<?php ob_start(); ?>

<div class="mb-4">
    <a href="/distributions" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-truck me-2"></i>Nouvelle Distribution
            </div>
            <div class="card-body">
                <?php if (empty($besoins)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                    <h5>Tous les besoins sont satisfaits !</h5>
                    <p>Il n'y a actuellement aucun besoin en attente de distribution.</p>
                    <a href="/besoins/create" class="btn btn-primary">
                        <i class="bi bi-plus me-1"></i>Enregistrer un nouveau besoin
                    </a>
                </div>
                <?php else: ?>
                <p class="text-muted mb-4">Sélectionnez un besoin pour effectuer une distribution:</p>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th>Article</th>
                                <th>Besoin</th>
                                <th>Reçu</th>
                                <th>Reste</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($besoins as $besoin): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($besoin['ville_nom']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($besoin['region_nom']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($besoin['article_nom']) ?>
                                    <br><span class="badge bg-light text-dark"><?= htmlspecialchars($besoin['categorie_nom']) ?></span>
                                </td>
                                <td><?= number_format($besoin['quantite_necessaire'], 2) ?> <?= $besoin['unite'] ?></td>
                                <td class="text-success"><?= number_format($besoin['quantite_recue'], 2) ?> <?= $besoin['unite'] ?></td>
                                <td class="text-danger fw-bold"><?= number_format($besoin['reste'], 2) ?> <?= $besoin['unite'] ?></td>
                                <td>
                                    <?php
                                    $badgeClass = $besoin['statut'] == 'partiel' ? 'bg-warning' : 'bg-danger';
                                    $statusText = $besoin['statut'] == 'partiel' ? 'Partiel' : 'En attente';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <a href="/distributions/create/<?= $besoin['id'] ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-truck me-1"></i>Distribuer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
