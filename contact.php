<?php
require_once 'config.php';

// Messages de succès/erreur
$message = '';
$message_type = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['cname'] ?? '';
    $email = $_POST['cemail'] ?? '';
    $telephone = $_POST['cphone'] ?? '';
    $adresse = $_POST['cadresse'] ?? '';
    $messageText = $_POST['cdemande'] ?? '';

    // Validation simple
    if (!empty($nom) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($messageText)) {
        try {
            $query = "INSERT INTO contact (nom, email, telephone, adresse, message) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$nom, $email, $telephone, $adresse, $messageText]);
            $message = 'Votre message a été envoyé avec succès.';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Erreur lors de l\'envoi: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = 'Veuillez remplir tous les champs correctement.';
        $message_type = 'danger';
    }
}


?>

<style>
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/logo2.png');
        background-size: cover;
        background-position: center;
        height: 90vh;
        color: white;
    }
</style>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center justify-content-center text-center text-white parallax">
    <div class="container">
        <div class="row">
            <div class="col-12 animate__animated animate__fadeInDown">
                <h1 class="display-3 fw-bold mb-4">Contactez-nous</h1>
                <p class="lead mb-5">Nous sommes là pour vous aider</p>
            </div>
        </div>
    </div>
</section>

<!-- Formulaire de contact -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h2 class="light-title">Ecrivez nous</h2>
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> animate__animated animate__fadeIn">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="form-group required-field">
                        <label for="contact-name">Nom Complet</label>
                        <input type="text" class="form-control" id="contact-name" name="cname" required>
                    </div>
                    <div class="form-group required-field">
                        <label for="contact-email">Email</label>
                        <input type="email" class="form-control" id="cemail" name="cemail" required>
                    </div>
                    <div class="form-group">
                        <label for="contact-phone">Téléphone</label>
                        <input type="tel" class="form-control" id="cphone" name="cphone">
                    </div>
                    <div class="form-group required-field">
                        <label for="contact-address">Adresse</label>
                        <input type="text" class="form-control" id="cadresse" name="cadresse" required>
                    </div>
                    <div class="form-group required-field">
                        <label for="contact-message">Votre message</label>
                        <textarea cols="30" rows="3" id="cdemande" class="form-control" name="cdemande" required></textarea>
                    </div><br>
                    <div class="form-footer">
                        <button type="submit" name="csend" id="csend" class="btn btn-primary">Envoyer</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <h2 class="light-title">Localisation</h2>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3009.1340428877343!2d-17.396837626232667!3d14.762168473103332!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xec10c9bce10e76b%3A0x85de2dbe841da3d!2sEcole%205!5e1!3m2!1sfr!2ssn!4v1731240613541!5m2!1sfr!2ssn" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                <img src="img/logo1.png" alt="Logo" class="img-fluid mt-5" style="max-width: 150px;"> <br>
            </div>
        </div>
    </div>
</section>

