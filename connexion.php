<?php
require_once 'config.php';

// Si l'utilisateur est déjà connecté, on le redirige selon son rôle
if(est_connecte()) {
    rediriger_par_role();
    exit;
}

ob_start(); 


$erreur = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = securiser($_POST['email']);
    $password = $_POST['password']; // On ne sécurise pas le mot de passe (pour le hash)
    
    try {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            // Création de la session utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirection selon le rôle
            rediriger_par_role($user['role']);
            exit;
        } else {
            $erreur = 'Email ou mot de passe incorrect';
        }
    } catch(PDOException $e) {
        $erreur = 'Une erreur est survenue. Veuillez réessayer plus tard.';
        // Pour le débogage en développement :
        // $erreur .= ' Erreur: ' . $e->getMessage();
    }
}

/**
 * Redirige l'utilisateur selon son rôle
 */
function rediriger_par_role($role = null) {
    if ($role === null && isset($_SESSION['user_role'])) {
        $role = $_SESSION['user_role'];
    }
    
    switch($role) {
        case 'admin':
            header('Location: admin_dashboard.php');
            break;
        case 'agent':
            header('Location: agent_dashboard.php');
            break;
        case 'user':
            header('Location: user_dashboard.php');
            break;
        default:
            header('Location: index.php');
    }
    exit;
}
?>


<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 animate__animated animate__fadeIn">
            <div class="card shadow-lg">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Connexion Admin</h2>
                        <p class="text-muted">Accédez à votre tableau de bord</p>
                    </div>

                    <?php if($erreur): ?>
                    <div class="alert alert-danger"><?php echo $erreur; ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group py-2">
                            <label for="password">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Se connecter</button>

                    </form>


                    <div class="text-center mt-3">Pas encore de compte ? <br>
                        <a href="inscription.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>S'inscrire
                        </a>
                    </div>
                    

                    <div class="text-center mt-3">
                        <a href="index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

