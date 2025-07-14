<?php
require_once 'config.php';

if (!est_connecte() || $_SESSION['user_role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Traitement de l'image
        $upload_dir = __DIR__ . '/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $photo_name = uniqid() . '.' . strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $photo_path = $upload_dir . $photo_name;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
            // Préparation des données
            $data = [
                'titre' => securiser($_POST['titre']),
                'description' => securiser($_POST['description']),
                'prix' => floatval($_POST['prix']),
                'superficie' => intval($_POST['superficie']),
                'chambres' => intval($_POST['chambres']),
                'bathroom' => intval($_POST['bathroom']), 
                'type_id' => intval($_POST['type_id']),
                'categorie_id' => intval($_POST['categorie_id']),
                'adresse' => securiser($_POST['adresse']),
                'ville' => securiser($_POST['ville']),
                'code_postal' => securiser($_POST['code_postal']),
                'photo' => 'uploads/' . $photo_name,
                'agent_id' => intval($_POST['agent_id'])
            ];

            // Insertion en base
            $query = "INSERT INTO biens (titre, description, prix, superficie, chambres, bathroom, type_id, categorie_id, adresse, ville, code_postal, photo, agent_id) 
                      VALUES (:titre, :description, :prix, :superficie, :chambres, :bathroom, :type_id, :categorie_id, :adresse, :ville, :code_postal, :photo, :agent_id)";

            $stmt = $db->prepare($query);
            $stmt->execute($data);

            $_SESSION['success'] = 'Le bien a été ajouté avec succès!';
            header('Location: ajouter_bien.php');
            exit;
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'upload de l\'image.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur lors de l\'ajout du bien: ' . $e->getMessage();
    }
 }

ob_start(); // Capturer le contenu spécifique
?>


<!-- Main content -->
    <main class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Ajouter un Bien</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="affiche_bien.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        
        

            <!-- Affichage des messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success animate__animated animate__fadeIn">
                    <?php echo $_SESSION['success']; ?>
                    <div class="mt-2">
                        <a href="affiche_bien.php" class="btn btn-sm btn-success">Voir la liste</a>
                        <a href="ajouter_bien.php" class="btn btn-sm btn-outline-success">Ajouter un autre</a>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
                <?php elseif (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger animate__animated animate__shakeX">
                        <?php echo $_SESSION['error']; ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
            <?php endif; ?>
            
            <?php if (!isset($_SESSION['success'])): ?>
                <div class="card shadow-sm animate__animated animate__fadeIn">
                    <div class="card-body">
                        <form action="ajouter_bien.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="titre" class="form-label">Titre</label>
                                        <input type="text" class="form-control" name="titre" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Type d'offre</label>
                                        <select class="form-select" name="type_offre" required>
                                            <option value="vente">À vendre</option>
                                            <option value="location">À louer</option>
                                        </select>
                                    </div>
                                </div>
                                 <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Catégorie</label>
                                     <input type="number" class="form-control" name="categorie_id" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Prix (FCFA)</label>
                                    <input type="number" class="form-control" name="prix" min="10000" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Superficie (m²)</label>
                                    <input type="number" class="form-control" name="superficie" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Nombre de chambres</label>
                                    <input type="number" class="form-control" name="chambres" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre de salles de bain</label>
                                        <input type="number" class="form-control" name="bathroom" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" class="form-control" name="adresse" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Ville</label>
                                    <input type="text" class="form-control" name="ville" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Code postal</label>
                                        <input type="text" class="form-control" name="code_postal" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Agent responsable</label>
                                        <select class="form-select" name="agent_id" required>
                                            <?php
                                            $agents = $db->query("SELECT id, CONCAT(prenom, ' ', nom) AS nom_complet FROM agents");
                                            foreach ($agents as $agent) {
                                                echo "<option value='{$agent['id']}'>{$agent['nom_complet']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Photo principale</label>
                                <input type="file" class="form-control" name="photo" accept="image/*" required>
                            </div>
                            <div class="d-grid gap-2 mt-2">
                                <button type="submit" class="btn btn-primary">Ajouter le bien</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
<?php
$content = ob_get_clean();
include 'dashboard_template.php';