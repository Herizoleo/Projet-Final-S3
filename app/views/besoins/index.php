<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
    <a href="/besoins/create" class="btn btn-primary">
        <i class="bi bi-plus me-1"></i>Enregistrer un Besoin
    </a>
</div>

<!-- Filtres de recherche -->
<div class="card mb-4">
    <div class="card-header py-2">
        <a class="text-decoration-none d-flex justify-content-between align-items-center" 
           data-bs-toggle="collapse" href="#filtresCollapse" role="button">
            <span><i class="bi bi-funnel me-2"></i>Filtres de recherche</span>
            <i class="bi bi-chevron-down"></i>
        </a>
    </div>
    <div class="collapse <?= array_filter($filters) ? 'show' : '' ?>" id="filtresCollapse">
        <div class="card-body">
            <form method="GET" action="/besoins">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Recherche</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Ville, article, région..." value="<?= htmlspecialchars($filters['search']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Statut</label>
                        <select name="statut" class="form-select form-select-sm">
                            <option value="">Tous</option>
                            <option value="en_attente" <?= $filters['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="partiel" <?= $filters['statut'] == 'partiel' ? 'selected' : '' ?>>Partiel</option>
                            <option value="satisfait" <?= $filters['statut'] == 'satisfait' ? 'selected' : '' ?>>Satisfait</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Catégorie</label>
                        <select name="categorie_id" class="form-select form-select-sm">
                            <option value="">Toutes</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filters['categorie_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Ville</label>
                        <select name="ville_id" class="form-select form-select-sm">
                            <option value="">Toutes</option>
                            <?php foreach ($villes as $ville): ?>
                            <option value="<?= $ville['id'] ?>" <?= $filters['ville_id'] == $ville['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ville['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date début</label>
                        <input type="date" name="date_debut" class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($filters['date_debut']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date fin</label>
                        <input type="date" name="date_fin" class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($filters['date_fin']) ?>">
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Rechercher
                        </button>
                        <a href="/besoins" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Article</th>
                        <th>Catégorie</th>
                        <th>Qté Nécessaire</th>
                        <th>Qté Reçue</th>
                        <th>Reste</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($besoins)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard fs-1 d-block mb-2"></i>
                            Aucun besoin trouvé
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($besoins as $besoin): ?>
                        <tr>
                            <td><?= $besoin['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($besoin['ville_nom']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($besoin['region_nom']) ?></small>
                            </td>
                            <td><strong><?= htmlspecialchars($besoin['article_nom']) ?></strong></td>
                            <td>
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($besoin['categorie_nom']) ?></span>
                            </td>
                            <td><?= number_format($besoin['quantite_necessaire'], 2) ?> <?= $besoin['unite'] ?></td>
                            <td class="text-success"><?= number_format($besoin['quantite_recue'], 2) ?> <?= $besoin['unite'] ?></td>
                            <td class="<?= $besoin['reste'] > 0 ? 'text-danger fw-bold' : 'text-success' ?>">
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
                            <td class="text-end">
                                <?php if ($besoin['statut'] != 'satisfait'): ?>
                                <a href="/distributions/create/<?= $besoin['id'] ?>" class="btn btn-success btn-sm" title="Distribuer">
                                    <i class="bi bi-truck"></i>
                                </a>
                                <?php endif; ?>
                                <a href="/besoins/<?= $besoin['id'] ?>" class="btn btn-info btn-sm" title="Détails">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/besoins/edit/<?= $besoin['id'] ?>" class="btn btn-warning btn-sm" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/besoins/delete/<?= $besoin['id'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce besoin ?')" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="px-3">
            <?php 
            $baseUrl = '/besoins';
            include __DIR__ . '/../components/pagination.php'; 
            ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
