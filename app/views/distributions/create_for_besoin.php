<?php ob_start(); ?>

<div class="mb-4">
    <a href="/distributions" class="btn btn-outline-secondary btn-sm">
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
                    <span class="text-muted">Région</span>
                    <span><?= htmlspecialchars($besoin['region_nom']) ?></span>
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
                
                <!-- Barre de progression -->
                <div class="mt-3">
                    <?php 
                    $pourcentage = $besoin['quantite_necessaire'] > 0 
                        ? min(100, ($besoin['quantite_recue'] / $besoin['quantite_necessaire']) * 100) 
                        : 0;
                    ?>
                    <small class="text-muted">Progression: <?= number_format($pourcentage, 1) ?>%</small>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-success" style="width: <?= $pourcentage ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stock disponible -->
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-box-seam me-2"></i>Stock Disponible
            </div>
            <div class="card-body text-center">
                <h2 class="text-success mb-0"><?= number_format($totalDisponible, 2) ?></h2>
                <p class="text-muted mb-0"><?= $besoin['unite'] ?> de <?= htmlspecialchars($besoin['article_nom']) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Formulaire de distribution -->
    <div class="col-lg-8">
        <?php if (empty($donsDisponibles)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-exclamation-triangle fs-1 d-block mb-3 text-warning"></i>
                <h5>Aucun don disponible</h5>
                <p class="text-muted">Il n'y a pas de stock de <strong><?= htmlspecialchars($besoin['article_nom']) ?></strong> disponible pour cette distribution.</p>
                <a href="/dons/create" class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i>Enregistrer un don
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Zone de notification en haut -->
        <div class="alert alert-danger alert-dismissible d-none" id="topAlertError" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Erreur:</strong> <span id="topAlertErrorMessage"></span>
            <button type="button" class="btn-close" onclick="hideTopAlert('error')"></button>
        </div>
        <div class="alert alert-warning alert-dismissible d-none" id="topAlertWarning" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Attention:</strong> <span id="topAlertWarningMessage"></span>
            <button type="button" class="btn-close" onclick="hideTopAlert('warning')"></button>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="bi bi-truck me-2"></i>Effectuer la Distribution
            </div>
            <div class="card-body">
                <form action="/distributions/store" method="POST" id="distributionForm">
                    <input type="hidden" name="besoin_id" value="<?= $besoin['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="don_id" class="form-label">Sélectionner un don <span class="text-danger">*</span></label>
                        <select class="form-select" id="don_id" name="don_id" required onchange="updateMaxQuantite()">
                            <option value="">-- Choisir un don --</option>
                            <?php foreach ($donsDisponibles as $don): ?>
                            <option value="<?= $don['id'] ?>" data-disponible="<?= $don['quantite_disponible'] ?>">
                                <?php if ($don['donateur']): ?>
                                    Don de <?= htmlspecialchars($don['donateur']) ?> - 
                                <?php else: ?>
                                    Don anonyme - 
                                <?php endif; ?>
                                <?= number_format($don['quantite_disponible'], 2) ?> <?= $don['unite'] ?> disponible
                                (reçu le <?= date('d/m/Y', strtotime($don['date_reception'])) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantite" class="form-label">Quantité à distribuer <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="quantite" name="quantite" 
                                   min="0.01" step="0.01" required placeholder="Entrez la quantité">
                            <span class="input-group-text"><?= $besoin['unite'] ?></span>
                        </div>
                        <div id="quantiteHelp" class="form-text">
                            Sélectionnez d'abord un don pour voir la quantité disponible.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_distribution" class="form-label">Date de distribution</label>
                        <input type="date" class="form-control" id="date_distribution" name="date_distribution" 
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" 
                                  placeholder="Notes supplémentaires (optionnel)"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-truck me-2"></i>Effectuer la Distribution
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Liste des dons disponibles -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-list-ul me-2"></i>Détail des Dons Disponibles
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Donateur</th>
                            <th>Date Réception</th>
                            <th>Quantité Disponible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donsDisponibles as $don): ?>
                        <tr>
                            <td><?= htmlspecialchars($don['donateur'] ?? 'Anonyme') ?></td>
                            <td><?= date('d/m/Y', strtotime($don['date_reception'])) ?></td>
                            <td>
                                <span class="badge bg-success">
                                    <?= number_format($don['quantite_disponible'], 2) ?> <?= $don['unite'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
<script>
const resteARecevoir = <?= $besoin['reste'] ?>;

function hideTopAlert(type) {
    const el = document.getElementById(type === 'error' ? 'topAlertError' : 'topAlertWarning');
    if (el) {
        el.classList.add('d-none');
        el.classList.remove('d-block');
    }
}

function showTopAlert(type, message) {
    hideTopAlert('error');
    hideTopAlert('warning');
    
    if (type === 'error') {
        const el = document.getElementById('topAlertError');
        const msgEl = document.getElementById('topAlertErrorMessage');
        if (el && msgEl) {
            msgEl.textContent = message;
            el.classList.remove('d-none');
            el.classList.add('d-block');
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    } else {
        const el = document.getElementById('topAlertWarning');
        const msgEl = document.getElementById('topAlertWarningMessage');
        if (el && msgEl) {
            msgEl.textContent = message;
            el.classList.remove('d-none');
            el.classList.add('d-block');
        }
    }
}

function updateMaxQuantite() {
    const select = document.getElementById('don_id');
    const selectedOption = select.options[select.selectedIndex];
    const quantiteInput = document.getElementById('quantite');
    const helpText = document.getElementById('quantiteHelp');
    
    hideTopAlert('error');
    hideTopAlert('warning');
    
    if (selectedOption.value) {
        const disponible = parseFloat(selectedOption.dataset.disponible);
        helpText.innerHTML = `Disponible: <strong>${disponible.toFixed(2)} <?= $besoin['unite'] ?></strong> | 
                              Reste à recevoir: <strong>${resteARecevoir.toFixed(2)} <?= $besoin['unite'] ?></strong>`;
        
        // Suggestion de quantité
        const suggestion = Math.min(disponible, resteARecevoir);
        quantiteInput.placeholder = `Suggestion: ${suggestion.toFixed(2)}`;
    } else {
        helpText.innerHTML = 'Sélectionnez d\'abord un don pour voir la quantité disponible.';
        quantiteInput.placeholder = 'Entrez la quantité';
    }
}

document.getElementById('quantite').addEventListener('input', function() {
    const select = document.getElementById('don_id');
    const selectedOption = select.options[select.selectedIndex];
    
    hideTopAlert('error');
    hideTopAlert('warning');
    
    if (selectedOption.value) {
        const disponible = parseFloat(selectedOption.dataset.disponible);
        const quantite = parseFloat(this.value);
        
        if (quantite > disponible) {
            showTopAlert('error', `La quantité demandée (${quantite.toFixed(2)}) dépasse le stock disponible (${disponible.toFixed(2)}). Cette opération sera refusée.`);
        } else if (quantite > resteARecevoir) {
            showTopAlert('warning', `Vous donnez plus que le besoin restant. L'excédent ne sera pas enregistré.`);
        }
    }
});

// Validation avant soumission
document.getElementById('distributionForm').addEventListener('submit', function(e) {
    const select = document.getElementById('don_id');
    const selectedOption = select.options[select.selectedIndex];
    const quantite = parseFloat(document.getElementById('quantite').value);
    
    if (selectedOption.value) {
        const disponible = parseFloat(selectedOption.dataset.disponible);
        
        if (quantite > disponible) {
            e.preventDefault();
            showTopAlert('error', `La quantité demandée (${quantite.toFixed(2)}) dépasse le stock disponible (${disponible.toFixed(2)}). Cette opération est refusée.`);
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
