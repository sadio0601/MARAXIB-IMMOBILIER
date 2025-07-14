<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// Messages de succès/erreur
$message = '';
$message_type = '';

// Suppression d'un utilisateur
if (isset($_GET['supprimer'])) {
    $user_id = intval($_GET['supprimer']);
    
    if ($user_id > 0) {
        try {
            $db->beginTransaction();
            
            // Supprimer d'abord les dépendances (uniquement les tables qui existent)
            $tables_dependantes = [
                'demandes' => 'id_user',  // Table demandes avec colonne id_user
                // 'favoris' => 'user_id'  // Commenté car la table n'existe pas
            ];
            
            foreach ($tables_dependantes as $table => $colonne) {
                try {
                    $db->exec("DELETE FROM $table WHERE $colonne = $user_id");
                } catch (PDOException $e) {
                    // Ignorer silencieusement si la table n'existe pas
                    continue;
                }
            }
            
            // Puis supprimer l'utilisateur
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            if ($stmt->rowCount() > 0) {
                $db->commit();
                $message = 'Utilisateur supprimé avec succès.';
                $message_type = 'success';
            } else {
                $db->rollBack();
                $message = 'Aucun utilisateur trouvé avec cet ID.';
                $message_type = 'danger';
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $message = 'Erreur lors de la suppression: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = 'ID utilisateur invalide.';
        $message_type = 'danger';
    }
}


// Récupérer les users
try {
    $query = "SELECT * FROM users ORDER BY id DESC";
    $stmt = $db->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors du chargement des users: " . $e->getMessage();
    exit;
}

ob_start();
?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Gestion des utilisateurs</h4>
        <a href="inscription.php" class="btn btn-primary float-end">
            <i class="fas fa-plus"></i> Ajouter un User 
        </a>
    </div>
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> animate__animated animate__fadeIn">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date de Création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $us): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($us['id']); ?></td>
                        <td><?php echo htmlspecialchars($us['nom']); ?></td>
                        <td><?php echo htmlspecialchars($us['email']); ?></td>
                        <td><?php echo htmlspecialchars($us['role']); ?></td>
                        <td><?php echo htmlspecialchars($us['created_at']); ?></td>
                        <td>
                            <a href="modifier_user.php?id=<?php echo $us['id']; ?>"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?supprimer=<?php echo $us['id']; ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Aucun utilisateur enregistré</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'dashboard_template.php';