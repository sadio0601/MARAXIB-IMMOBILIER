<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'user') {
    header('Location: connexion.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Requête simplifiée pour récupérer les biens demandés par l'utilisateur
    $stmt = $db->prepare("
        SELECT b.* FROM biens b
        JOIN demandes d ON b.id = d.bien_id
        WHERE d.user_id = ?
        ORDER BY d.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $biens_demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}


ob_start();
?>
<div class="container">
    
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h2>Mes biens demandés</h2>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="user_dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
    
    <?php if(!empty($biens_demandes)): ?>
        <div class="row">
            <?php foreach($biens_demandes as $bien): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if(!empty($bien['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($bien['image_url']); ?>" class="card-img-top" alt="Image du bien">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($bien['titre']); ?></h5>
                            <p class="card-text">
                                <strong>Prix:</strong> <?php echo number_format($bien['prix'], 0, ',', ' '); ?> €<br>
                                <strong>Localisation:</strong> <?php echo htmlspecialchars($bien['ville']); ?><br>
                                <strong>Surface:</strong> <?php echo htmlspecialchars($bien['superficie']); ?> m²<br>
                                <strong>Chambres:</strong> <?php echo htmlspecialchars($bien['chambres']); ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">
                                Ajouté le <?php echo date('d/m/Y', strtotime($bien['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Vous n'avez fait aucune demande de bien pour le moment.
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include 'dashboard_template.php';