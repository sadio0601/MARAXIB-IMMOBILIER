<?php

error_reporting(0); // Désactive TOUTES les erreurs PHP
ini_set('display_errors', 0); // Masque l'affichage des erreurs
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'agent') {
    header('Location: connexion.php');
    exit;
}

// Récupérer l'ID de l'agent lié à l'utilisateur
$user_id = $_SESSION['user_id'];

try {
    // Trouver l'agent correspondant à l'utilisateur
    $stmt = $db->prepare("SELECT id FROM agents WHERE email = (SELECT email FROM users WHERE id = ?)");
    $stmt->execute([$user_id]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    $agent_id = $agent['id'];

    // Statistiques de l'agent
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM biens WHERE agent_id = ?) as total_biens,
            (SELECT COUNT(*) FROM biens WHERE agent_id = ? AND statut = 'validée') as biens_valides,
            (SELECT COUNT(*) FROM demandes d JOIN biens b ON d.id_propriete = b.id WHERE b.agent_id = ?) as total_demandes
    ");
    $stmt->execute([$agent_id, $agent_id, $agent_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dernières demandes
    $stmt = $db->prepare("
        SELECT d.*, b.titre as bien_titre 
        FROM demandes d
        JOIN biens b ON d.id_propriete = b.id
        WHERE b.agent_id = ?
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$agent_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des données: " . $e->getMessage();
}

ob_start();
?>
<div class="row">
    <div class="col-md-4">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Biens gérés</h5>
                <h2><?php echo $stats['total_biens'] ?? '0'; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="mes_biens.php">Voir mes biens</a>
                <i class="bi bi-arrow-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Biens validés</h5>
                <h2><?php echo $stats['biens_valides'] ?? '0'; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="mes_biens.php?statut=validée">Voir les biens validés</a>
                <i class="bi bi-arrow-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-info text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Demandes reçues</h5>
                <h2><?php echo $stats['total_demandes'] ?? '0'; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="mes_demandes.php">Voir les demandes</a>
                <i class="bi bi-arrow-right"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Dernières demandes</h4>
                <a href="mes_demandes.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if(!empty($demandes)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bien</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($demandes as $demande): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($demande['bien_titre'], 0, 20)); ?>...</td>
                                    <td><?php echo date('d/m/Y', strtotime($demande['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $demande['statut'] === 'validée' ? 'success' : 
                                                 ($demande['statut'] === 'refusée' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($demande['statut']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="voir_demande.php?id=<?php echo $demande['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Aucune demande récente</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Mes biens récents</h4>
                <a href="ajouter_bien.php" class="btn btn-sm btn-success">
                    <i class="bi bi-plus"></i> Ajouter
                </a>
            </div>
            <div class="card-body">
                <?php
                try {
                    $stmt = $db->prepare("
                        SELECT * FROM biens 
                        WHERE agent_id = ?
                        ORDER BY created_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$agent_id]);
                    $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if(!empty($biens)): ?>
                        <div class="list-group">
                            <?php foreach($biens as $bien): ?>
                            <a href="modifier_bien.php?id=<?php echo $bien['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo htmlspecialchars(substr($bien['titre'], 0, 30)); ?></h5>
                                    <span class="badge bg-<?php 
                                        echo $bien['statut'] === 'validée' ? 'success' : 
                                             ($bien['statut'] === 'refusée' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($bien['statut']); ?>
                                    </span>
                                </div>
                                <p class="mb-1">
                                    <?php echo number_format($bien['prix'], 0, ',', ' '); ?> € | 
                                    <?php echo $bien['ville']; ?> | 
                                    <?php echo $bien['superficie']; ?> m²
                                </p>
                                <small>Ajouté le <?php echo date('d/m/Y', strtotime($bien['created_at'])); ?></small>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucun bien enregistré</p>
                    <?php endif;
                } catch (PDOException $e) {
                    echo '<p class="text-danger">Erreur lors du chargement des biens</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include 'dashboard_template.php';