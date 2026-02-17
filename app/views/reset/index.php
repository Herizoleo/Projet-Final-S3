<?php ob_start(); ?>

<div class="mb-4">
    <a href="/" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour au tableau de bord
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- État actuel -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-database me-2"></i>État actuel des données
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2 col-4 mb-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0"><?= $stats['villes'] ?></h4>
                            <small class="text-muted">Villes</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0"><?= $stats['besoins'] ?></h4>
                            <small class="text-muted">Besoins</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0"><?= $stats['dons'] ?></h4>
                            <small class="text-muted">Dons</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0"><?= $stats['distributions'] ?></h4>
                            <small class="text-muted">Distributions</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0"><?= $stats['achats'] ?></h4>
                            <small class="text-muted">Achats</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="border rounded p-2">
                            <h4 class="mb-0"><?= $stats['ventes'] ?></h4>
                            <small class="text-muted">Ventes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Réinitialisation partielle -->
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Réinitialisation aux Données Initiales
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Cette action va restaurer les données initiales:</strong>
                    <ul class="mb-0 mt-2">
                        <li>5 villes (Toamasina, Mananjary, Farafangana, Nosy Be, Morondava)</li>
                        <li>26 besoins</li>
                        <li>16 dons</li>
                        <li>Distributions, achats et ventes remis à zéro</li>
                    </ul>
                </div>
                
                <form action="/reset" method="POST" onsubmit="return confirmReset(this)">
                    <div class="mb-3">
                        <label for="confirmation" class="form-label">
                            Tapez <strong>REINITIALISER</strong> pour confirmer:
                        </label>
                        <input type="text" class="form-control" id="confirmation" name="confirmation" 
                               autocomplete="off" placeholder="REINITIALISER">
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser aux données initiales
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Réinitialisation complète -->
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-trash me-2"></i>Réinitialisation Complète
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-octagon me-2"></i>
                    <strong>ATTENTION:</strong> Cette action va <strong>TOUT supprimer</strong> et restaurer les données initiales par défaut (catégories, types d'articles, régions).
                </div>
                
                <form action="/reset/complete" method="POST" onsubmit="return confirmResetComplete(this)">
                    <div class="mb-3">
                        <label for="confirmation_complete" class="form-label">
                            Tapez <strong>TOUT SUPPRIMER</strong> pour confirmer:
                        </label>
                        <input type="text" class="form-control" id="confirmation_complete" name="confirmation" 
                               autocomplete="off" placeholder="TOUT SUPPRIMER">
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Réinitialisation Complète
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmReset(form) {
    if (form.confirmation.value !== 'REINITIALISER') {
        alert('Veuillez taper exactement "REINITIALISER" pour confirmer.');
        return false;
    }
    return confirm('Êtes-vous sûr de vouloir supprimer toutes les données transactionnelles ?');
}

function confirmResetComplete(form) {
    if (form.confirmation.value !== 'TOUT SUPPRIMER') {
        alert('Veuillez taper exactement "TOUT SUPPRIMER" pour confirmer.');
        return false;
    }
    return confirm('ATTENTION: Toutes les données seront supprimées et remplacées par les valeurs par défaut. Continuer ?');
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>
