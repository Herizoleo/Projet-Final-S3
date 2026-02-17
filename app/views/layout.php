<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'BNGRC' ?> - Gestion des Dons</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="bi bi-heart-pulse-fill me-2"></i>BNGRC</h4>
            <small>Gestion des Dons pour les Sinistrés</small>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-section">Principal</div>
            <a href="/" class="nav-link <?= ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                Tableau de Bord
            </a>
            
            <div class="nav-section mt-3">Gestion</div>
            <a href="/villes" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/villes') === 0 ? 'active' : '' ?>">
                <i class="bi bi-geo-alt"></i>
                Villes
            </a>
            <a href="/besoins" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/besoins') === 0 ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check"></i>
                Besoins
            </a>
            <a href="/dons" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dons') === 0 ? 'active' : '' ?>">
                <i class="bi bi-gift"></i>
                Dons
            </a>
            <a href="/distributions" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/distributions') === 0 ? 'active' : '' ?>">
                <i class="bi bi-truck"></i>
                Distributions
            </a>
            <a href="/achats" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/achats') === 0 ? 'active' : '' ?>">
                <i class="bi bi-cart-check"></i>
                Achats
            </a>
            <a href="/ventes" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/ventes') === 0 ? 'active' : '' ?>">
                <i class="bi bi-cash-coin"></i>
                Ventes
            </a>
            
            <div class="nav-section mt-3">Rapports</div>
            <a href="/recap" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/recap') === 0 ? 'active' : '' ?>">
                <i class="bi bi-bar-chart"></i>
                Récapitulation
            </a>
            
            <div class="nav-section mt-3">Configuration</div>
            <a href="/categories" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/categories') === 0 ? 'active' : '' ?>">
                <i class="bi bi-tags"></i>
                Catégories
            </a>
            <a href="/reset" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/reset') === 0 ? 'active' : '' ?>">
                <i class="bi bi-arrow-counterclockwise"></i>
                Réinitialiser
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="main-header d-flex justify-content-between align-items-center">
            <div>
                <h1><?= $pageTitle ?? 'BNGRC' ?></h1>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">
                    <i class="bi bi-calendar me-1"></i>
                    <?= date('d/m/Y') ?>
                </span>
            </div>
        </header>
        
        <!-- Page Content -->
        <div class="page-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= $_SESSION['warning'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['warning']); ?>
            <?php endif; ?>
            
            <?= $content ?>
        </div>
        
        <!-- Footer -->
        <footer class="main-footer">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="bi bi-c-circle me-1"></i> BNGRC <?= date('Y') ?> - Gestion des Dons pour les Sinistrés
                </span>
                <span class="text-muted small">
                    <i class="bi bi-people-fill me-1"></i>
                    <strong>ETU4341</strong> | <strong>ETU4267</strong> | <strong>ETU4165</strong>
                </span>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto-dismiss alerts after 5 seconds (sauf les alertes de validation)
        document.querySelectorAll('.alert:not(#topAlertError):not(#topAlertWarning)').forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>
</body>
</html>
