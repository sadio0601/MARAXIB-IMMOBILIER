<?php
require_once 'config.php';

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Initialisation des variables
$erreurs = [];
$success = '';
$user_data = [];
$agent_data = [];

// Récupérer les données utilisateur
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si c'est un agent, récupérer les données supplémentaires
    if ($user_role === 'agent') {
        $stmt = $db->prepare("SELECT * FROM agents WHERE email = ?");
        $stmt->execute([$user_data['email']]);
        $agent_data = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $erreurs[] = "Erreur lors de la récupération des données: " . $e->getMessage();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation des champs
    if (empty($nom)) {
        $erreurs[] = "Le nom est obligatoire";
    }

    if (empty($email)) {
        $erreurs[] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide";
    }

    // Vérification du mot de passe actuel si changement demandé
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user_data['password'])) {
            $erreurs[] = "Le mot de passe actuel est incorrect";
        } elseif (strlen($new_password) < 8) {
            $erreurs[] = "Le nouveau mot de passe doit contenir au moins 8 caractères";
        } elseif ($new_password !== $confirm_password) {
            $erreurs[] = "Les nouveaux mots de passe ne correspondent pas";
        }
    }

    // Si pas d'erreurs, mise à jour des données
    if (empty($erreurs)) {
        try {
            $db->beginTransaction();

            // Mise à jour des données utilisateur
            $query = "UPDATE users SET nom = ?, email = ?";
            $params = [$nom, $email];

            // Si changement de mot de passe
            if (!empty($new_password)) {
                $query .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_BCRYPT);
            }

            $query .= " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $db->prepare($query);
            $stmt->execute($params);

            // Mise à jour des données agent si nécessaire
            if ($user_role === 'agent' && !empty($agent_data)) {
                $stmt = $db->prepare("
                    UPDATE agents SET 
                        nom = ?, 
                        prenom = ?,
                        email = ?,
                        telephone = ?,
                        description = ?
                    WHERE id = ?
                ");
                
                // Séparation du nom complet en nom/prenom pour les agents
                $noms = explode(' ', $nom, 2);
                $nom_agent = $noms[0] ?? '';
                $prenom_agent = $noms[1] ?? '';

                $stmt->execute([
                    $nom_agent,
                    $prenom_agent,
                    $email,
                    $telephone,
                    trim($_POST['description'] ?? ''),
                    $agent_data['id']
                ]);
            }

            $db->commit();

            // Mettre à jour les données de session
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_email'] = $email;

            $success = "Vos informations ont été mises à jour avec succès";
            
            // Recharger les données
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_role === 'agent') {
                $stmt = $db->prepare("SELECT * FROM agents WHERE email = ?");
                $stmt->execute([$email]);
                $agent_data = $stmt->fetch(PDO::FETCH_ASSOC);
            }

        } catch (PDOException $e) {
            $db->rollBack();
            $erreurs[] = "Erreur lors de la mise à jour: " . $e->getMessage();
        }
    }
}

ob_start();
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($user_data['nom'] ?? ''); ?></h4>
                    <span class="badge bg-<?php 
                        echo $user_role === 'admin' ? 'danger' : 
                             ($user_role === 'agent' ? 'primary' : 'secondary'); 
                    ?>">
                        <?php echo ucfirst($user_role); ?>
                    </span>
                </div>
            </div>
            
            <?php if($user_role === 'agent' && !empty($agent_data)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Statistiques</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Biens gérés
                            <span class="badge bg-primary rounded-pill">
                                <?php 
                                    try {
                                        $stmt = $db->prepare("SELECT COUNT(*) FROM biens WHERE agent_id = ?");
                                        $stmt->execute([$agent_data['id']]);
                                        echo $stmt->fetchColumn();
                                    } catch (PDOException $e) {
                                        echo '0';
                                    }
                                ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Demandes
                            <span class="badge bg-primary rounded-pill">
                                <?php 
                                    try {
                                        $stmt = $db->prepare("
                                            SELECT COUNT(*) FROM demandes d
                                            JOIN biens b ON d.id_propriete = b.id
                                            WHERE b.agent_id = ?
                                        ");
                                        $stmt->execute([$agent_data['id']]);
                                        echo $stmt->fetchColumn();
                                    } catch (PDOException $e) {
                                        echo '0';
                                    }
                                ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h4>Modifier mon profil</h4>
                </div>
                <div class="card-body">
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($erreurs)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($erreurs as $erreur): ?>
                                    <li><?php echo $erreur; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo htmlspecialchars($user_data['nom'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($user_role === 'agent' && !empty($agent_data)): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" 
                                           value="<?php echo htmlspecialchars($agent_data['telephone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo htmlspecialchars($agent_data['description'] ?? ''); 
                            ?></textarea>
                        </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <h5 class="mb-3">Changer le mot de passe</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mot de passe actuel</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmation</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <small>Laissez les champs de mot de passe vides si vous ne souhaitez pas le changer</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

   include 'dashboard_template.php';