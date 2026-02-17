<?php ob_start(); ?>

<div class="mb-4">
    <a href="/achats" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row g-4">
    <!-- Argent disponible -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash-coin me-2"></i>Argent Disponible
            </div>
            <div class="card-body text-center">
                <h2 class="text-success mb-0"><?= number_format($totalArgent, 0, ',', ' ') ?></h2>
                <p class="text-muted mb-0">Ariary</p>
            </div>
        </div>
        
        <?php if (!empty($donsArgent)): ?>
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-list-ul me-2"></i>Détail des Dons en Argent
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Donateur</th>
                            <th>Disponible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donsArgent as $don): ?>
                        <tr>
                            <td><?= htmlspecialchars($don['donateur'] ?? 'Anonyme') ?></td>
                            <td><strong><?= number_format($don['quantite_disponible'], 0, ',', ' ') ?></strong> Ar</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Formulaire -->
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
        <?php elseif (empty($besoins)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle fs-1 d-block mb-3 text-success"></i>
                <h5>Tous les besoins sont satisfaits</h5>
                <p class="text-muted">Il n'y a aucun besoin en attente avec un prix unitaire défini.</p>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Notifications -->
        <div class="alert alert-danger alert-dismissible d-none" id="topAlertError" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Erreur:</strong> <span id="topAlertErrorMessage"></span>
            <button type="button" class="btn-close" onclick="hideTopAlert('error')"></button>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cart-plus me-2"></i>Effectuer un Achat
            </div>
            <div class="card-body">
                <form action="/achats/store" method="POST" id="achatForm">
                    
                    <!-- Filtre par ville -->
                    <div class="mb-3">
                        <label for="ville_filtre" class="form-label">Filtrer par ville</label>
                        <select class="form-select" id="ville_filtre" onchange="filterBesoins()">
                            <option value="">-- Toutes les villes --</option>
                            <?php foreach ($villes as $ville): ?>
                            <option value="<?= $ville['id'] ?>"><?= htmlspecialchars($ville['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="besoin_id" class="form-label">Besoin à satisfaire <span class="text-danger">*</span></label>
                        <select class="form-select" id="besoin_id" name="besoin_id" required onchange="updateCalcul()">
                            <option value="">-- Sélectionner un besoin --</option>
                            <?php foreach ($besoins as $besoin): ?>
                            <option value="<?= $besoin['id'] ?>" 
                                    data-prix="<?= $besoin['prix_unitaire'] ?>"
                                    data-unite="<?= $besoin['unite'] ?>"
                                    data-reste="<?= $besoin['reste'] ?>"
                                    data-ville="<?= $besoin['ville_id'] ?>">
                                <?= htmlspecialchars($besoin['ville_nom']) ?> - <?= htmlspecialchars($besoin['article_nom']) ?>
                                (Reste: <?= number_format($besoin['reste'], 2) ?> <?= $besoin['unite'] ?> - 
                                Prix: <?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar/<?= $besoin['unite'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
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
                            <span class="input-group-text" id="unite_label">unité</span>
                        </div>
                        <div id="quantiteHelp" class="form-text">
                            Sélectionnez d'abord un besoin et un don.
                        </div>
                    </div>
                    
                    <!-- Calcul du montant -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block">Prix unitaire</small>
                                    <strong id="prix_display">-</strong>
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
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                            <i class="bi bi-cart-check me-2"></i>Effectuer l'Achat
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($donsArgent) && !empty($besoins)): ?>
<script>
const totalArgent = <?= $totalArgent ?>;

function hideTopAlert(type) {
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

function filterBesoins() {
    const villeFiltre = document.getElementById('ville_filtre').value;
    const besoinSelect = document.getElementById('besoin_id');
    
    for (let option of besoinSelect.options) {
        if (option.value === '') continue;
        const villeId = option.dataset.ville;
        option.style.display = (!villeFiltre || villeId === villeFiltre) ? '' : 'none';
    }
    
    besoinSelect.value = '';
    updateCalcul();
}

function updateCalcul() {
    const besoinSelect = document.getElementById('besoin_id');
    const donSelect = document.getElementById('don_id');
    const quantiteInput = document.getElementById('quantite');
    const uniteLabel = document.getElementById('unite_label');
    const helpText = document.getElementById('quantiteHelp');
    
    const prixDisplay = document.getElementById('prix_display');
    const quantiteDisplay = document.getElementById('quantite_display');
    const montantDisplay = document.getElementById('montant_display');
    
    hideTopAlert();
    
    const besoinOption = besoinSelect.options[besoinSelect.selectedIndex];
    const donOption = donSelect.options[donSelect.selectedIndex];
    
    if (besoinOption.value && donOption.value) {
        const prix = parseFloat(besoinOption.dataset.prix);
        const unite = besoinOption.dataset.unite;
        const reste = parseFloat(besoinOption.dataset.reste);
        const disponible = parseFloat(donOption.dataset.disponible);
        const quantite = parseFloat(quantiteInput.value) || 0;
        
        uniteLabel.textContent = unite;
        prixDisplay.textContent = new Intl.NumberFormat('fr-FR').format(prix) + ' Ar/' + unite;
        
        const maxQuantite = Math.min(reste, Math.floor(disponible / prix * 100) / 100);
        helpText.innerHTML = `Reste à satisfaire: <strong>${reste.toFixed(2)} ${unite}</strong> | 
                              Max achetable: <strong>${maxQuantite.toFixed(2)} ${unite}</strong> 
                              (${new Intl.NumberFormat('fr-FR').format(disponible)} Ar disponible)`;
        
        if (quantite > 0) {
            quantiteDisplay.textContent = quantite.toFixed(2) + ' ' + unite;
            const montant = quantite * prix;
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
        uniteLabel.textContent = 'unité';
        prixDisplay.textContent = '-';
        quantiteDisplay.textContent = '-';
        montantDisplay.textContent = '-';
        helpText.innerHTML = 'Sélectionnez d\'abord un besoin et un don.';
    }
}

document.getElementById('achatForm').addEventListener('submit', function(e) {
    const besoinSelect = document.getElementById('besoin_id');
    const donSelect = document.getElementById('don_id');
    const quantite = parseFloat(document.getElementById('quantite').value);
    
    const besoinOption = besoinSelect.options[besoinSelect.selectedIndex];
    const donOption = donSelect.options[donSelect.selectedIndex];
    
    if (besoinOption.value && donOption.value) {
        const prix = parseFloat(besoinOption.dataset.prix);
        const disponible = parseFloat(donOption.dataset.disponible);
        const montant = quantite * prix;
        
        if (montant > disponible) {
            e.preventDefault();
            showTopAlert(`Le montant (${new Intl.NumberFormat('fr-FR').format(montant)} Ar) dépasse l'argent disponible (${new Intl.NumberFormat('fr-FR').format(disponible)} Ar). Opération refusée.`);
            return false;
        }
    }
});
</script>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
