<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
    <a href="/achats/create" class="btn btn-primary">
        <i class="bi bi-cart-plus me-1"></i>Nouvel Achat
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
            <form method="GET" action="/achats">
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
                        <a href="/achats" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Résumé -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card info">
            <h3><?= $stats['total'] ?></h3>
            <p>Achats effectués</p>
            <i class="bi bi-cart-check"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card success">
            <h3><?= number_format($stats['total_montant'], 0, ',', ' ') ?></h3>
            <p>Ar dépensés</p>
            <i class="bi bi-cash-coin"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card primary">
            <h3><?= number_format($stats['total_quantite'], 2) ?></h3>
            <p>Articles achetés</p>
            <i class="bi bi-box-seam"></i>
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
                        <th>Prix Unit.</th>
                        <th>Montant Total</th>
                        <th>Donateur</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($achats)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-cart fs-1 d-block mb-2"></i>
                            Aucun achat trouvé
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($achats as $achat): ?>
                        <tr>
                            <td><?= $achat['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($achat['date_achat'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($achat['ville_nom']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($achat['region_nom']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($achat['article_nom']) ?>
                                <br><span class="badge bg-light text-dark"><?= htmlspecialchars($achat['categorie_nom']) ?></span>
                            </td>
                            <td>
                                <strong><?= number_format($achat['quantite'], 2) ?></strong> <?= $achat['unite'] ?>
                            </td>
                            <td><?= number_format($achat['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                            <td>
                                <span class="badge bg-success fs-6">
                                    <?= number_format($achat['montant_total'], 0, ',', ' ') ?> Ar
                                </span>
                            </td>
                            <td><?= htmlspecialchars($achat['donateur'] ?? 'Anonyme') ?></td>
                            <td class="text-end">
                                <form action="/achats/delete/<?= $achat['id'] ?>" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet achat ?');">
                                    <button type="submit" class="btn btn-sm btn-danger">
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
            $baseUrl = '/achats';
            include __DIR__ . '/../components/pagination.php'; 
            ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
