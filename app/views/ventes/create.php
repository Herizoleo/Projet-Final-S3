<?php ob_start(); ?>

<div class="mb-4">
    <a href="/ventes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour aux ventes
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash-coin me-2"></i>Vendre un Don
            </div>
            <div class="card-body">
                <?php if (empty($donsVendables)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Aucun don disponible à la vente.</strong><br>
                    <small>Un don peut être vendu uniquement s'il n'y a plus de besoins en attente pour ce type d'article.</small>
                </div>
                <a href="/dons" class="btn btn-primary">
                    <i class="bi bi-box me-1"></i>Voir les dons
                </a>
                <?php else: ?>
                
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Les dons vendus seront convertis en argent avec une réduction de <strong><?= $pourcentage_reduction ?>%</strong> sur le prix unitaire.
                </div>
                
                <form action="/ventes/store" method="POST">
                    <div class="mb-3">
                        <label for="don_id" class="form-label">Don à vendre <span class="text-danger">*</span></label>
                        <select class="form-select" id="don_id" name="don_id" required onchange="updatePriceInfo()">
                            <option value="">-- Sélectionner un don --</option>
                            <?php foreach ($donsVendables as $don): ?>
                            <option value="<?= $don['id'] ?>" 
                                    data-prix="<?= $don['prix_unitaire'] ?>"
                                    data-unite="<?= $don['unite'] ?>"
                                    data-max="<?= $don['quantite_disponible'] ?>">
                                <?= htmlspecialchars($don['article_nom']) ?> 
                                (<?= number_format($don['quantite_disponible'], 0, ',', ' ') ?> <?= $don['unite'] ?> disponibles)
                                - <?= number_format($don['prix_unitaire'], 0, ',', ' ') ?> Ar/<?= $don['unite'] ?>
                                <?php if ($don['donateur']): ?> - Don de <?= htmlspecialchars($don['donateur']) ?><?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantite" class="form-label">Quantité à vendre <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantite" name="quantite" 
                                   min="1" step="0.01" required onchange="calculateTotal()" oninput="calculateTotal()">
                            <small class="text-muted" id="quantite_max_info"></small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="pourcentage_reduction" class="form-label">Pourcentage de réduction</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="pourcentage_reduction" name="pourcentage_reduction" 
                                       min="0" max="100" step="0.1" value="<?= $pourcentage_reduction ?>"
                                       onchange="calculateTotal()" oninput="calculateTotal()">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Par défaut: <?= $pourcentage_reduction ?>%</small>
                        </div>
                    </div>
                    
                    <!-- Récapitulatif des prix -->
                    <div class="card bg-light mb-3" id="price_summary" style="display: none;">
                        <div class="card-body">
                            <h6 class="card-title">Récapitulatif</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Prix unitaire original:</small><br>
                                    <span id="prix_original">-</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Prix unitaire après réduction:</small><br>
                                    <span id="prix_reduit" class="text-success">-</span>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Montant total à recevoir:</strong>
                                <strong class="text-success fs-5" id="montant_total">0 Ar</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" 
                                  placeholder="Raison de la vente, acheteur..."></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-cash-coin me-1"></i>Confirmer la Vente
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updatePriceInfo() {
    const select = document.getElementById('don_id');
    const option = select.options[select.selectedIndex];
    const quantiteInput = document.getElementById('quantite');
    const maxInfo = document.getElementById('quantite_max_info');
    
    if (option.value) {
        const max = parseFloat(option.dataset.max);
        const unite = option.dataset.unite;
        quantiteInput.max = max;
        maxInfo.textContent = `Maximum: ${max.toLocaleString('fr-FR')} ${unite}`;
        document.getElementById('price_summary').style.display = 'block';
        calculateTotal();
    } else {
        maxInfo.textContent = '';
        document.getElementById('price_summary').style.display = 'none';
    }
}

function calculateTotal() {
    const select = document.getElementById('don_id');
    const option = select.options[select.selectedIndex];
    
    if (!option.value) return;
    
    const prixOriginal = parseFloat(option.dataset.prix);
    const unite = option.dataset.unite;
    const quantite = parseFloat(document.getElementById('quantite').value) || 0;
    const reduction = parseFloat(document.getElementById('pourcentage_reduction').value) || 0;
    
    const prixReduit = prixOriginal * (1 - reduction / 100);
    const montantTotal = prixReduit * quantite;
    
    document.getElementById('prix_original').textContent = prixOriginal.toLocaleString('fr-FR') + ' Ar/' + unite;
    document.getElementById('prix_reduit').textContent = prixReduit.toLocaleString('fr-FR') + ' Ar/' + unite + ' (-' + reduction + '%)';
    document.getElementById('montant_total').textContent = montantTotal.toLocaleString('fr-FR') + ' Ar';
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
