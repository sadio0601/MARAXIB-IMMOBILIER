<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'user') {
    header('Location: connexion.php');
    exit;
}

// Récupérer les données de l'utilisateur
$user_id = $_SESSION['user_id'];

try {
    // Statistiques des demandes seulement
    $stmt = $db->prepare("SELECT COUNT(*) as total_demandes FROM demandes WHERE id_user = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les 5 dernières demandes
    $stmt = $db->prepare("
        SELECT * FROM demandes 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $demandes_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des données: " . $e->getMessage();
}



ob_start();
?>
<div class="container">
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Mes demandes</h5>
                    <h2><?php echo htmlspecialchars($stats['total_demandes'] ?? '0'); ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="demande_user.php?id=<?php echo $user_id; ?>">
                        Voir toutes mes demandes
                    </a>
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Mes dernières demandes</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($demandes_recentes)): ?>
                        <div class="list-group">
                            <?php foreach($demandes_recentes as $demande): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">
                                        <?php echo htmlspecialchars($demande['type_demande'] ?? 'Type non spécifié'); ?>
                                    </h5>
                                    <small>
                                        <?php echo isset($demande['created_at']) ? date('d/m/Y H:i', strtotime($demande['created_at'])) : 'Date inconnue'; ?>
                                    </small>
                                </div>
                                <p class="mb-1">
                                    <?php echo htmlspecialchars($demande['details'] ?? 'Aucun détail fourni'); ?>
                                </p>
                                <?php if(isset($demande['statut'])): ?>
                                <span class="badge bg-<?php 
                                    echo $demande['statut'] === 'validée' ? 'success' : 
                                         ($demande['statut'] === 'en attente' ? 'warning' : 'secondary'); 
                                ?>">
                                    <?php echo htmlspecialchars($demande['statut']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Vous n'avez fait aucune demande récemment</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include 'dashboard_template.php';