<?= $this->extend('layout/main_layout'); ?>
<?= $this->section('content'); ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light">
    <div class="container">
        <ol class="breadcrumb py-2 mb-0">
            <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('berita'); ?>">Berita</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail Berita</li>
        </ol>
    </div>
</nav>

<!-- Detail Berita Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <article>
                    <header class="mb-4">
                        <h1 class="fw-bold mb-3"><?= esc($news['title']) ?></h1>
                        <div class="d-flex align-items-center text-muted mb-3">
                            <i class="fa-regular fa-clock me-2"></i>
                            <span><?= date('d F Y', strtotime($news['created_at'])) ?></span>
                        </div>
                        
                        <?php if(!empty($images)): 
                            $imagePath = 'uploads/news/' . esc($images[0]['image_filename']);
                            $imageExists = file_exists(ROOTPATH . 'public/' . $imagePath);
                        ?>
                        <img src="<?= $imageExists ? base_url($imagePath) : 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' ?>" 
                             class="img-fluid rounded w-100" 
                             alt="<?= esc($news['title']) ?>"
                             style="max-height: 400px; object-fit: cover;"
                             onerror="this.src='https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                        <?php endif; ?>
                    </header>
                    
                    <div class="news-content">
                        <?= $news['content'] ?>
                    </div>
                    
                    <?php if(count($images) > 1): ?>
                    <div class="mt-5">
                        <h5 class="mb-3">Galeri Foto</h5>
                        <div class="row g-3">
                            <?php for($i = 1; $i < count($images); $i++): 
                                $imagePath = 'uploads/news/' . esc($images[$i]['image_filename']);
                                $imageExists = file_exists(ROOTPATH . 'public/' . $imagePath);
                            ?>
                            <div class="col-md-4">
                                <img src="<?= $imageExists ? base_url($imagePath) : 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80' ?>" 
                                     class="img-fluid rounded" 
                                     alt="Galeri berita"
                                     style="height: 150px; object-fit: cover;"
                                     onerror="this.src='https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'">
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </article>
                
                <div class="mt-5 pt-4 border-top">
                    <a href="<?= base_url('berita') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Berita
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>