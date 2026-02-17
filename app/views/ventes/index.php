<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/" class="btn btn-outline-secondary btn-sm me-2">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
    <div>
        <a href="/ventes/config" class="btn btn-outline-secondary btn-sm me-2">
            <i class="bi bi-gear me-1"></i>Configuration
        </a>
        <a href="/ventes/create" class="btn btn-success">
            <i class="bi bi-cash-coin me-1"></i>Vendre un Don
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3><?= $stats['nb_ventes'] ?? 0 ?></h3>
                <small>Ventes effectuées</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?= number_format($stats['total_montant'] ?? 0, 0, ',', ' ') ?> Ar</h3>
                <small>Montant total généré</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3><?= $pourcentage_reduction ?>%</h3>
                <small>Réduction appliquée</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header">
        <a class="text-decoration-none" data-bs-toggle="collapse" href="#filtres">
            <i class="bi bi-funnel me-2"></i>Filtres de recherche
        </a>
    </div>
    <div class="collapse <?= array_filter($filters) ? 'show' : '' ?>" id="filtres">
        <div class="card-body">
            <form method="GET" action="/ventes">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Article, donateur...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date début</label>
                        <input type="date" class="form-control" name="date_debut" 
                               value="<?= $filters['date_debut'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date fin</label>
                        <input type="date" class="form-control" name="date_fin" 
                               value="<?= $filters['date_fin'] ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Liste des ventes -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul me-2"></i>Historique des Ventes
        <span class="badge bg-secondary float-end"><?= $pagination['totalItems'] ?> vente(s)</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ventes)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-cash-coin" style="font-size: 3rem;"></i>
            <p class="mt-3">Aucune vente enregistrée</p>
            <a href="/ventes/create" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i>Effectuer une vente
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Article</th>
                        <th>Catégorie</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">Prix original</th>
                        <th class="text-end">Réduction</th>
                        <th class="text-end">Prix vente</th>
                        <th class="text-end">Montant total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventes as $vente): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($vente['date_vente'])) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($vente['article_nom']) ?></strong>
                            <?php if ($vente['donateur']): ?>
                            <br><small class="text-muted">Don de: <?= htmlspecialchars($vente['donateur']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($vente['categorie_nom']) ?></span></td>
                        <td class="text-end"><?= number_format($vente['quantite'], 0, ',', ' ') ?> <?= $vente['unite'] ?></td>
                        <td class="text-end"><?= number_format($vente['prix_unitaire_original'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end text-danger">-<?= $vente['pourcentage_reduction'] ?>%</td>
                        <td class="text-end"><?= number_format($vente['prix_vente'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end"><strong class="text-success"><?= number_format($vente['montant_total'], 0, ',', ' ') ?> Ar</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($pagination['totalPages'] > 1): ?>
<?php include __DIR__ . '/../components/pagination.php'; ?>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
