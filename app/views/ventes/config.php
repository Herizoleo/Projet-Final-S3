<?php ob_start(); ?>

<div class="mb-4">
    <a href="/ventes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour aux ventes
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>Configuration des Ventes
            </div>
            <div class="card-body">
                <form action="/ventes/config" method="POST">
                    <div class="mb-4">
                        <label for="pourcentage_reduction" class="form-label">
                            Pourcentage de réduction lors de la vente
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-lg" id="pourcentage_reduction" 
                                   name="pourcentage_reduction" min="0" max="100" step="0.1" 
                                   value="<?= $pourcentage_reduction ?>">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">
                            Ce pourcentage sera déduit du prix unitaire lors de la vente d'un don.<br>
                            Exemple: Article à 10 000 Ar avec 10% de réduction = vendu à 9 000 Ar
                        </small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Valeur actuelle:</strong> <?= $pourcentage_reduction ?>%<br>
                        Un article de 100 000 Ar sera vendu à <?= number_format(100000 * (1 - $pourcentage_reduction/100), 0, ',', ' ') ?> Ar
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
