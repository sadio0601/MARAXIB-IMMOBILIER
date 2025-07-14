
        
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <img src="img/logo2.png" alt="" class="img-fluid" style="max-width: 150px;">
                    <p>Votre plateforme de gestion immobilière complète.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liens utiles</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Accueil</a></li>
                        <li><a href="biens.php" class="text-white">Recherche de biens</a></li>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <address>
                        Email: <a href="mailto:agenceimmobiliere@gmail.com" class="text-white">agenceimmobiliere@gmail.com</a><br>
                        Tél: <a href="tel: +221 77 123 45 67" class="text-white">+221 77 123 45 67</a>
                    </address>
                </div>
            </div>
            <div class="text-center mt-3">
                <p>&copy; <?php echo date('Y'); ?> MARAXIB-IMMOBILIER - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>

<script>
        // Animation au chargement de la page
        $(document).ready(function() {
            // Animation des chiffres
            $('.counter').counterUp({
                delay: 10,
                time: 1000
            });
            
            // Animation au scroll
            $('.animate-on-scroll').each(function() {
                $(this).waypoint(function() {
                    $(this.element).addClass('animate__animated animate__fadeInUp');
                }, {
                    offset: '80%'
                });
            });
            
            // Effet de parallaxe
            $(window).scroll(function() {
                var scrollPosition = $(this).scrollTop();
                $('.parallax').css('background-position-y', -scrollPosition * 0.5 + 'px');
            });
        });
          $(window).scroll(function() {
            if($(this).scrollTop() > 100) {
                $('.navbar').addClass('scrolled');
            } else {
                $('.navbar').removeClass('scrolled');
            }
        });
    </script>
