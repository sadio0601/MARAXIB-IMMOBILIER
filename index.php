

<?php
require_once 'config.php';
try {
    // Récupérer le nombre de biens disponibles
    $query_biens = "SELECT COUNT(*) FROM biens";
    $stmt_biens = $db->query($query_biens);
    $total_biens = $stmt_biens->fetchColumn();

    // Récupérer le nombre d'agents
    $query_agents = "SELECT COUNT(*) FROM agents";
    $stmt_agents = $db->query($query_agents);
    $total_agents = $stmt_agents->fetchColumn();

} catch (PDOException $e) {
    echo "Erreur lors de la récupération des données: " . $e->getMessage();
    $total_biens = 0;
    $total_agents = 0;
}


?>

<style>
    .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/bg.png');
            background-size: cover;
            background-position: center;
            height: 120vh;
            color: white;
        }
</style>


<!-- Hero Section -->
<section class="hero-section d-flex align-items-center justify-content-center text-center text-white parallax">
    <div class="container">
        <div class="row">
            <div class="col-12 animate__animated animate__fadeInDown">
                <h1 class="display-3 fw-bold mb-4">Trouvez votre maison de rêve</h1>
                <p class="lead mb-5">Découvrez notre sélection exclusive de propriétés à travers le pays</p>
                <a href="biens.php" class="btn btn-primary btn-lg px-4 me-2 animate__animated animate__pulse animate__infinite">
                    <i class="fas fa-search me-2"></i>Voir les biens
                </a>
                <a href="agents.php" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-users me-2"></i>Nos agents
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Statistiques -->

<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 animate-on-scroll">
                <div class="p-4">
                    <h2 class="counter display-4 fw-bold" data-count="<?php echo $total_biens; ?>"><?php echo $total_biens; ?></h2>
                    <p class="fs-5 text-muted">Biens disponibles</p>
                </div>
            </div>
            <div class="col-md-4 animate-on-scroll">
                <div class="p-4">
                    <h2 class="counter display-4 fw-bold" data-count="50" style="color: var(--color-accent);">20</h2>
                    <p class="fs-5 text-muted">Clients satisfaits</p>
                </div>
            </div>
            <div class="col-md-4 animate-on-scroll">
                <div class="p-4">
                    <h2 class="counter display-4 fw-bold" data-count="<?php echo $total_agents; ?>"><?php echo $total_agents; ?></h2>
                    <p class="fs-5 text-muted">Agents</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dernières propriétés -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5 animate-on-scroll">
            <div class="col-12 text-center">
                <h2 class="fw-bold">Nos dernières offres</h2>
                <p class="text-muted">Découvrez nos propriétés les plus récentes</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php
            try {
                $query = "SELECT * FROM biens ORDER BY created_at DESC LIMIT 3";
                $stmt = $db->query($query);
                $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach($properties as $index => $property) {
                    $animation_delay = ($index + 1) * 0.2;
                    echo '
                    <div class="col-md-4 animate-on-scroll" style="animation-delay: '.$animation_delay.'s">
                        <div class="card card-hover h-100 overflow-hidden">
                            <img src="'.$property['photo'].'" class="card-img-top property-img" alt="'.$property['titre'].'">
                            <div class="card-body">
                                <span class="badge bg-'.($property['type_id'] == 'vente' ? 'success' : 'info').' mb-2">'.ucfirst($property['type_id']).'</span>
                                <h5 class="card-title">'.$property['titre'].'</h5>
                                <p class="card-text text-muted">'.substr($property['description'], 0, 100).'...</p>
                                <ul class="list-inline">
                                    <li class="list-inline-item"><i class="fas fa-bed me-1"></i> '.$property['chambres'].' chambres</li>
                                    <li class="list-inline-item"><i class="fas fa-bath me-1"></i> '.$property['bathroom'].' sdb</li>
                                    <li class="list-inline-item"><i class="fas fa-ruler-combined me-1"></i> '.$property['superficie'].' m²</li>
                                </ul>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-primary">'.number_format($property['prix'], 0, ',', ' ').' FCFA</h5>
                                    <a href="biens.php#property-'.$property['id'].'" class="btn btn-sm btn-outline-primary">Voir plus</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } catch(PDOException $e) {
                echo '<div class="col-12 text-center text-danger">Erreur de chargement des propriétés</div>';
            }
            ?>
        </div>
        
        <div class="row mt-5 animate-on-scroll">
            <div class="col-12 text-center">
                <a href="biens.php" class="btn btn-primary px-4">
                    <i class="fas fa-eye me-2"></i>Voir toutes les propriétés
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Nos agents -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5 animate-on-scroll">
            <div class="col-12 text-center">
                <h2 class="fw-bold">Rencontrez nos agents</h2>
                <p class="text-muted">Nos experts sont à votre service</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php
            try {
                $query = "SELECT * FROM agents ORDER BY RAND() LIMIT 3";
                $stmt = $db->query($query);
                $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach($agents as $index => $agent) {
                    $animation_delay = ($index + 1) * 0.2;
                    echo '
                    <div class="col-md-4 animate-on-scroll" style="animation-delay: '.$animation_delay.'s">
                        <div class="card card-hover text-center h-100 border-0">
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
                        </div>
                    </div>';
                }
            } catch(PDOException $e) {
                echo '<div class="col-12 text-center text-danger">Erreur de chargement des agents</div>';
            }
            ?>
        </div>
        
        <div class="row mt-5 animate-on-scroll">
            <div class="col-12 text-center">
                <a href="agents.php" class="btn btn-primary px-4">
                    <i class="fas fa-users me-2"></i>Voir tous nos agents
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Témoignages -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5 animate-on-scroll">
            <div class="col-12 text-center">
                <h2 class="fw-bold">Ce que disent nos clients</h2>
                <p class="text-muted">Découvrez les témoignages de nos clients satisfaits</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4 animate-on-scroll">
                <div class="card card-hover h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <img src="img/mo.jpg" class="rounded-circle me-3" width="60" height="60" alt="Client" style="border:2px solid var(--color-accent);">
                            <div>
                                <h5 class="mb-0" style="color:var(--color-accent);">Mo Lopez</h5>
                                <p class="text-muted mb-0">Acheté en 2023</p>
                            </div>
                        </div>
                        <p class="card-text">"L'équipe a été incroyablement professionnelle et m'a aidé à trouver la maison parfaite pour ma famille. Je recommande vivement cette agence!"</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 animate-on-scroll" style="animation-delay: 0.2s">
                <div class="card card-hover h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <img src="img/mh.jpg" class="rounded-circle me-3" width="60" height="60" alt="Client" style="border:2px solid var(--color-accent);">
                            <div>
                                <h5 class="mb-0" style="color:var(--color-accent);">Mouhamed Rassoul</h5>
                                <p class="text-muted mb-0">Loué en 2022</p>
                            </div>
                        </div>
                        <p class="card-text">"Service exceptionnel! Mon agent a compris exactement ce que je recherchais et m'a présenté plusieurs options qui correspondaient à mes critères."</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 animate-on-scroll" style="animation-delay: 0.4s">
                <div class="card card-hover h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <img src="img/zs.jpg" class="rounded-circle me-3" width="60" height="60" alt="Client" style="border:2px solid var(--color-accent);">
                            <div>
                                <h5 class="mb-0" style="color:var(--color-accent);">Canabasse</h5>
                                <p class="text-muted mb-0">Acheté en 2023</p>
                            </div>
                        </div>
                        <p class="card-text">"J'ai acheté une villa en moins d'une semaine grâce à leur réseau et leur marketing efficace. Très impressionnée par leur professionnalisme."</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

