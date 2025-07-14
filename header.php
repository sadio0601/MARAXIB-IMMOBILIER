
 <?php
 ob_start();
// Fonction helper pour déterminer l'URL du dashboard
function get_dashboard_url() {
    if (!est_connecte()) return 'connexion.php';
    
    switch($_SESSION['user_role']) {
        case 'admin': return 'admin_dashboard.php';
        case 'agent': return 'agent_dashboard.php';
        case 'user': return 'user_dashboard.php';
        default: return 'index.php';
    }
}

// Liste des pages accessibles sans connexion
$pages_publiques = [
    'index.php',
    'biens.php',
    'agents.php',
    'contact.php',
    'connexion.php',
    'inscription.php'
];

// Vérifier si la page actuelle nécessite une connexion
$page_actuelle = basename($_SERVER['PHP_SELF']);
$connexion_requise = !in_array($page_actuelle, $pages_publiques);

// Rediriger vers la connexion si nécessaire
if ($connexion_requise && !est_connecte()) {
    header('Location: connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
?>
<style>
    .collapse ul li a {
        font-size: 20px;
        font-weight: bold;
    }
</style>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Plateforme Immobilière'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="img/logo4.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>


<div class="py-2 border-bottom" style="background-color: #bfc9ca;">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="mb-2 mb-md-0 text-white">
            <i class="fas fa-map-marker-alt me-2"></i>Dakar - Sénégal 
            <span class="mx-3">|</span>
            <i class="fas fa-phone me-2"></i>
            <a href="tel: +221 77 123 45 67" class="text-decoration-none text-white">+221 77 123 45 67</a>
            <span class="mx-3">|</span>
            <i class="fas fa-envelope me-2"></i>
            <a href="mailto:agenceimmobiliere@gmail.com" class="text-decoration-none text-white">agenceimmobiliere@gmail.com</a>
        </div>
        <div>
            <a href="https://facebook.com" class="text-white me-3" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://twitter.com" class="text-white me-3" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://instagram.com" class="text-white me-3" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://linkedin.com" class="text-white" target="_blank"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </div>
</div>

<nav class="navbar navbar-expand-lg" style="background-color: #1f618d;">
    <div class="container">
        <a class="navbar-brand text-white" href="index.php">
             <img src="img/logo.png" alt="" width="200" height="50">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="biens.php">Biens immobiliers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="agents.php">Agents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="contact.php">Contact</a>
                </li>
                
                
                
            </ul>
            
            <ul class="navbar-nav">
                <?php if(est_connecte()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['user_nom']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo get_dashboard_url(); ?>">Tableau de bord</a></li>
                            <li><a class="dropdown-item" href="profil.php">Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                             
                    <?php if($_SESSION['user_role'] === 'admin'): ?>
                        <li><a class="dropdown-item" href="affiche_bien.php">Gestion des biens</a></li>
                        <li><a class="dropdown-item" href="utilisateurs.php">Gestion des utilisateurs</a></li>

                    
                    <?php endif; ?>
                            <li><a class="dropdown-item" href="deconnexion.php">Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="connexion.php">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light ms-2" href="inscription.php">Inscription</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

  