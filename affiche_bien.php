<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

// Gérer la suppression d'un bien
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    try {
        $query = "DELETE FROM biens WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        $_SESSION['success'] = 'Le bien a été supprimé avec succès!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression du bien: ' . $e->getMessage();
    }
}

// Récupérer les biens
try {
    $query = "SELECT * FROM biens ORDER BY id DESC";
    $stmt = $db->query($query);
    $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors du chargement des biens: " . $e->getMessage();
    exit;
}

ob_start(); // Capturer le contenu spécifique
?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Derniers biens ajoutés</h4>
        <a href="ajouter_bien.php" class="btn btn-primary float-end">
            <i class="fas fa-plus"></i> Ajouter un bien 
        </a>
    </div>
    <div class="card-body">
        <!-- Affichage des messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Ville</th>
                        <th>Prix</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($biens) > 0): ?>
                    <?php foreach($biens as $bien): ?>
                    <tr>
                        <td><?php echo $bien['id']; ?></td>
                        <td><?php echo $bien['titre']; ?></td>
                        <td><?php echo $bien['ville']; ?></td>
                        <td><?php echo number_format($bien['prix'], 0, ',', ' ') . ' FCFA'; ?></td>
                        <td>
                            <a href="modifier_bien.php?id=<?php echo $bien['id']; ?>"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="affiche_bien.php?supprimer=<?php echo $bien['id']; ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet bien?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aucun bien enregistré</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

