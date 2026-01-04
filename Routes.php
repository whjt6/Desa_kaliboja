<?php
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// =====================================================================
// RUTE PUBLIK (HALAMAN DEPAN)
// =====================================================================

// Rute Halaman Utama
$routes->get('/', 'Home::index');

// Rute untuk statistik dan profil
$routes->get('statistik', 'Home::statistik');  // HAPUS DUPLIKAT
$routes->get('profil', 'Profil::index');
$routes->get('compatibility', 'Home::compatibility'); // ARAHKAN KE HOME

// Rute untuk Berita (PUBLIK)
$routes->get('berita', 'Berita::index');
$routes->get('berita/(:num)', 'Berita::detail/$1');

// Rute untuk Wisata (PUBLIK)
$routes->get('wisata', 'WisataController::landing');
$routes->get('wisata/(:num)', 'WisataController::detail/$1');

// Rute untuk Produk Publik
$routes->get('produk', 'Products::publicIndex');
$routes->get('produk/(:num)', 'Products::show/$1');

// Alias untuk produk dalam bahasa Inggris
$routes->get('products', 'Products::publicIndex');
$routes->get('products/(:num)', 'Products::show/$1');

// Rute untuk Gallery (PUBLIK)
$routes->get('gallery', 'Gallery::index');
$routes->get('gallery/public', 'Gallery::publicIndex');
$routes->get('gallery/detail/(:num)', 'Gallery::detail/$1');

// Rute untuk Aparatur Desa (PUBLIK)
$routes->get('aparatur', 'ApparatusController::publicIndex');

$routes->get('kesehatan', 'KesehatanController::index');
$routes->get('posyandu', 'KesehatanController::posyandu');
$routes->get('posbindu', 'KesehatanController::posbindu');
$routes->get('kesehatan/dusun/(:segment)', 'KesehatanController::dusun/$1');

// =====================================================================
// RUTE JDIH (PUBLIK)
// =====================================================================

$routes->group('jdih', static function ($routes) {
    $routes->get('/', 'JdihFrontController::index');
    $routes->get('kategori/(:num)', 'JdihFrontController::kategori/$1');
    $routes->get('detail/(:num)', 'JdihFrontController::detail/$1');
    $routes->get('search', 'JdihFrontController::search');
    $routes->get('download/(:num)', 'JdihFrontController::download/$1');
    $routes->get('tahun/(:num)', 'JdihFrontController::tahun/$1');
});

// =====================================================================
// RUTE RKP DESA (PUBLIK) - ARAHKAN KE RkpController
// =====================================================================

$routes->group('rkp', static function ($routes) {
    $routes->get('/', 'RkpController::index');  // GANTI: RkpFrontController -> RkpController
    $routes->get('detail/(:num)', 'RkpController::detail/$1');
    $routes->get('tahun/(:num)', 'RkpController::tahun/$1');
});

$routes->group('koperasi', function($routes) {
    // Homepage Koperasi
    $routes->get('/', 'KoperasiController::index');
    
    // Profil
    $routes->get('profil', 'KoperasiController::profil');
    
    // Unit Usaha
    $routes->get('unit-usaha', 'KoperasiController::unitUsaha');
    $routes->get('unit-usaha/detail/(:num)', 'KoperasiController::detailUnit/$1');
    $routes->get('detail/(:num)', 'KoperasiController::detailUnit/$1'); // Alias
    
    // Keanggotaan
    $routes->get('keanggotaan', 'KoperasiController::keanggotaan');
    $routes->get('form-pendaftaran', 'KoperasiController::formPendaftaran');
    $routes->post('form-pendaftaran', 'KoperasiController::submitPendaftaran');
    
    // Laporan
    $routes->get('laporan', 'KoperasiController::laporan');
    $routes->get('download/(:num)', 'KoperasiController::downloadLaporan/$1');
    
    // Berita
    $routes->get('berita', 'KoperasiController::berita');
    $routes->get('berita/(:segment)', 'KoperasiController::detailBerita/$1');
    
    // Kontak
    $routes->get('kontak', 'KoperasiController::kontak');
});

// =====================================================================
// RUTE AUTENTIKASI
// =====================================================================

