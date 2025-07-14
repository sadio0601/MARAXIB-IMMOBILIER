<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// Messages de succès/erreur
$message = '';
$message_type = '';

// Suppression d'un agent
if (isset($_GET['supprimer'])) {
    $agent_id = intval($_GET['supprimer']);
    
    try {
        // Vérifier si l'agent a des biens associés
        $query = "SELECT COUNT(*) FROM biens WHERE agent_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$agent_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $message = 'Impossible de supprimer cet agent car il a des propriétés associées.';
            $message_type = 'danger';
        } else {
            // Supprimer l'agent
            $query = "DELETE FROM agents WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$agent_id]);
            
            $message = 'Agent supprimé avec succès.';
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = 'Erreur lors de la suppression: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Récupérer la liste des agents
try {
    $query = "SELECT * FROM agents ORDER BY nom, prenom";
    $stmt = $db->query($query);
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = 'Erreur lors du chargement des agents: ' . $e->getMessage();
    $message_type = 'danger';
    $agents = [];
}

ob_start(); // Capturer le contenu spécifique
?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Liste des Agents</h4>
        <div class="float-end">
            <a href="ajouter_agent.php" class="btn btn-sm btn-outline-success">
                <i class="fas fa-plus"></i> Ajouter un Agent
            </a>
        </div>
    </div>

    <?php if($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> animate__animated animate__fadeIn">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($agents) > 0): ?>
                    <?php foreach($agents as $agent): ?>
                    <tr class="animate__animated animate__fadeIn">
                        <td>
                            <img src="<?php echo $agent['photo']; ?>"
                                alt="<?php echo $agent['prenom'].' '.$agent['nom']; ?>" class="rounded-circle"
                                width="50" height="50">
                        </td>
                        <td><?php echo $agent['prenom'].' '.$agent['nom']; ?></td>
                        <td><?php echo $agent['email']; ?></td>
                        <td><?php echo $agent['telephone'] ?? 'N/A'; ?></td>
                        <td>
                            <a href="modifier_agent.php?id=<?php echo $agent['id']; ?>"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="liste_agents.php?supprimer=<?php echo $agent['id']; ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet agent?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aucun agent enregistré</td>
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
?>