<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// Vérifiez si un ID d'utilisateur est fourni
if (!isset($_GET['id'])) {
    header('Location: modifier_user.php');
    exit;
}

$user_id = intval($_GET['id']);
$user = null;

// Récupérer les données de l'utilisateur existant
try {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: modifier_user.php');
        exit;
    }
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des données de l'utilisateur: " . $e->getMessage();
    exit;
}

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = securiser($_POST['nom']);
    $email = securiser($_POST['email']);
    
    // Validation
    $erreurs = [];
    if (empty($nom)) {
        $erreurs[] = 'Le nom est requis.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'email est requis et doit être valide.';
    }

    // Vérifier si l'email existe déjà
    try {
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email, $user_id]);
        
        if ($stmt->fetch()) {
            $erreurs[] = 'Cet email est déjà utilisé par un autre utilisateur.';
        }
    } catch (PDOException $e) {
        $erreurs[] = 'Erreur lors de la vérification de l\'email: ' . $e->getMessage();
    }

    // Si pas d'erreurs, mettre à jour l'utilisateur
    if (empty($erreurs)) {
        try {
            $query = "UPDATE users SET nom = ?, email = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$nom, $email, $user_id]);
            
            $_SESSION['success'] = 'L\'utilisateur a été modifié avec succès!';
            header('Location: modifier_user.php');
            exit;
        } catch (PDOException $e) {
            $erreurs[] = 'Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage();
        }
    }
}

ob_start(); // Capturer le contenu spécifique
?>

<!-- Main content -->
<main class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Modifier un Utilisateur</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="utilisateurs.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <?php if (!empty($erreurs)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($erreurs as $erreur): ?>
            <li><?php echo $erreur; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['success']; ?>
        <?php unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $user['nom']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rôle</label>
                    <input type="text" class="form-control" value="<?php echo $user['role']; ?>" readonly>
                </div>
                <button type="submit" class="btn btn-primary">Modifier l'utilisateur</button>
            </form>
        </div>
    </div>
</main>

<?php
$content = ob_get_clean();
include 'dashboard_template.php';
?>