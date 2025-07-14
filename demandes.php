

<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// Récupérer les demandes
try {
    $query = "SELECT d.*, p.titre, p.ville FROM demandes d LEFT JOIN biens p ON d.id_propriete = p.id ORDER BY d.created_at DESC";
    $stmt = $db->query($query);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Erreur lors du chargement des demandes: " . $e->getMessage();
    exit;
}

// Traitement des actions sur les demandes
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id_demande = intval($_GET['id']);
    
    // Récupérer l'email de l'utilisateur
    $query = "SELECT email FROM demandes WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id_demande]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $email_utilisateur = $demande['email'];
    
    if ($action === 'valider') {
        // Mettre à jour la demande à "validée"
        $query = "UPDATE demandes SET statut = 'validée' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id_demande]);
        mail($email_utilisateur, "Demande validée", "Votre demande a été validée. Veuillez procéder au paiement.");
    } elseif ($action === 'refuser') {
        // Mettre à jour la demande à "refusée"
        $query = "UPDATE demandes SET statut = 'refusée' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id_demande]);
        mail($email_utilisateur, "Demande refusée", "Votre demande a été refusée.");
    } elseif ($action === 'en_attente') {
        // Mettre à jour la demande à "en attente"
        $query = "UPDATE demandes SET statut = 'en attente' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id_demande]);
        mail($email_utilisateur, "Demande en attente", "Votre demande est en attente.");
    }
}

ob_start(); // Capturer le contenu spécifique

?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Liste de Demandes</h4>
    </div>
  
    
    <div class="card-body">
        <div class="table-responsive">
              <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Titre</th>
                                    <th>Ville</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($demandes) > 0): ?>
                                    <?php foreach($demandes as $demande): ?>
                                        <tr>
                                            <td><?php echo $demande['id']; ?></td>
                                            <td><?php echo $demande['titre']; ?></td>
                                            <td><?php echo $demande['ville']; ?></td>
                                            <td><?php echo $demande['email']; ?></td>
                                            <td><?php echo $demande['telephone']; ?></td>
                                            <td>
                                                <a href="demandes.php?action=valider&id=<?php echo $demande['id']; ?>" class="btn btn-sm btn-success">Valider</a>
                                                <a href="demandes.php?action=refuser&id=<?php echo $demande['id']; ?>" class="btn btn-sm btn-danger">Refuser</a>
                                                <a href="demandes.php?action=en_attente&id=<?php echo $demande['id']; ?>" class="btn btn-sm btn-warning">En Attente</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Aucune demande enregistrée</td>
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

 