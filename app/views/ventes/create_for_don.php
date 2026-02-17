<?php ob_start(); ?>

<div class="mb-4">
    <a href="/dons/<?= $don['id'] ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour au don
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash-coin me-2"></i>Vendre: <?= htmlspecialchars($don['article_nom']) ?>
            </div>
            <div class="card-body">
                <!-- Infos du don -->
                <div class="alert alert-light border mb-4">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Article</small><br>
                            <strong><?= htmlspecialchars($don['article_nom']) ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Catégorie</small><br>
                            <span class="badge bg-secondary"><?= htmlspecialchars($don['categorie_nom']) ?></span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-4">
                            <small class="text-muted">Disponible</small><br>
                            <strong class="text-primary"><?= number_format($quantite_max, 0, ',', ' ') ?> <?= $don['unite'] ?></strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Prix unitaire</small><br>
                            <strong><?= number_format($don['prix_unitaire'], 0, ',', ' ') ?> Ar</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">Valeur totale</small><br>
                            <strong><?= number_format($don['prix_unitaire'] * $quantite_max, 0, ',', ' ') ?> Ar</strong>
                        </div>
                    </div>
                    <?php if ($don['donateur']): ?>
                    <hr>
                    <small class="text-muted">Donateur: <?= htmlspecialchars($don['donateur']) ?></small>
                    <?php endif; ?>
                </div>
                
                <form action="/ventes/store" method="POST">
                    <input type="hidden" name="don_id" value="<?= $don['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantite" class="form-label">Quantité à vendre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="quantite" name="quantite" 
                                       min="1" max="<?= $quantite_max ?>" step="0.01" required
                                       value="<?= $quantite_max ?>"
                                       onchange="calculateTotal()" oninput="calculateTotal()">
                                <span class="input-group-text"><?= $don['unite'] ?></span>
                            </div>
                            <small class="text-muted">Max: <?= number_format($quantite_max, 0, ',', ' ') ?> <?= $don['unite'] ?></small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="pourcentage_reduction" class="form-label">Réduction</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="pourcentage_reduction" name="pourcentage_reduction" 
                                       min="0" max="100" step="0.1" value="<?= $pourcentage_reduction ?>"
                                       onchange="calculateTotal()" oninput="calculateTotal()">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calcul -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted">Prix original</small><br>
                                    <span id="prix_original"><?= number_format($don['prix_unitaire'], 0, ',', ' ') ?></span> Ar
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Prix après réduction</small><br>
                                    <span id="prix_reduit" class="text-success"><?= number_format($don['prix_unitaire'] * (1 - $pourcentage_reduction/100), 0, ',', ' ') ?></span> Ar
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Montant total</small><br>
                                    <strong class="text-success fs-5" id="montant_total"><?= number_format($don['prix_unitaire'] * (1 - $pourcentage_reduction/100) * $quantite_max, 0, ',', ' ') ?></strong> Ar
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-lg me-1"></i>Confirmer la Vente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const prixUnitaire = <?= $don['prix_unitaire'] ?>;

function calculateTotal() {
    const quantite = parseFloat(document.getElementById('quantite').value) || 0;
    const reduction = parseFloat(document.getElementById('pourcentage_reduction').value) || 0;
    
    const prixReduit = prixUnitaire * (1 - reduction / 100);
    const montantTotal = prixReduit * quantite;
    
    document.getElementById('prix_reduit').textContent = prixReduit.toLocaleString('fr-FR');
    document.getElementById('montant_total').textContent = montantTotal.toLocaleString('fr-FR');
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
