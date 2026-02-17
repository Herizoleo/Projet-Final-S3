<?php ob_start(); ?>

<div class="mb-4">
    <a href="/besoins" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-plus me-2"></i>Enregistrer un Besoin
            </div>
            <div class="card-body">
                <form action="/besoins/store" method="POST">
                    <div class="mb-3">
                        <label for="ville_id" class="form-label">Ville <span class="text-danger">*</span></label>
                        <select class="form-select" id="ville_id" name="ville_id" required>
                            <option value="">-- Sélectionner une ville --</option>
                            <?php foreach ($villes as $ville): ?>
                            <option value="<?= $ville['id'] ?>">
                                <?= htmlspecialchars($ville['nom']) ?> (<?= htmlspecialchars($ville['region_nom']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type_article_id" class="form-label">Type d'article <span class="text-danger">*</span></label>
                        <select class="form-select" id="type_article_id" name="type_article_id" required>
                            <option value="">-- Sélectionner un article --</option>
                            <?php 
                            $currentCategorie = '';
                            foreach ($types as $type): 
                                if ($currentCategorie != $type['categorie_nom']):
                                    if ($currentCategorie != '') echo '</optgroup>';
                                    $currentCategorie = $type['categorie_nom'];
                            ?>
                            <optgroup label="<?= htmlspecialchars($currentCategorie) ?>">
                            <?php endif; ?>
                                <option value="<?= $type['id'] ?>">
                                    <?= htmlspecialchars($type['nom']) ?> (<?= $type['unite'] ?>)
                                </option>
                            <?php endforeach; ?>
                            <?php if ($currentCategorie != '') echo '</optgroup>'; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantite_necessaire" class="form-label">Quantité nécessaire <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantite_necessaire" name="quantite_necessaire" 
                               min="0.01" step="0.01" required placeholder="Ex: 100">
                    </div>
                    
                    <div class="mb-4">
                        <label for="date_enregistrement" class="form-label">Date d'enregistrement</label>
                        <input type="date" class="form-control" id="date_enregistrement" name="date_enregistrement" 
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer le Besoin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