$routes->get('login', 'Auth::index', ['as' => 'login']);
$routes->get('auth/login', 'Auth::index');
$routes->post('auth/login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// =====================================================================
// AREA ADMIN (BUTUH LOGIN)
// =====================================================================

$routes->group('dashboard', ['filter' => 'auth'], static function ($routes) {
    // Dashboard utama
    $routes->get('/', 'Dashboard::index');
    
    // =================================================================
    // MANAJEMEN BERITA (ADMIN)
    // =================================================================
    $routes->group('news', static function ($routes) {
        $routes->get('/', 'News::index');
        $routes->get('show/(:num)', 'News::show/$1');
        $routes->get('create', 'News::create');
        $routes->post('store', 'News::store');
        $routes->get('edit/(:num)', 'News::edit/$1');
        $routes->post('update/(:num)', 'News::update/$1');
        $routes->post('delete/(:num)', 'News::delete/$1');
    });

    // =================================================================
    // MANAJEMEN GALLERY (ADMIN)
    // =================================================================
    $routes->group('gallery', static function($routes) {
        $routes->get('/', 'Gallery::adminIndex');
        $routes->get('create', 'Gallery::create');
        $routes->post('store', 'Gallery::store');
        $routes->get('edit/(:num)', 'Gallery::edit/$1');
        $routes->post('update/(:num)', 'Gallery::update/$1');
        $routes->get('delete/(:num)', 'Gallery::delete/$1');
    });

    // =================================================================
    // MANAJEMEN APARATUR DESA (ADMIN)
    // =================================================================
    $routes->group('apparatus', static function ($routes) {
        $routes->get('/', 'ApparatusController::index');
        $routes->get('create', 'ApparatusController::create');
        $routes->post('store', 'ApparatusController::store');
        $routes->get('edit/(:num)', 'ApparatusController::edit/$1');
        $routes->post('update/(:num)', 'ApparatusController::update/$1');
        $routes->post('delete/(:num)', 'ApparatusController::delete/$1');
    });
    
    // =================================================================
    // MANAJEMEN POSYANDU (ADMIN) - PERBAIKI RUTE INI
    // =================================================================
    $routes->group('posyandu', static function ($routes) {
        $routes->get('/', 'PosyanduController::index');
        $routes->get('show/(:num)', 'PosyanduController::show/$1'); // TAMBAHKAN INI
        $routes->get('create', 'PosyanduController::create');
        $routes->post('store', 'PosyanduController::store');
        $routes->get('edit/(:num)', 'PosyanduController::edit/$1');
        $routes->post('update/(:num)', 'PosyanduController::update/$1');
        $routes->post('delete/(:num)', 'PosyanduController::delete/$1');
        $routes->get('statistik', 'PosyanduController::statistik');
    });
    
    // =================================================================
    // MANAJEMEN POSBINDU (ADMIN) - PERBAIKI RUTE INI
    // =================================================================
    $routes->group('posbindu', static function ($routes) {
        $routes->get('/', 'PosbinduController::index');
        $routes->get('show/(:num)', 'PosbinduController::show/$1'); // TAMBAHKAN INI
        $routes->get('create', 'PosbinduController::create');
        $routes->post('store', 'PosbinduController::store');
        $routes->get('edit/(:num)', 'PosbinduController::edit/$1');
        $routes->post('update/(:num)', 'PosbinduController::update/$1');
        $routes->post('delete/(:num)', 'PosbinduController::delete/$1');
        $routes->get('statistik', 'PosbinduController::statistik');
        
    });

    // =================================================================
    // MANAJEMEN PRODUK (ADMIN)
    // =================================================================
    $routes->group('products', static function ($routes) {
        $routes->get('/', 'Products::index');
        $routes->get('create', 'Products::create');
        $routes->post('store', 'Products::store');
        $routes->get('edit/(:num)', 'Products::edit/$1');
        $routes->post('update/(:num)', 'Products::update/$1');
        $routes->post('delete/(:num)', 'Products::delete/$1');
    });

    // =================================================================
    // MANAJEMEN PENDUDUK (ADMIN)
    // =================================================================
    $routes->group('population', static function ($routes) {
        $routes->get('/', 'PopulationController::index');
        $routes->get('create', 'PopulationController::create');
        $routes->post('store', 'PopulationController::store');
        $routes->get('edit/(:num)', 'PopulationController::edit/$1');
        $routes->post('update/(:num)', 'PopulationController::update/$1');
        $routes->post('delete/(:num)', 'PopulationController::delete/$1');
        $routes->get('stats', 'PopulationController::getStats');
    });

    // =================================================================
    // MANAJEMEN SURAT (ADMIN)
    // =================================================================
    $routes->group('surat', static function ($routes) {
        $routes->get('/', 'SuratController::index');
        $routes->get('create', 'SuratController::create');
        $routes->post('store', 'SuratController::store');
        $routes->get('edit/(:num)', 'SuratController::edit/$1');
        $routes->post('update/(:num)', 'SuratController::update/$1');
        $routes->post('delete/(:num)', 'SuratController::delete/$1');
        $routes->get('get-form-fields/(:num)', 'SuratController::getFormFields/$1');
        $routes->post('generate', 'SuratController::generate');
        $routes->get('arsip', 'SuratController::arsip');
        $routes->post('arsip/delete/(:num)', 'SuratController::deleteArsip/$1');
        $routes->get('download/(:num)', 'SuratController::downloadArsip/$1');
    });

    
   
    // =================================================================
    // JDIH (ADMIN)
    // =================================================================
    $routes->group('jdih', static function ($routes) {
        // Dashboard JDIH
        $routes->get('/', 'JdihController::index');
        
        // Manajemen Kategori
        $routes->group('kategori', static function ($routes) {
            $routes->get('/', 'JdihController::kategori');
            $routes->post('store', 'JdihController::storeKategori');
            $routes->post('update/(:num)', 'JdihController::updateKategori/$1');
            $routes->post('delete/(:num)', 'JdihController::deleteKategori/$1');
        });

        // Manajemen Peraturan
        $routes->group('peraturan', static function ($routes) {
            $routes->get('/', 'JdihController::peraturan');
            $routes->get('create', 'JdihController::createPeraturan');
            $routes->post('store', 'JdihController::storePeraturan');
            $routes->get('edit/(:num)', 'JdihController::editPeraturan/$1');
            $routes->post('update/(:num)', 'JdihController::updatePeraturan/$1');
            $routes->post('delete/(:num)', 'JdihController::deletePeraturan/$1');
            $routes->get('detail/(:num)', 'JdihController::detailPeraturan/$1');
        });

        // Manajemen Riwayat
        $routes->group('riwayat', static function ($routes) {
            $routes->get('(:num)', 'JdihController::riwayat/$1');
            $routes->post('store', 'JdihController::storeRiwayat');
            $routes->post('delete/(:num)', 'JdihController::deleteRiwayat/$1');
        });

        // Laporan dan Statistik
        $routes->get('laporan', 'JdihController::laporan');
        $routes->get('statistik', 'JdihController::statistik');
    });

    // =================================================================
    // RKP DESA (ADMIN)
    // =================================================================
    $routes->group('rkp', static function ($routes) {
        $routes->get('/', 'RkpController::index');
        $routes->get('create', 'RkpController::create');
        $routes->post('store', 'RkpController::store');
        $routes->get('edit/(:num)', 'RkpController::edit/$1');
        $routes->post('update/(:num)', 'RkpController::update/$1');
        $routes->post('delete/(:num)', 'RkpController::delete/$1');
        $routes->get('laporan', 'RkpController::laporan');
        $routes->get('statistik', 'RkpController::statistik');
    });
    
    $routes->group('koperasi', static function ($routes) {
        // Dashboard Koperasi
        $routes->get('/', 'KoperasiAdminController::index');
        
        // Manajemen Anggota
        $routes->group('anggota', static function ($routes) {
            $routes->get('/', 'KoperasiAdminController::anggota');
            $routes->get('create', 'KoperasiAdminController::createAnggota');
            $routes->post('store', 'KoperasiAdminController::storeAnggota');
            $routes->get('edit/(:num)', 'KoperasiAdminController::editAnggota/$1');
            $routes->post('update/(:num)', 'KoperasiAdminController::updateAnggota/$1');
            $routes->post('delete/(:num)', 'KoperasiAdminController::deleteAnggota/$1');
            $routes->post('status/(:num)', 'KoperasiAdminController::updateStatus/$1');
            $routes->get('export', 'KoperasiAdminController::exportAnggota');
        });
        
        // Manajemen Unit Usaha
        $routes->group('unit-usaha', static function ($routes) {
            $routes->get('/', 'KoperasiAdminController::unitUsaha');
            $routes->get('create', 'KoperasiAdminController::createUnit');
            $routes->post('store', 'KoperasiAdminController::storeUnit');
            $routes->get('edit/(:num)', 'KoperasiAdminController::editUnit/$1');
            $routes->post('update/(:num)', 'KoperasiAdminController::updateUnit/$1');
            $routes->post('delete/(:num)', 'KoperasiAdminController::deleteUnit/$1');
            $routes->get('kategori', 'KoperasiAdminController::kategoriUnit');
            $routes->post('kategori/store', 'KoperasiAdminController::storeKategori');
            $routes->post('kategori/delete/(:num)', 'KoperasiAdminController::deleteKategori/$1');
        });
        
        // Manajemen Simpanan
        $routes->group('simpanan', static function ($routes) {
            $routes->get('/', 'KoperasiAdminController::simpanan');
            $routes->get('create', 'KoperasiAdminController::createSimpanan');
            $routes->post('store', 'KoperasiAdminController::storeSimpanan');
            $routes->get('detail/(:num)', 'KoperasiAdminController::detailSimpanan/$1');
            $routes->get('laporan', 'KoperasiAdminController::laporanSimpanan');
            $routes->get('export', 'KoperasiAdminController::exportSimpanan');
        });
        
        // Manajemen Laporan
        $routes->group('laporan', static function ($routes) {
            $routes->get('/', 'KoperasiAdminController::laporan');
            $routes->get('create', 'KoperasiAdminController::createLaporan');
            $routes->post('store', 'KoperasiAdminController::storeLaporan');
            $routes->get('edit/(:num)', 'KoperasiAdminController::editLaporan/$1');
            $routes->post('update/(:num)', 'KoperasiAdminController::updateLaporan/$1');
            $routes->post('delete/(:num)', 'KoperasiAdminController::deleteLaporan/$1');
            $routes->get('download/(:num)', 'KoperasiAdminController::downloadLaporan/$1');
        });
        
        // Manajemen Berita Koperasi
        $routes->group('berita', static function ($routes) {
            $routes->get('/', 'KoperasiAdminController::berita');
            $routes->get('create', 'KoperasiAdminController::createBerita');
            $routes->post('store', 'KoperasiAdminController::storeBerita');
            $routes->get('edit/(:num)', 'KoperasiAdminController::editBerita/$1');
            $routes->post('update/(:num)', 'KoperasiAdminController::updateBerita/$1');
            $routes->post('delete/(:num)', 'KoperasiAdminController::deleteBerita/$1');
        });
        
        // Pengaturan Koperasi
        $routes->group('pengaturan', static function ($routes) {
            $routes->get('/', 'KoperasiAdminController::pengaturan');
            $routes->post('update', 'KoperasiAdminController::updatePengaturan');
            $routes->get('slider', 'KoperasiAdminController::slider');
            $routes->post('slider/store', 'KoperasiAdminController::storeSlider');
            $routes->post('slider/delete/(:num)', 'KoperasiAdminController::deleteSlider/$1');
        });
        $routes->group('pendaftaran', static function ($routes) {
        $routes->get('/', 'KoperasiAdminController::pendaftaran');
        $routes->get('detail/(:num)', 'KoperasiAdminController::detailPendaftaran/$1');
        $routes->post('approve/(:num)', 'KoperasiAdminController::approvePendaftaran/$1');
        $routes->post('reject/(:num)', 'KoperasiAdminController::rejectPendaftaran/$1');
        $routes->post('delete/(:num)', 'KoperasiAdminController::deletePendaftaran/$1');
    });
    
    // SHOW/DETAIL ROUTES
    $routes->get('anggota/show/(:num)', 'KoperasiAdminController::showAnggota/$1');
    $routes->get('unit-usaha/show/(:num)', 'KoperasiAdminController::showUnit/$1');
    $routes->get('berita/show/(:num)', 'KoperasiAdminController::showBerita/$1');
        
        // Dashboard Statistik
        $routes->get('statistik', 'KoperasiAdminController::statistik');
        $routes->get('dashboard-data', 'KoperasiAdminController::dashboardData');
    });

    // Manajemen Wisata
    $routes->group('wisata', static function($routes) {
        $routes->get('/', 'WisataController::index');
        $routes->get('create', 'WisataController::create');
        $routes->post('store', 'WisataController::store');
        $routes->get('edit/(:num)', 'WisataController::edit/$1');
        $routes->post('update/(:num)', 'WisataController::update/$1');
        $routes->post('delete/(:num)', 'WisataController::delete/$1');
        $routes->get('statistik', 'WisataController::statistik');
    });

    // =================================================================
    // MANAJEMEN KEUANGAN (ADMIN)
    // =================================================================
    $routes->group('keuangan', static function($routes) {
        $routes->get('/', 'Keuangan::index');
        $routes->get('create', 'Keuangan::create');
        $routes->post('store', 'Keuangan::store');
        $routes->get('edit/(:num)', 'Keuangan::edit/$1');
        $routes->post('update/(:num)', 'Keuangan::update/$1');
        $routes->post('delete/(:num)', 'Keuangan::delete/$1');
    });
});

// =====================================================================
// HAPUS SEMUA API PUBLIC ROUTE KARENA TIDAK ADA CONTROLLER
// =====================================================================
// $routes->group('api/public', static function($routes) {
//     // HAPUS SEMUA API PUBLIC ROUTE
// });

// =====================================================================
// SITEMAP - HAPUS KARENA TIDAK ADA CONTROLLER
// =====================================================================
// $routes->get('sitemap.xml', 'Sitemap::index');

// =====================================================================
// RUTE FALLBACK (Harus di paling akhir)
// =====================================================================

// Route untuk error 404
$routes->set404Override(function() {
    return view('errors/html/error_404');
});