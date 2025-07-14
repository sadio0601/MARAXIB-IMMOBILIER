<?php 
require_once 'config.php';



// Récupérer les paramètres de filtrage
$agent_id = isset($_GET['agent']) ? intval($_GET['agent']) : 0;
$ville = isset($_GET['ville']) ? trim($_GET['ville']) : '';
$agent_name = '';

if($agent_id > 0) {
    try {
        $query = "SELECT CONCAT(prenom, ' ', nom) AS nom_complet FROM agents WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$agent_id]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($agent) {
            $agent_name = $agent['nom_complet'];
        }
    } catch(PDOException $e) {
        // Ne rien faire, on continue sans le nom de l'agent
    }
}
?>

<!-- Hero Section -->
<section class="hero-section-small d-flex align-items-center justify-content-center text-center text-white" style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/IMG_7585'); background-size: cover; background-position: center; height: 120vh;">
    <div class="container">
        <div class="row">
            <div class="col-12 animate__animated animate__fadeInDown">
                <h1 class="display-4 fw-bold mb-4">Nos Biens Immobiliers</h1>
                <p class="lead mb-5">Découvrez notre sélection exclusive</p>

                <!-- Barre de recherche par ville - Version très visible -->
                <div class="row ">
                    <div class="col-lg-8 mx-auto">
                        <div class="p-4 rounded-3 shadow-sm" style="background-color: rgba(255, 255, 255, 0.2); margin-top: 15%;">
                            <!-- <h5 class="mb-3 text-center"><i class="fas fa-map-marked-alt text-primary me-2"></i>Recherche par localisation</h5> -->
                            <form method="GET" class="row g-3 align-items-center">
                                <div class="col-md-9">
                                    <div class="input-group border rounded-3 overflow-hidden">
                                        <span class="input-group-text bg-white border-0">
                                            <i class="fas fa-city text-primary"></i>
                                        </span>
                                        <input type="text" 
                                            name="ville" 
                                            class="form-control border-0 py-3" 
                                            placeholder="Entrez une ville (ex: Pikine, Mariste, Almadies...)" 
                                            value="<?php echo htmlspecialchars($ville); ?>"
                                            id="villeSearch">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100 py-3">
                                        <i class="fas fa-search me-2"></i>Aller
                                    </button>
                                </div>
                                <?php if($agent_id > 0): ?>
                                    <input type="hidden" name="agent" value="<?php echo $agent_id; ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if($agent_name): ?>
                    <div class="alert alert-info d-inline-block animate__animated animate__fadeInUp">
                        <i class="fas fa-user-tie me-2"></i>Biens gérés par <?php echo htmlspecialchars($agent_name); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Liste des biens -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5 animate-on-scroll">
            <div class="col-12 text-center">
                <h2 class="fw-bold">Notre catalogue</h2>
                <p class="text-muted">Trouvez la propriété qui vous correspond</p>
            </div>
        </div>
        
        
        
        <div class="row g-4" id="propertiesContainer">
            <?php
            try {
                $query = "SELECT m.*, CONCAT(a.prenom, ' ', a.nom) AS agent_nom 
                        FROM biens m 
                        LEFT JOIN agents a ON m.agent_id = a.id";

                $params = [];
                $where = [];
                
                if($agent_id > 0) {
                    $where[] = "m.agent_id = ?";
                    $params[] = $agent_id;
                }
                
                if(!empty($ville)) {
                    $where[] = "m.ville LIKE ?";
                    $params[] = "%$ville%";
                }
                
                if(!empty($where)) {
                    $query .= " WHERE " . implode(" AND ", $where);
                }
                
                $query .= " ORDER BY m.created_at DESC";
                
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if(count($properties) > 0) {
                    foreach($properties as $index => $property) {
                        $animation_delay = ($index % 4) * 0.2;
                        echo '
                        <div class="col-md-6 col-lg-4 animate-on-scroll property-item" 
                            data-type="'.$property['type_id'].'"
                            data-chambres="'.$property['chambres'].'"
                            data-prix="'.$property['prix'].'"
                            data-ville="'.strtolower($property['ville']).'"
                            style="animation-delay: '.$animation_delay.'s">
                            <div class="card card-hover h-100 overflow-hidden" id="property-'.$property['id'].'">
                                <a href="details.php?id='.$property['id'].'" class="stretched-link"></a>
                                <img src="'.$property['photo'].'" class="card-img-top property-img" alt="'.$property['titre'].'">
                                <div class="card-body">
                                    <span class="badge bg-'.($property['type_id'] == 'vente' ? 'success' : 'info').' mb-2">'.ucfirst($property['type_id']).'</span>
                                    <h5 class="card-title">'.htmlspecialchars($property['titre']).'</h5>
                                    <p class="card-text text-muted">'.htmlspecialchars(substr($property['description'], 0, 100)).'...</p>
                                    <ul class="list-inline">
                                        <li class="list-inline-item"><i class="fas fa-bed me-1"></i> '.$property['chambres'].' chambres</li>
                                        <li class="list-inline-item"><i class="fas fa-bath me-1"></i> '.$property['bathroom'].' sdb</li>
                                        <li class="list-inline-item"><i class="fas fa-ruler-combined me-1"></i> '.$property['superficie'].' m²</li>
                                    </ul>
                                    <div class="d-flex align-items-center mt-3">
                                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                        <small class="text-muted">'.htmlspecialchars($property['ville']).'</small>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 text-primary">'.number_format($property['prix'], 0, ',', ' ').' FCFA</h5>
                                        <small class="text-muted">'.htmlspecialchars($property['agent_nom'] ?? 'Non attribué').'</small>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div class="col-12 text-center text-muted py-5">
                            <i class="fas fa-home fa-3x mb-3 text-muted"></i>
                            <h4>Aucun bien ne correspond à votre recherche</h4>
                            <p class="mt-3">Essayez avec un autre nom de ville ou <a href="biens.php" class="text-primary">réinitialisez les filtres</a></p>
                          </div>';
                }
            } catch(PDOException $e) {
                echo '<div class="col-12 text-center text-danger py-5">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h4>Erreur technique</h4>
                        <p>Nous ne pouvons pas afficher les biens pour le moment</p>
                      </div>';
            }
            ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtrage en temps réel
    const villeSearch = document.getElementById('villeSearch');
    if(villeSearch) {
        villeSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const properties = document.querySelectorAll('.property-item');
            
            properties.forEach(property => {
                const ville = property.dataset.ville;
                if(ville.includes(searchTerm) || searchTerm === '') {
                    property.style.display = "block";
                } else {
                    property.style.display = "none";
                }
            });
        });
    }
    
    // Animation au scroll
    const animateOnScroll = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        animateOnScroll.observe(el);
    });
});
</script>

