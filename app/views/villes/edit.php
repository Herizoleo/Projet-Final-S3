<?php ob_start(); ?>

<div class="mb-4">
    <a href="/villes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2"></i>Modifier la Ville
            </div>
            <div class="card-body">
                <form action="/villes/update/<?= $ville['id'] ?>" method="POST">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom de la ville <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" required 
                               value="<?= htmlspecialchars($ville['nom']) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="region_id" class="form-label">Région <span class="text-danger">*</span></label>
                        <select class="form-select" id="region_id" name="region_id" required>
                            <option value="">-- Sélectionner une région --</option>
                            <?php foreach ($regions as $region): ?>
                            <option value="<?= $region['id'] ?>" <?= $region['id'] == $ville['region_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($region['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="population_sinistree" class="form-label">Population sinistrée</label>
                        <input type="number" class="form-control" id="population_sinistree" name="population_sinistree" 
                               min="0" value="<?= $ville['population_sinistree'] ?>">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
