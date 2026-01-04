<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita Desa Kaliboja | Website Resmi</title>
    <meta name="description"
        content="Informasi terbaru dan kegiatan terkini di Desa Kaliboja - berita, pengumuman, dan update kegiatan desa.">
    <link rel="icon" href="/img/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <style>
    :root {
        --primary: #4A6C6F;
        --primary-dark: #3a5659;
        --secondary: #F4EAD5;
        --accent: #D6A25B;
        --accent-light: #e6b877;
        --light: #F9F7F3;
        --text-dark: #333;
        --text-light: #6c757d;
        --shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        --shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.15);
        --border-radius: 0.75rem;
    }

    body {
        background: var(--light);
        color: var(--text-dark);
        font-family: 'Inter', sans-serif;
        line-height: 1.6;
        scroll-behavior: smooth;
    }

    h1, h2, h3, h4, h5, h6,
    .navbar-brand {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
    }

    .navbar {
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        padding: 0.8rem 0;
        transition: all 0.3s ease;
    }

    .navbar.scrolled {
        padding: 0.5rem 0;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
    }

    .nav-link {
        font-weight: 500;
        position: relative;
        padding: 0.5rem 0.8rem !important;
        margin: 0 0.2rem;
        transition: all 0.3s ease;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: var(--accent);
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .nav-link:hover::before,
    .nav-link.active::before {
        width: 70%;
    }

    .btn-primary {
        background: var(--primary);
        border-color: var(--primary);
        padding: 0.6rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }

    .btn-outline-primary {
        color: var(--primary);
        border-color: var(--primary);
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: var(--primary);
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .section-title {
        font-weight: 700;
        color: var(--primary);
        text-align: center;
        margin-bottom: 2.4rem;
        position: relative;
    }

    .section-title::after {
        content: '';
        display: block;
        width: 84px;
        height: 4px;
        background: var(--accent);
        margin: 0.8rem auto 0;
        border-radius: 2px;
    }

    .hero-section {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        color: white;
        position: relative;
        overflow: hidden;
        padding: 7rem 0 4rem;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h20L0 20z' fill='%23ffffff' fill-opacity='0.05'/%3E%3C/svg%3E");
        opacity: 0.1;
    }

    /* News Card Styles */
    .news-card {
        border: none;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        height: 100%;
    }

    .news-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }

    .news-image-container {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .news-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .news-card:hover .news-image {
        transform: scale(1.05);
    }

    .news-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--accent);
        color: white;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 2;
    }

    .news-date {
        position: absolute;
        bottom: 15px;
        left: 15px;
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary);
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 2;
    }

    .no-image {
        height: 250px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        color: #6c757d;
    }

    footer {
        background: linear-gradient(to right, #0a1920, #111);
        color: #bbb;
        position: relative;
        overflow: hidden;
    }

    footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(to right, var(--accent), var(--primary));
    }

    .wa-float {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 1030;
        animation: pulse 2s infinite, float 3s ease-in-out infinite;
    }

    .to-top {
        position: fixed;
        right: 20px;
        bottom: 80px;
        z-index: 1030;
        display: none;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        to { transform: scale(1); }
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        to { transform: translateY(0px); }
    }

    .breadcrumb {
        background-color: transparent;
        padding: 0.75rem 0;
        margin-bottom: 0;
    }

    .breadcrumb-item a {
        color: var(--primary);
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .hero-section {
            padding: 6rem 0 3rem;
        }
        
        .section-title {
            font-size: 1.8rem;
            margin-bottom: 2rem;
        }
        
        .news-image-container {
            height: 220px;
        }
        
        .no-image {
            height: 220px;
        }
    }

    @media (max-width: 576px) {
        .section-title {
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <!-- Announcement Bar -->
    <div class="announcement-bar text-center" style="background-color: var(--primary); color: white; padding: 8px 0;">
        <div class="container">
            <p class="mb-0"><i class="fas fa-bullhorn me-2"></i> Selamat datang di website resmi Desa Kaliboja! <a
                    href="/berita" class="text-white fw-bold ms-2">Lihat berita terbaru &rarr;</a></p>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <img src="/img/logo.png" class="me-2" alt="Logo Desa Kaliboja" width="40" height="40">Desa Kaliboja
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <section class="hero-section">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-down">Berita Desa Kaliboja</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="200">
                        Informasi terbaru dan kegiatan terkini di Desa Kaliboja
                    </p>
                    <div data-aos="fade-up" data-aos-delay="300">
                        <a href="#news" class="btn btn-light btn-lg me-2">
                            <i class="fas fa-newspaper me-2"></i>Jelajahi Berita
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="bg-light py-3">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Berita Desa</li>
            </ol>
        </div>
    </nav>

    <!-- News Section -->
    <section id="news" class="py-5">
        <div class="container">
            <h2 class="section-title" data-aos="fade-in">Berita Terbaru</h2>
            
            <?php if (!empty($news)) : ?>
            <div class="row g-4">
                <?php foreach ($news as $n) : 
                    // Cek gambar berita
                    $imagePath = '';
                    $imageExists = false;
                    
                    if(!empty($n['images'])) {
                        $imagePath = 'uploads/news/' . esc($n['images'][0]['image_filename']);
                        $imageExists = file_exists(ROOTPATH . 'public/' . $imagePath);
                    }
                ?>
                <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="fade-up">
                    <div class="card news-card">
                        <div class="news-image-container">
                            <?php if($imageExists) : ?>
                                <img src="<?= base_url($imagePath); ?>" class="news-image" alt="<?= esc($n['title']); ?>">
                            <?php else : ?>
                                <div class="no-image">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p>Gambar tidak tersedia</p>
                                </div>
                            <?php endif; ?>
                            
                            <span class="news-badge">Berita</span>
                            <span class="news-date"><?= date('d M Y', strtotime($n['created_at'])); ?></span>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= esc($n['title']); ?></h5>
                            
                            <?php if(!empty($n['content'])) : ?>
                            <p class="card-text text-muted flex-grow-1">
                                <?= esc(word_limiter(strip_tags($n['content']), 15)); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>Admin Desa
                                </small>
                                <a href="/berita/<?= esc($n['id']); ?>" 
                                   class="btn btn-primary btn-sm">
                                    Baca <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <div class="text-center py-5" data-aos="fade-in">
                <i class="fas fa-newspaper fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Belum ada berita</h4>
                <p class="text-muted mb-4">Silakan kunjungi kembali nanti untuk melihat berita terbaru dari Desa Kaliboja</p>
                <a href="/" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Kembali ke Home
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-0">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4">Desa Kaliboja</h5>
                    <p class="mb-4">Portal resmi desa untuk transparansi informasi, promosi potensi, dan
                        pelayanan digital kepada masyarakat.</p>
                    <div class="d-flex">
                        <a href="https://www.facebook.com/share/17fpGKh173/" target="_blank"
                            class="text-white me-3 hover-lift">
                            <div class="social-icon">
                                <i class="fa-brands fa-facebook-f"></i>
                            </div>
                        </a>
                        <a href="https://www.instagram.com/desa_kaliboja?igsh=MTB2enB2N3I4dGp2OA==" target="_blank"
                            class="text-white me-3 hover-lift">
                            <div class="social-icon">
                                <i class="fa-brands fa-instagram"></i>
                            </div>
                        </a>
                        <a href="https://x.com/desa_kaliboja?t=taTDsUWdhSoPbIiqADFfyQ&s=09&fbclid=PAdGRjcAMvYX1leHRuA2FlbQIxMQABp9AdVHP8awNqQGOky0UFUiiEt9za1hiL0Wldzmpg5X_LPj7CyczURUw5Jk2f_aem_r-xoS5uVycPxEOxfhEjr2A"
                            target="_blank" class="text-white me-3 hover-lift">
                            <div class="social-icon">
                                <i class="fa-brands fa-x-twitter"></i>
                            </div>
                        </a>
                        <a href="https://www.tiktok.com/@desa_kaliboja?fbclid=PAdGRjcAMvYeNleHRuA2FlbQIxMQABp-jUXBxjp43fgoeGN6x01EfX3g1Nj10GpaTEukdsoluv5Zt4yNimvhdrphwe_aem_5lvWmF8h8HUWv1miYT-y0A"
                            target="_blank" class="text-white hover-lift">
                            <div class="social-icon">
                                <i class="fa-brands fa-tiktok"></i>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4">Kontak</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><i class="fa-solid fa-location-dot me-2"></i>Desa Kaliboja,
                            Kec. Paninggaran,
                            Kabupaten Pekalongan, Jawa Tengah</li>
                        <li class="mb-2"><i class="fa-solid fa-envelope me-2"></i>kalibojadesa@gmail.com
                        </li>
                        <li><i class="fa-solid fa-clock me-2"></i>Senin - Jumat: 08:00 - 16:00</li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h5 class="text-white mb-4">Tautan Cepat</h5>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-unstyled small">
                                <li class="mb-2"><a href="/#statistik"
                                        class="text-white text-decoration-none">Statistik</a></li>
                                <li class="mb-2"><a href="/#profil"
                                        class="text-white text-decoration-none">Profil</a></li>
                                <li class="mb-2"><a href="/#berita"
                                        class="text-white text-decoration-none">Berita</a></li>
                                <li class="mb-2"><a href="/#produk"
                                        class="text-white text-decoration-none">Produk</a></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled small">
                                <li class="mb-2"><a href="/#potensi"
                                        class="text-white text-decoration-none">Potensi</a></li>
                                <li class="mb-2"><a href="/#wisata"
                                        class="text-white text-decoration-none">Wisata</a></li>
                                <li class="mb-2"><a href="/#galeri"
                                        class="text-white text-decoration-none">Galeri</a></li>
                                <li class="mb-2"><a href="/#testimoni"
                                        class="text-white text-decoration-none">Testimoni</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center small">
                <div class="mb-2">&copy; 2023 Pemerintah Desa Kaliboja. All Rights Reserved.
                </div>
                <div>Dikembangkan oleh <a href="#" class="text-white text-decoration-none">Tim IT KKN 4
                        Kelompok 7
                        Desa Kaliboja</a></div>
            </div>
        </div>
    </footer>

    <!-- Floating WA & To Top -->
    <a class="wa-float btn btn-success rounded-circle shadow" href="https://wa.me/628123456789" target="_blank"
        title="Hubungi via WhatsApp">
        <i class="fa-brands fa-whatsapp fa-lg"></i>
    </a>

    <button id="toTop" class="to-top btn btn-primary rounded-circle shadow" title="Kembali ke atas">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi AOS
        AOS.init({
            once: true,
            duration: 1000,
            offset: 100,
            easing: 'ease-out-back'
        });

        // Scroll effects & to top
        const toTop = document.getElementById('toTop');
        const navbar = document.querySelector('.navbar');

        if (toTop && navbar) {
            window.addEventListener('scroll', () => {
                toTop.style.display = window.scrollY > 400 ? 'block' : 'none';
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            toTop.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Handle image errors
        document.querySelectorAll('.news-image').forEach(img => {
            img.addEventListener('error', function() {
                this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgZmlsbD0iIzk5OSI+R2FtYmFyIHRpZGFrIHRlcnNlZGlhPC90ZXh0Pjwvc3ZnPg==';
            });
        });

        // Smooth scroll untuk navigasi
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#' || targetId === '#!') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    const navbarHeight = document.querySelector('.navbar').offsetHeight;
                    const targetPosition = targetElement.getBoundingClientRect().top + window
                        .pageYOffset - navbarHeight;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
    </script>
</body>

</html>