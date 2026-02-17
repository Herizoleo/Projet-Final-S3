<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
    <a href="/villes/create" class="btn btn-primary">
        <i class="bi bi-plus me-1"></i>Ajouter une Ville
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Région</th>
                    <th>Population Sinistrée</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($villes)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                        Aucune ville enregistrée
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($villes as $ville): ?>
                    <tr>
                        <td><?= $ville['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($ville['nom']) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-map me-1"></i><?= htmlspecialchars($ville['region_nom']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">
                                <i class="bi bi-people me-1"></i><?= number_format($ville['population_sinistree']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="/villes/<?= $ville['id'] ?>" class="btn btn-info btn-sm" title="Détails">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="/villes/edit/<?= $ville['id'] ?>" class="btn btn-warning btn-sm" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="/villes/delete/<?= $ville['id'] ?>" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette ville ?')" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
