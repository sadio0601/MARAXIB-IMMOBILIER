<?php
require_once 'config.php';

if (!function_exists('est_connecte')) {
    die("Erreur: Le fichier config.php n'a pas été inclus correctement");
}

if (est_connecte()) {
    header('Location: index.php');
    exit;
}

 

// Initialisation des variables
$erreurs = [];
$succes = '';
$roles_disponibles = ['admin', 'agent', 'user']; // Rôles possibles

// Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération et sécurisation des données
    $nom = securiser($_POST['nom'] ?? '');
    $email = securiser($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // On ne sécurise pas le mot de passe
    $confirmation = $_POST['confirmation'] ?? '';
    $role = securiser($_POST['role'] ?? 'user');

    // Validation des champs
    if(empty($nom)) {
        $erreurs[] = 'Le nom est requis';
    } elseif(strlen($nom) > 50) {
        $erreurs[] = 'Le nom ne doit pas dépasser 50 caractères';
    }
    
    if(empty($email)) {
        $erreurs[] = 'L\'email est requis';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'email n\'est pas valide';
    } elseif(strlen($email) > 100) {
        $erreurs[] = 'L\'email ne doit pas dépasser 100 caractères';
    }
    
    if(empty($password)) {
        $erreurs[] = 'Le mot de passe est requis';
    } elseif(strlen($password) < 8) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères';
    } elseif(!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $erreurs[] = 'Le mot de passe doit contenir au moins une majuscule et un chiffre';
    }
    
    if($password !== $confirmation) {
        $erreurs[] = 'Les mots de passe ne correspondent pas';
    }
    
    if(!in_array($role, $roles_disponibles)) {
        $erreurs[] = 'Rôle invalide';
    }

    // Si c'est un agent, vérifier qu'il existe dans la table agents
    if($role === 'agent') {
        try {
            $stmt = $db->prepare("SELECT id FROM agents WHERE email = ?");
            $stmt->execute([$email]);
            
            if(!$stmt->fetch()) {
                $erreurs[] = 'Pour s\'inscrire comme agent, votre email doit être enregistré dans le système. Contactez l\'administrateur.';
            }
        } catch(PDOException $e) {
            $erreurs[] = 'Erreur lors de la vérification de l\'agent: ' . $e->getMessage();
        }
    }

    // Vérification de l'email unique
    try {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if($stmt->fetch()) {
            $erreurs[] = 'Cet email est déjà utilisé';
        }
    } catch(PDOException $e) {
        $erreurs[] = 'Erreur de vérification de l\'email: ' . $e->getMessage();
    }
    
    // Si pas d'erreurs, on procède à l'inscription
    if(empty($erreurs)) {
        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            $query = "INSERT INTO users (nom, email, password, role) 
                     VALUES (:nom, :email, :password, :role)";
            $stmt = $db->prepare($query);
            
            $stmt->execute([
                ':nom' => $nom,
                ':email' => $email,
                ':password' => $password_hash,
                ':role' => $role
            ]);
            
            $succes = 'Inscription réussie! Vous pouvez maintenant vous connecter.';
            
            // Réinitialisation du formulaire
            $_POST = [];
        } catch(PDOException $e) {
            $erreurs[] = 'Erreur lors de l\'inscription: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 animate__animated animate__fadeIn">
            <div class="card shadow-lg">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Inscription Admin</h2>
                        <p class="text-muted">Créez votre compte administrateur</p>
                    </div>

                    <?php if(!empty($erreurs)): ?>
                    <div class="alert alert-danger animate__animated animate__shakeX">
                        <ul class="mb-0">
                            <?php foreach($erreurs as $erreur): ?>
                            <li><?php echo $erreur; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if($succes): ?>
                    <div class="alert alert-success animate__animated animate__fadeIn">
                        <?php echo $succes; ?>
                        <div class="mt-2">
                            <a href="connexion.php" class="btn btn-sm btn-success">Se connecter</a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!$succes): ?>

                    <form method="post">
                        <div class="form-group">
                            <label for="nom">Nom complet</label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Mot de passe (min 8 caractères, majuscule et chiffre)</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirmation">Confirmation du mot de passe</label>
                            <input type="password" class="form-control" id="confirmation" name="confirmation" required>
                        </div>

                        <div class="form-group">
                            <label for="role">Rôle</label>
                            <select class="form-control" id="role" name="role">
                                <option value="user" <?php echo ($_POST['role'] ?? '') === 'user' ? 'selected' : ''; ?>>
                                    Utilisateur standard</option>
                                <option value="admin"
                                    <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrateur
                                </option>
                            </select>
                        </div><br>

                        <button type="submit" class="btn btn-primary">S'inscrire</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Déjà un compte? <a href="connexion.php" class="text-decoration-none">Connectez-vous</a></p>
                        <a href="../index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Retour à l'accueil
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

