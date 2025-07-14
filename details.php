<?php
require_once 'config.php';



// Récupérer l'ID du bien
$id_propriete = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_propriete > 0) {
    try {
        $query = "SELECT m.*, CONCAT(a.prenom, ' ', a.nom) AS agent_nom 
                  FROM biens m 
                  LEFT JOIN agents a ON m.agent_id = a.id 
                  WHERE m.id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id_propriete]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur de chargement des détails.";
        exit;
    }
}

// Vérifiez si le bien existe
if (!$property) {
    echo "<h2>Bien non trouvé</h2>";
    exit;
}
?>


<section class="hero-section-small d-flex align-items-center justify-content-center text-center text-white " style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/IMG_7679.png'); background-size: cover; background-position: center; height: 120vh;">
    <div class="container">
        <div class="row">
            <div class="col-12 animate__animated animate__fadeInDown mb-5">
                <h1 class="display-4 fw-bold mb-4">Détails de la Propriété</h1>
                <p class="lead mb-5">Voici toutes les informations concernant cette propriété.</p>
            </div>
        </div>
    </div>
</section>
<section class="property-details mt-5">
    <div class="container">
      
        <!-- Button Return -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="biens.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Récupération des données du formulaire
                $id_user = $_SESSION['user_id']; // Assurez-vous que l'utilisateur est connecté
                $email = $_POST['email'];
                $telephone = $_POST['telephone'];
                $message = $_POST['message'];

                try {
                    // Insertion dans la table demandes
                    $query = "INSERT INTO demandes (id_propriete, id_user, telephone, email, message) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$id_propriete, $id_user, $telephone, $email, $message]);
                    echo "<div class='alert alert-success mt-5'>Votre message a été envoyé avec succès.</div>";
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger mt-5'>Erreur lors de l'envoi de votre message: " . $e->getMessage() . "</div>";
                }
            }
        ?>

        <h1><?php echo $property['titre']; ?></h1>
        <div class="row">
            <!-- Informations du bien -->
            <div class="col-md-6">
                <p class="fs-3"><?php echo $property['description']; ?></p><br /><br />
                <img src="<?php echo $property['photo']; ?>" class="img-fluid" alt="<?php echo $property['titre']; ?>"> 
                
                <div class="container mt-5">
                    <h2 class="text-center">Aperçu</h2>
                    <ul class="list-unstyled row text-center">
                        <li class="col-md-4 mb-3">
                            <div class="card p-3">
                                <i class="fas fa-tags fa-2x"></i>
                                <h5 class="fs-5">Type de biens:</h5>
                                <p><?php echo ucfirst($property['type_id']); ?></p>
                            </div>
                        </li>
                        <li class="col-md-4 mb-3">
                            <div class="card p-3">
                                <i class="fas fa-bed fa-2x"></i>
                                <h5 class="fs-5">Chambres:</h5>
                                <p><?php echo $property['chambres']; ?></p>
                            </div>
                        </li>
                        <li class="col-md-4 mb-3">
                            <div class="card p-3">
                                <i class="fas fa-bath fa-2x"></i>
                                <h5 class="fs-5">Salles de bain:</h5>
                                <p><?php echo $property['bathroom']; ?></p>
                            </div>
                        </li>
                        <li class="col-md-4 mb-3">
                            <div class="card p-3">
                                <i class="fas fa-ruler-combined fa-2x"></i>
                                <h5 class="fs-5">Superficie:</h5>
                                <p><?php echo $property['superficie']; ?> m²</p>
                            </div>
                        </li>
                        <li class="col-md-4 mb-3">
                            <div class="card p-3">
                                <i class="fas fa-map-marker-alt fa-2x"></i>
                                <h5 class="fs-5">Adresse:</h5>
                                <p><?php echo $property['adresse'] . ', ' . $property['ville'] ; ?></p>
                            </div>
                        </li>
                        <li class="col-md-4 mb-3">
                            <div class="card p-3">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                                <h5 class="fs-5">Prix:</h5>
                                <p><?php echo number_format($property['prix'], 0, ',', ' ') . ' FCFA'; ?></p>
                            </div>
                        </li>
                        <li class="col-md-4 mb-3">
                            <div class="card p-3">
                                <i class="fas fa-user-tie fa-2x"></i>
                                <h5 class="fs-5">Agent:</h5>
                                <p><?php echo $property['agent_nom'] ? $property['agent_nom'] : 'Non attribué'; ?></p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-2"></div>

            <!-- Formulaire de contact avec les informations de contact -->
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <img src="img/logo1.png" alt="Logo" class="img-fluid" style="max-width: 150px;"> <br>
                    <ul class="list-unstyled">
                        <li>Pikine Rue 10, Dakar</li> <br>
                        <li><a href="tel: +221 77 123 45 67" class="text-black btn btn-sm btn-outline-primary"><strong>+221 77 123 45 67</strong></a></li> <br>
                        <li><a href="mailto: agenceimmobiliere@gmail.com" class="text-black btn btn-sm btn-outline-primary"><strong>agenceimmobiliere@gmail.com</strong></a></li>  <hr>
                    </ul>

                    <div class="text-center mt-4">
                        <h5>Suivez-nous sur</h5>
                        <div>
                            <a href="#"><i class="fab fa-facebook fa-2x mx-2"></i></a>
                            <a href="#"><i class="fab fa-twitter fa-2x mx-2"></i></a>
                            <a href="#"><i class="fab fa-instagram fa-2x mx-2"></i></a>
                            <a href="#"><i class="fab fa-linkedin fa-2x mx-2"></i></a>
                        </div> <hr>
                    </div>
                </div>

                <form action="" method="POST">
                    <input type="hidden" name="id_propriete" value="<?php echo $id_propriete; ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom Complet</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Numéro de Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" required>
Bonjour, je suis intéressé par <?php echo $property['ville'] . ': ' . $property['titre'] . ' de ' . $property['chambres'] . ' chambres à ' . $property['type_id']; ?>
                        </textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer le message</button>
                </form>
            </div>
            
        </div>
    </div>
</section>

<?php include('footer.php'); ?>