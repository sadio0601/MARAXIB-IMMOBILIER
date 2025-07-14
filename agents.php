<?php 
require_once 'config.php';
$page_title = "Nos Agents";




?>

<style>
    .form-control{
        background-color: rgba(255, 255, 255, 0.4);
    }
</style>

<!-- Hero Section -->
<section class="hero-section-small d-flex align-items-center justify-content-center text-center text-white " style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/bgagent.png'); background-size: cover; background-position: center; height: 120vh;">
    <div class="container">
        <div class="row">
            <div class="col-12 animate__animated animate__fadeInDown">
                <h1 class="display-4 fw-bold mb-4">Nos Agents Immobiliers</h1>
                <p class="lead mb-5">Rencontrez notre équipe d'experts dévoués</p>

                <!-- Barre de recherche -->
                <input type="text" id="searchInput" class="form-control " placeholder="Rechercher un agent par nom..." onkeyup="filterAgents()">
            </div>
        </div>
    </div>
</section>

<!-- Liste des agents -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5 animate-on-scroll">
            <div class="col-12 text-center">
                <h2 class="fw-bold">Notre équipe</h2>
                <p class="text-muted">Des professionnels à votre service</p>
            </div>
        </div>
        
        <div class="row g-4" id="agentsContainer">
            <?php
            try {
                $query = "SELECT * FROM agents ORDER BY nom, prenom";
                $stmt = $db->query($query);
                $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if(count($agents) > 0) {
                    foreach($agents as $index => $agent) {
                        $animation_delay = ($index % 3) * 0.2;
                        echo '
                        <div class="col-md-4 animate-on-scroll agent-card" style="animation-delay: '.$animation_delay.'s">
                            <div class="card card-hover text-center h-100">
                                <div class="card-body pt-5 px-4 pb-4">
                                    <img src="'.$agent['photo'].'" alt="'.$agent['prenom'].' '.$agent['nom'].'" class="agent-img mb-3">
                                    <h5 class="card-title mb-1">'.$agent['prenom'].' '.$agent['nom'].'</h5>
                                    <p class="text-muted mb-3">Agent immobilier</p>
                                    <div class="d-flex justify-content-center gap-3 mt-3">
                                        <a href="tel:'.$agent['telephone'].'" class="btn btn-sm btn-outline-primary rounded-circle">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                        <a href="mailto:'.$agent['email'].'" class="btn btn-sm btn-outline-primary rounded-circle">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <a href="biens.php?agent='.$agent['id'].'" class="btn btn-primary">
                                        <i class="fas fa-home me-2"></i>Voir ses biens
                                    </a>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div class="col-12 text-center text-muted">Aucun agent disponible pour le moment.</div>';
                }
            } catch(PDOException $e) {
                echo '<div class="col-12 text-center text-danger">Erreur de chargement des agents</div>';
            }
            ?>
        </div>
    </div>
</section>

<script>
function filterAgents() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const agents = document.getElementsByClassName('agent-card');

    for (let i = 0; i < agents.length; i++) {
        const title = agents[i].getElementsByClassName('card-title')[0].textContent;
        if (title.toLowerCase().indexOf(filter) > -1) {
            agents[i].style.display = "";
        } else {
            agents[i].style.display = "none";
        }
    }
}
</script>

