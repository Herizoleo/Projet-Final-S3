<?php ob_start(); ?>

<div class="mb-4">
    <a href="/dons" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gift me-2"></i>Enregistrer un Don
            </div>
            <div class="card-body">
                <form action="/dons/store" method="POST">
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
                        <label for="quantite_totale" class="form-label">Quantité <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantite_totale" name="quantite_totale" 
                               min="0.01" step="0.01" required placeholder="Ex: 100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="donateur" class="form-label">Donateur</label>
                        <input type="text" class="form-control" id="donateur" name="donateur" 
                               placeholder="Nom du donateur (optionnel)">
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_reception" class="form-label">Date de réception</label>
                        <input type="date" class="form-control" id="date_reception" name="date_reception" 
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Notes ou description du don (optionnel)"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer le Don
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
