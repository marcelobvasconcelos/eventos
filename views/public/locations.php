<?php
$title = 'Ficha Técnica dos Locais';
ob_start();
?>

<div class="container mt-4">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-white">Nossos Espaços</h1>
        <p class="lead text-white-50">Conheça os detalhes técnicos de cada ambiente disponível para seus eventos</p>
    </div>

    <div class="row g-4">
        <script>const locationImagesMap = {};</script>
        <?php foreach ($locations as $location): ?>
            <script>
                locationImagesMap[<?php echo $location['id']; ?>] = <?php 
                    $imgPaths = array_map(function($img) { return $img['image_path']; }, $location['images'] ?? []);
                    echo json_encode(array_values($imgPaths)); 
                ?>;
            </script>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 rounded-4 hover-shadow transition-all overflow-hidden">
                    <?php if (!empty($location['images'])): ?>
                        <div id="carouselLoc<?php echo $location['id']; ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($location['images'] as $idx => $img): ?>
                                    <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" 
                                             class="d-block w-100" 
                                             style="height: 250px; object-fit: cover; cursor: pointer;" 
                                             onclick="openLightbox(<?php echo $location['id']; ?>, <?php echo $idx; ?>)"
                                             alt="Imagem do Local">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($location['images']) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselLoc<?php echo $location['id']; ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true" style="background-color: rgba(0,0,0,0.3); border-radius: 50%;"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselLoc<?php echo $location['id']; ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true" style="background-color: rgba(0,0,0,0.3); border-radius: 50%;"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                <i class="fas fa-map-marker-alt fa-lg"></i>
                            </div>
                            <h3 class="card-title h4 fw-bold text-dark mb-0"><?php echo htmlspecialchars($location['name']); ?></h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-light text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-2">
                                <i class="fas fa-users me-2"></i>Capacidade: <strong><?php echo htmlspecialchars($location['capacity']); ?> pessoas</strong>
                            </span>
                        </div>
                        
                        <p class="card-text text-muted">
                            <?php echo nl2br(htmlspecialchars($location['description'])); ?>
                        </p>
                    </div>
                    <!--
                    <div class="card-footer bg-white border-top-0 pb-4">
                        <a href="/eventos/request/form?location_id=<?php echo $location['id']; ?>" class="btn btn-outline-primary rounded-pill w-100">
                            Selecionar este local
                        </a>
                    </div>
                    -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-5 mb-5">
        <a href="/eventos/request/form" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
            <i class="fas fa-calendar-plus me-2"></i>Solicitar Evento Agora
        </a>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-transparent border-0 shadow-none">
      <div class="modal-body p-0 text-center position-relative">
          <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3 bg-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8;"></button>
          
          <div class="position-relative d-inline-block">
              <img id="lightboxImage" src="" class="img-fluid rounded shadow-lg" style="max-height: 90vh;" alt="Zoom">
              
              <!-- Navigation Buttons -->
              <button class="btn btn-dark position-absolute top-50 start-0 translate-middle-y ms-2 rounded-circle shadow nav-btn" id="prevBtn" onclick="changeImage(-1)" style="width: 40px; height: 40px; opacity: 0.7;">
                  <i class="fas fa-chevron-left"></i>
              </button>
              <button class="btn btn-dark position-absolute top-50 end-0 translate-middle-y me-2 rounded-circle shadow nav-btn" id="nextBtn" onclick="changeImage(1)" style="width: 40px; height: 40px; opacity: 0.7;">
                  <i class="fas fa-chevron-right"></i>
              </button>
          </div>
      </div>
    </div>
  </div>
</div>

<script>
let currentLocId = null;
let currentImgIndex = 0;

function openLightbox(locId, index) {
    currentLocId = locId;
    currentImgIndex = index;
    updateLightboxImage();
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}

function changeImage(direction) {
    if (!currentLocId || !locationImagesMap[currentLocId]) return;
    
    const images = locationImagesMap[currentLocId];
    currentImgIndex += direction;
    
    if (currentImgIndex < 0) {
        currentImgIndex = images.length - 1;
    } else if (currentImgIndex >= images.length) {
        currentImgIndex = 0;
    }
    updateLightboxImage();
}

function updateLightboxImage() {
    if (!currentLocId || !locationImagesMap[currentLocId]) return;
    
    const images = locationImagesMap[currentLocId];
    if (images.length === 0) return;
    
    document.getElementById('lightboxImage').src = images[currentImgIndex];
    
    // Hide nav buttons if only 1 image
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    if (images.length <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    } else {
        prevBtn.style.display = 'block';
        nextBtn.style.display = 'block';
    }
}
</script>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
