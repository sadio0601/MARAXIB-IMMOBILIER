<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

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

ob_start(); // Capturer le contenu spécifique
?>
<div class="row">
    <div class="col-md-3">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Utilisateurs</h5>
                <h2><?php echo $stats['total_users'] ?? '0'; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="utilisateurs.php">Voir détails</a>
                <i class="bi bi-arrow-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Biens immobiliers</h5>
                <h2><?php echo $stats['total_biens'] ?? '0'; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="affiche_bien.php">Voir détails</a>
                <i class="bi bi-arrow-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-dark mb-4">
            <div class="card-body">
                <h5 class="card-title">Agents</h5>
                <h2><?php echo $stats['total_agents'] ?? '0'; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-dark stretched-link" href="liste_agents.php">Voir détails</a>
                <i class="bi bi-arrow-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Demandes</h5>
                <h2><?php echo $stats['total_demandes'] ?? '0'; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="demandes.php">Voir détails</a>
                <i class="bi bi-arrow-right"></i>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h4>Derniers biens ajoutés</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Prix</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $db->prepare("SELECT * FROM biens ORDER BY created_at DESC LIMIT 5");
                        $stmt->execute();
                        $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($biens as $bien): ?>
                            <tr>
                                <td><?php echo $bien['id']; ?></td>
                                <td><?php echo htmlspecialchars($bien['titre']); ?></td>
                                <td><?php echo number_format($bien['prix'], 2, ',', ' '); ?> FCFA</td>
                                <td><?php echo htmlspecialchars($bien['ville']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $bien['statut'] === 'validée' ? 'success' : 
                                             ($bien['statut'] === 'refusée' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($bien['statut']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="details.php?id=<?php echo $bien['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="modifier_bien.php?id=<?php echo $bien['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach;
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='6'>Erreur lors de la récupération des biens</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include 'dashboard_template.php';