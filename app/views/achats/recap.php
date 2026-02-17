<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Vue d'ensemble des montants</h5>
    <button class="btn btn-primary" onclick="actualiserStats()" id="refreshBtn">
        <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
    </button>
</div>

<div class="row g-4">
    <!-- Besoins -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-clipboard-check me-2"></i>Besoins (en montant)
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">Besoins Totaux</small>
                            <h3 class="mb-0 text-primary" id="besoins_total">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h3>
                            <small class="text-muted">Ariary</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                            <small class="text-muted d-block mb-1">Besoins Satisfaits</small>
                            <h3 class="mb-0 text-success" id="besoins_satisfaits">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h3>
                            <small class="text-muted">Ariary</small>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Progression</span>
                        <span class="fw-bold" id="besoins_pourcentage">-</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" id="besoins_progress" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Reste -->
                <div class="mt-3 text-center">
                    <span class="text-muted">Reste à satisfaire: </span>
                    <strong class="text-danger" id="besoins_reste">-</strong>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dons -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <i class="bi bi-gift me-2"></i>Dons (en montant)
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">Dons Reçus</small>
                            <h3 class="mb-0 text-success" id="dons_recus">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h3>
                            <small class="text-muted">Ariary</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                            <small class="text-muted d-block mb-1">Dons Dispatchés</small>
                            <h3 class="mb-0 text-info" id="dons_dispatches">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </h3>
                            <small class="text-muted">Ariary</small>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Utilisation des dons</span>
                        <span class="fw-bold" id="dons_pourcentage">-</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-info" id="dons_progress" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Reste -->
                <div class="mt-3 text-center">
                    <span class="text-muted">Dons disponibles: </span>
                    <strong class="text-primary" id="dons_disponibles">-</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Résumé global -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-bar-chart me-2"></i>Résumé Global
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <h3 id="stat_besoins_total">-</h3>
                    <p>Besoins totaux</p>
                    <i class="bi bi-clipboard-check"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <h3 id="stat_besoins_satisfaits">-</h3>
                    <p>Besoins satisfaits</p>
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <h3 id="stat_dons_recus">-</h3>
                    <p>Dons reçus</p>
                    <i class="bi bi-gift"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <h3 id="stat_dons_dispatches">-</h3>
                    <p>Dons dispatchés</p>
                    <i class="bi bi-truck"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timestamp de mise à jour -->
<div class="text-center mt-3">
    <small class="text-muted">
        Dernière actualisation: <span id="lastUpdate">-</span>
    </small>
</div>

<script>
function formatMontant(montant) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(montant));
}

function formatMontantCompact(montant) {
    if (montant >= 1000000000) {
        return (montant / 1000000000).toFixed(1) + ' Md';
    } else if (montant >= 1000000) {
        return (montant / 1000000).toFixed(1) + ' M';
    } else if (montant >= 1000) {
        return (montant / 1000).toFixed(0) + ' K';
    }
    return formatMontant(montant);
}

function actualiserStats() {
    const btn = document.getElementById('refreshBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Chargement...';
    
    fetch('/api/recap-stats')
        .then(response => response.json())
        .then(data => {
            // Besoins
            const besoinsTotal = parseFloat(data.besoins_total) || 0;
            const besoinsSatisfaits = parseFloat(data.besoins_satisfaits) || 0;
            const besoinsReste = besoinsTotal - besoinsSatisfaits;
            const besoinsPourcentage = besoinsTotal > 0 ? (besoinsSatisfaits / besoinsTotal * 100) : 0;
            
            document.getElementById('besoins_total').textContent = formatMontant(besoinsTotal);
            document.getElementById('besoins_satisfaits').textContent = formatMontant(besoinsSatisfaits);
            document.getElementById('besoins_reste').textContent = formatMontant(besoinsReste) + ' Ar';
            document.getElementById('besoins_pourcentage').textContent = besoinsPourcentage.toFixed(1) + '%';
            document.getElementById('besoins_progress').style.width = besoinsPourcentage + '%';
            
            // Dons
            const donsRecus = parseFloat(data.dons_recus) || 0;
            const donsDispatches = parseFloat(data.dons_dispatches) || 0;
            const donsDisponibles = donsRecus - donsDispatches;
            const donsPourcentage = donsRecus > 0 ? (donsDispatches / donsRecus * 100) : 0;
            
            document.getElementById('dons_recus').textContent = formatMontant(donsRecus);
            document.getElementById('dons_dispatches').textContent = formatMontant(donsDispatches);
            document.getElementById('dons_disponibles').textContent = formatMontant(donsDisponibles) + ' Ar';
            document.getElementById('dons_pourcentage').textContent = donsPourcentage.toFixed(1) + '%';
            document.getElementById('dons_progress').style.width = donsPourcentage + '%';
            
            // Stats cards
            document.getElementById('stat_besoins_total').textContent = formatMontantCompact(besoinsTotal);
            document.getElementById('stat_besoins_satisfaits').textContent = formatMontantCompact(besoinsSatisfaits);
            document.getElementById('stat_dons_recus').textContent = formatMontantCompact(donsRecus);
            document.getElementById('stat_dons_dispatches').textContent = formatMontantCompact(donsDispatches);
            
            // Timestamp
            const now = new Date();
            document.getElementById('lastUpdate').textContent = now.toLocaleString('fr-FR');
            
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Actualiser';
        })
        .catch(error => {
            console.error('Erreur:', error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Actualiser';
            alert('Erreur lors du chargement des données.');
        });
}

// Charger les stats au chargement de la page
document.addEventListener('DOMContentLoaded', actualiserStats);
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
