<?php ob_start(); ?>

<div class="mb-4">
    <a href="/achats" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="row g-4">
    <!-- Informations du besoin -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-clipboard-check me-2"></i>Besoin à Satisfaire
            </div>
            <div class="card-body">
                <h5><?= htmlspecialchars($besoin['article_nom']) ?></h5>
                <span class="badge bg-light text-dark mb-3"><?= htmlspecialchars($besoin['categorie_nom']) ?></span>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Ville</span>
                    <strong><?= htmlspecialchars($besoin['ville_nom']) ?></strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Prix unitaire</span>
                    <strong class="text-primary"><?= number_format($prix_unitaire, 0, ',', ' ') ?> Ar/<?= $besoin['unite'] ?></strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Quantité nécessaire</span>
                    <strong><?= number_format($besoin['quantite_necessaire'], 2) ?> <?= $besoin['unite'] ?></strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Déjà reçu</span>
                    <span class="text-success"><?= number_format($besoin['quantite_recue'], 2) ?> <?= $besoin['unite'] ?></span>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom bg-light px-2 rounded">
                    <span class="text-danger fw-bold">Reste à recevoir</span>
                    <span class="text-danger fw-bold"><?= number_format($besoin['reste'], 2) ?> <?= $besoin['unite'] ?></span>
                </div>
                
                <div class="d-flex justify-content-between py-2 mt-2 bg-warning bg-opacity-10 px-2 rounded">
                    <span class="fw-bold">Coût total restant</span>
                    <span class="fw-bold text-warning"><?= number_format($besoin['reste'] * $prix_unitaire, 0, ',', ' ') ?> Ar</span>
                </div>
            </div>
        </div>
        
        <!-- Argent disponible -->
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash-coin me-2"></i>Argent Disponible
            </div>
            <div class="card-body text-center">
                <h2 class="text-success mb-0"><?= number_format($totalArgent, 0, ',', ' ') ?></h2>
                <p class="text-muted mb-0">Ariary</p>
            </div>
        </div>
    </div>
    
    <!-- Formulaire d'achat -->
    <div class="col-lg-8">
        <?php if (empty($donsArgent)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-exclamation-triangle fs-1 d-block mb-3 text-warning"></i>
                <h5>Aucun don en argent disponible</h5>
                <p class="text-muted">Pour effectuer des achats, il faut d'abord recevoir des dons en argent (Ariary).</p>
                <a href="/dons/create" class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i>Enregistrer un don
                </a>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Zone de notification -->
        <div class="alert alert-danger alert-dismissible d-none" id="topAlertError" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Erreur:</strong> <span id="topAlertErrorMessage"></span>
            <button type="button" class="btn-close" onclick="hideTopAlert()"></button>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cart-plus me-2"></i>Effectuer l'Achat
            </div>
            <div class="card-body">
                <form action="/achats/store" method="POST" id="achatForm">
                    <input type="hidden" name="besoin_id" value="<?= $besoin['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="don_id" class="form-label">Don en argent à utiliser <span class="text-danger">*</span></label>
                        <select class="form-select" id="don_id" name="don_id" required onchange="updateCalcul()">
                            <option value="">-- Sélectionner un don --</option>
                            <?php foreach ($donsArgent as $don): ?>
                            <option value="<?= $don['id'] ?>" data-disponible="<?= $don['quantite_disponible'] ?>">
                                <?= htmlspecialchars($don['donateur'] ?? 'Anonyme') ?> - 
                                <?= number_format($don['quantite_disponible'], 0, ',', ' ') ?> Ar disponible
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantite" class="form-label">Quantité à acheter <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="quantite" name="quantite" 
                                   min="0.01" step="0.01" required placeholder="Entrez la quantité"
                                   oninput="updateCalcul()">
                            <span class="input-group-text"><?= $besoin['unite'] ?></span>
                        </div>
                        <div id="quantiteHelp" class="form-text">
                            Sélectionnez d'abord un don pour voir le montant maximum.
                        </div>
                    </div>
                    
                    <!-- Calcul du montant -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block">Prix unitaire</small>
                                    <strong><?= number_format($prix_unitaire, 0, ',', ' ') ?> Ar</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Quantité</small>
                                    <strong id="quantite_display">-</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Montant Total</small>
                                    <strong id="montant_display" class="text-success fs-5">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_achat" class="form-label">Date d'achat</label>
                        <input type="date" class="form-control" id="date_achat" name="date_achat" 
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" 
                                  placeholder="Notes supplémentaires (optionnel)"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-cart-check me-2"></i>Effectuer l'Achat
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Liste des dons disponibles -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-list-ul me-2"></i>Détail des Dons en Argent
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Donateur</th>
                            <th>Date Réception</th>
                            <th>Disponible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donsArgent as $don): ?>
                        <tr>
                            <td><?= htmlspecialchars($don['donateur'] ?? 'Anonyme') ?></td>
                            <td><?= date('d/m/Y', strtotime($don['date_reception'])) ?></td>
                            <td>
                                <span class="badge bg-success">
                                    <?= number_format($don['quantite_disponible'], 0, ',', ' ') ?> Ar
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

