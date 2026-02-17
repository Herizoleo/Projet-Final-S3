<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="/" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
    <div>
        <a href="/categories/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle Catégorie
        </a>
        <a href="/types-articles/create" class="btn btn-success btn-sm ms-2">
            <i class="bi bi-plus-lg me-1"></i>Nouveau Type d'Article
        </a>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($categories as $categorie): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-tag me-2"></i><?= htmlspecialchars($categorie['nom']) ?>
                </span>
                <div>
                    <span class="badge bg-primary me-2"><?= $categorie['nb_types'] ?> types</span>
                    <a href="/categories/edit/<?= $categorie['id'] ?>" class="btn btn-sm btn-outline-warning" title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <?php if ($categorie['nb_types'] == 0): ?>
                    <a href="/categories/delete/<?= $categorie['id'] ?>" class="btn btn-sm btn-outline-danger" 
                       onclick="return confirm('Supprimer cette catégorie ?')" title="Supprimer">
                        <i class="bi bi-trash"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if ($categorie['description']): ?>
                <p class="text-muted small"><?= htmlspecialchars($categorie['description']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($typesParCategorie[$categorie['id']])): ?>
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Unité</th>
                            <th class="text-end">Prix</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($typesParCategorie[$categorie['id']] as $type): ?>
                        <tr>
                            <td><?= htmlspecialchars($type['nom']) ?></td>
                            <td><span class="badge bg-light text-dark"><?= $type['unite'] ?></span></td>
                            <td class="text-end">
                                <?php if ($type['prix_unitaire']): ?>
                                <strong><?= number_format($type['prix_unitaire'], 0, ',', ' ') ?></strong> Ar
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/types-articles/edit/<?= $type['id'] ?>" class="btn btn-sm btn-link p-0" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted text-center py-3">Aucun type d'article</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
