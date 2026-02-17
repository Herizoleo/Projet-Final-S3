<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
    <a href="/distributions/create" class="btn btn-primary">
        <i class="bi bi-plus me-1"></i>Nouvelle Distribution
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
            <form method="GET" action="/distributions">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Recherche</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Ville, article, donateur..." value="<?= htmlspecialchars($filters['search']) ?>">
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
                        <a href="/distributions" class="btn btn-outline-secondary btn-sm">
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
                        <th>Date</th>
                        <th>Ville</th>
                        <th>Article</th>
                        <th>Quantité</th>
                        <th>Donateur</th>
                        <th>Notes</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($distributions)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-truck fs-1 d-block mb-2"></i>
                            Aucune distribution trouvée
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($distributions as $dist): ?>
                        <tr>
                            <td><?= $dist['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($dist['date_distribution'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($dist['ville_nom']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($dist['region_nom']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($dist['article_nom']) ?></strong>
                                <br><span class="badge bg-light text-dark"><?= htmlspecialchars($dist['categorie_nom']) ?></span>
                            </td>
                            <td><strong><?= number_format($dist['quantite'], 2) ?> <?= $dist['unite'] ?></strong></td>
                            <td><?= htmlspecialchars($dist['donateur'] ?? 'Anonyme') ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($dist['notes'] ?? '-') ?></small></td>
                            <td class="text-end">
                                <form action="/distributions/delete/<?= $dist['id'] ?>" method="POST" style="display:inline"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette distribution ?')">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="px-3">
            <?php 
            $baseUrl = '/distributions';
            include __DIR__ . '/../components/pagination.php'; 
            ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