<script>
const prixUnitaire = <?= $prix_unitaire ?>;
const resteARecevoir = <?= $besoin['reste'] ?>;

function hideTopAlert() {
    const el = document.getElementById('topAlertError');
    if (el) {
        el.classList.add('d-none');
        el.classList.remove('d-block');
    }
}

function showTopAlert(message) {
    const el = document.getElementById('topAlertError');
    const msgEl = document.getElementById('topAlertErrorMessage');
    if (el && msgEl) {
        msgEl.textContent = message;
        el.classList.remove('d-none');
        el.classList.add('d-block');
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function updateCalcul() {
    const donSelect = document.getElementById('don_id');
    const selectedOption = donSelect.options[donSelect.selectedIndex];
    const quantiteInput = document.getElementById('quantite');
    const helpText = document.getElementById('quantiteHelp');
    const quantiteDisplay = document.getElementById('quantite_display');
    const montantDisplay = document.getElementById('montant_display');
    
    hideTopAlert();
    
    if (selectedOption.value) {
        const disponible = parseFloat(selectedOption.dataset.disponible);
        const maxQuantite = Math.min(resteARecevoir, Math.floor(disponible / prixUnitaire * 100) / 100);
        
        helpText.innerHTML = `Disponible: <strong>${new Intl.NumberFormat('fr-FR').format(disponible)} Ar</strong> | 
                              Max achetable: <strong>${maxQuantite.toFixed(2)} <?= $besoin['unite'] ?></strong>`;
        
        quantiteInput.placeholder = `Suggestion: ${maxQuantite.toFixed(2)}`;
        
        const quantite = parseFloat(quantiteInput.value) || 0;
        
        if (quantite > 0) {
            quantiteDisplay.textContent = quantite.toFixed(2) + ' <?= $besoin['unite'] ?>';
            const montant = quantite * prixUnitaire;
            montantDisplay.textContent = new Intl.NumberFormat('fr-FR').format(montant) + ' Ar';
            
            if (montant > disponible) {
                showTopAlert(`Le montant (${new Intl.NumberFormat('fr-FR').format(montant)} Ar) dépasse l'argent disponible (${new Intl.NumberFormat('fr-FR').format(disponible)} Ar).`);
                montantDisplay.classList.remove('text-success');
                montantDisplay.classList.add('text-danger');
            } else {
                montantDisplay.classList.remove('text-danger');
                montantDisplay.classList.add('text-success');
            }
        } else {
            quantiteDisplay.textContent = '-';
            montantDisplay.textContent = '-';
        }
    } else {
        helpText.innerHTML = 'Sélectionnez d\'abord un don pour voir le montant maximum.';
        quantiteInput.placeholder = 'Entrez la quantité';
        quantiteDisplay.textContent = '-';
        montantDisplay.textContent = '-';
    }
}

document.getElementById('achatForm').addEventListener('submit', function(e) {
    const donSelect = document.getElementById('don_id');
    const selectedOption = donSelect.options[donSelect.selectedIndex];
    const quantite = parseFloat(document.getElementById('quantite').value);
    
    if (selectedOption.value) {
        const disponible = parseFloat(selectedOption.dataset.disponible);
        const montant = quantite * prixUnitaire;
        
        if (montant > disponible) {
            e.preventDefault();
            showTopAlert(`Le montant (${new Intl.NumberFormat('fr-FR').format(montant)} Ar) dépasse l'argent disponible (${new Intl.NumberFormat('fr-FR').format(disponible)} Ar). Opération refusée.`);
            return false;
        }
    }
});
</script>

        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
