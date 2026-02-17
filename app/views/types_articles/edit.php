<?php ob_start(); ?>

<div class="mb-4">
    <a href="/categories" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour aux catégories
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil me-2"></i>Modifier le Type d'Article
            </div>
            <div class="card-body">
                <form action="/types-articles/update/<?= $type['id'] ?>" method="POST">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom de l'article <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" required 
                               value="<?= htmlspecialchars($type['nom']) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="categorie_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                        <select class="form-select" id="categorie_id" name="categorie_id" required>
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $type['categorie_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unite" class="form-label">Unité de mesure <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unite" name="unite" required 
                                   value="<?= htmlspecialchars($type['unite']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="prix_unitaire" class="form-label">Prix unitaire (Ar)</label>
                            <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire" 
                                   min="0" step="100" value="<?= $type['prix_unitaire'] ?? '' ?>">
                            <small class="text-muted">Laisser vide pour les dons en argent</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer
                        </button>
                        <a href="/types-articles/delete/<?= $type['id'] ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('Supprimer ce type d\'article ?')">
                            <i class="bi bi-trash me-1"></i>Supprimer
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
