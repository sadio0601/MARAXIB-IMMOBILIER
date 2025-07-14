<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// Traitement du formulaire
$erreurs = [];
$succes = '';

// Vérifiez si un ID d'agent est fourni
if (!isset($_GET['id'])) {
    header('Location: liste_agents.php');
    exit;
}

$id = intval($_GET['id']);

// Récupérer les données de l'agent existant
try {
    $query = "SELECT * FROM agents WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agent) {
        header('Location: liste_agents.php');
        exit;
    }
} catch (PDOException $e) {
    $erreurs[] = 'Erreur lors de la récupération des données de l\'agent.';
}

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = securiser($_POST['nom']);
    $prenom = securiser($_POST['prenom']);
    $email = securiser($_POST['email']);
    $telephone = securiser($_POST['telephone']);
    
    // Validation
    if (empty($nom)) {
        $erreurs[] = 'Le nom est requis';
    }
    if (empty($prenom)) {
        $erreurs[] = 'Le prénom est requis';
    }
    if (empty($email)) {
        $erreurs[] = 'L\'email est requis';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'email n\'est pas valide';
    }

    // Traitement de l'image
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $photo = $agent['photo']; // Conserver l'ancienne photo par défaut
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Vérifie le format de l'image
        if (in_array($ext, $allowed)) {
            // Vérifie la taille de l'image
            if ($_FILES['photo']['size'] <= 2 * 1024 * 1024) { // 2 Mo max
                $photo_name = uniqid() . '.' . $ext;
                $photo_path = $upload_dir . $photo_name;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                    $photo = 'uploads/' . $photo_name;
                } else {
                    $erreurs[] = 'Erreur lors du téléchargement de l\'image. Vérifiez les permissions du dossier.';
                }
            } else {
                $erreurs[] = 'L\'image dépasse la taille maximale de 2 Mo.';
            }
        } else {
            $erreurs[] = 'Format d\'image non supporté. Utilisez JPG, PNG ou GIF.';
        }
    }

    // Vérifier si l'email existe déjà
    try {
        $query = "SELECT id FROM agents WHERE email = ? AND id != ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email, $id]);
        
        if ($stmt->fetch()) {
            $erreurs[] = 'Cet email est déjà utilisé par un autre agent';
        }
    } catch (PDOException $e) {
        $erreurs[] = 'Une erreur est survenue. Veuillez réessayer plus tard.';
    }
    
    // Si pas d'erreurs, mettre à jour l'agent
    if (empty($erreurs)) {
        try {
            $query = "UPDATE agents SET nom = ?, prenom = ?, email = ?, telephone = ?, photo = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$nom, $prenom, $email, $telephone, $photo, $id]);
            
            $succes = 'Agent modifié avec succès!';
        } catch (PDOException $e) {
            $erreurs[] = 'Erreur lors de la modification de l\'agent: ' . $e->getMessage();
        }
    }
}

ob_start(); // Capturer le contenu spécifique
?>

<!-- Main content -->
<main class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Modifier un Agent</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="liste_agents.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <?php if (!empty($erreurs)): ?>
    <div class="alert alert-danger animate__animated animate__shakeX">
        <ul class="mb-0">
            <?php foreach ($erreurs as $erreur): ?>
            <li><?php echo $erreur; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($succes): ?>
    <div class="alert alert-success animate__animated animate__fadeIn">
        <?php echo $succes; ?>
        <div class="mt-2">
            <a href="liste_agents.php" class="btn btn-sm btn-success">Voir la liste</a>
            <a href="modifier_agent.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-success">Modifier à nouveau</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$succes): ?>
    <div class="card shadow-sm animate__animated animate__fadeIn">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom"
                                value="<?php echo $agent['prenom']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                value="<?php echo $agent['nom']; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo $agent['email']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone"
                                value="<?php echo $agent['telephone']; ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Photo (laisser vide pour conserver l'ancienne)</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG ou GIF (max 2MB)</small>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php
$content = ob_get_clean();
include 'dashboard_template.php';
?>