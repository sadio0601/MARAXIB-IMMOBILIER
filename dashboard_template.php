<?php
require_once 'config.php';

// Vérification de la connexion et du rôle
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

// Récupérer les données de l'utilisateur
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Titre de la page selon le rôle
$page_title = ucfirst($user_role) . " Dashboard";
// Récupérer les statistiques
try {
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM biens) as total_biens,
            (SELECT COUNT(*) FROM agents) as total_agents,
            (SELECT COUNT(*) FROM demandes) as total_demandes
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des statistiques";
}

// Si la variable $content n'est pas définie, on la crée vide
$content = $content ?? '';
// ob_start(); // Capturer le contenu spécifique

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="assets/css/dashboard.css">
   
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar bg-dark text-white">
            <div class="sidebar-header p-3 text-center">
                <img src="img/logo2.png" alt="" class="img-fluid" style="max-width: 150px;">
                <div class="user-info mt-3">
                    <div class="user-avatar">
                        <i class="bi bi-person-circle fs-1"></i>
                    </div>
                    <h5 class="mt-2"><?php echo htmlspecialchars($_SESSION['user_nom']); ?></h5>
                    <small class="text-muted"><?php echo ucfirst($user_role); ?></small>
                </div>
            </div>
            
            <ul class="sidebar-menu nav flex-column">
                <li class="nav-item">
                    <a href="<?php echo get_dashboard_url(); ?>" class="nav-link active">
                        <i class="bi bi-speedometer2 me-2"></i> Tableau de bord
                    </a>
                </li>
                
                <?php if($user_role === 'admin' || $user_role === 'agent'): ?>
                <li class="nav-item">
                    <a href="affiche_bien.php" class="nav-link">
                        <i class="bi bi-house me-2"></i> Gestion des biens
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if($user_role === 'admin'): ?>
                <li class="nav-item">
                    <a href="utilisateurs.php" class="nav-link">
                        <i class="bi bi-people me-2"></i> Gestion des utilisateurs
                    </a>
                </li>
                <li>
                    <a href="liste_agents.php" class="nav-link">
                        <i class="bi bi-person-workspace me-2"></i> Gestion des agents
                    </a>
                </li>
                <li class="nav-item">
                    <a href="demandes.php" class="nav-link">
                        <i class="bi bi-envelope me-2"></i> Gestion des demandes
                    </a>    
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a href="profil.php" class="nav-link">
                        <i class="bi bi-person me-2"></i> Mon profil
                    </a>
                </li>
                
                <?php if($user_role === 'user'): ?>
                <li class="nav-item">
                    <a href="demandes.php" class="nav-link">
                        <i class="bi bi-envelope me-2"></i> Mes demandes
                    </a>
                </li>
                <?php endif; ?>
                
                  <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="bi bi-house-door me-2"></i> Accueil
                    </a>
                </li>
                <li class="nav-item mt-auto">
                    <a href="deconnexion.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="dashboard-main">
            <header class="dashboard-header bg-light p-3 d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0"><?php echo $page_title; ?></h2>
                <div class="dashboard-alert">
                    <?php if(isset($success_message)): ?>
                        <div class="alert alert-success mb-0 py-2"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger mb-0 py-2"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                </div>
            </header>
            
            <main class="dashboard-content p-4">
                <!-- Le contenu spécifique à chaque dashboard sera inséré ici -->
                <?php echo $content; ?>
            </main>
            
            <footer class="dashboard-footer bg-light p-3 text-center">
                <img src="img/logo4.png" alt="Logo" class="img-fluid mt-5" style="max-width: 150px;"> <br><br>
                <small class="text-muted">© <?php echo date('Y'); ?> MARAXIB-IMMOBILIER - Tous droits réservés</small>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html>