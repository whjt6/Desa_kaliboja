<?php

namespace App\Controllers;

use App\Models\WisataModel;
use App\Models\NewsModel;
use App\Models\PopulationModel;
use App\Models\ProductModel;
use App\Models\ApparatusModel;
use App\Models\GalleryModel;
use App\Models\JdihPeraturanModel;
use App\Models\RkpModel;
use App\Models\VisitorCounterModel;

class Home extends BaseController
{
    protected $wisataModel;
    protected $newsModel;
    protected $populationModel;
    protected $productModel;
    protected $apparatusModel;
    protected $galleryModel;
    protected $jdihPeraturanModel;
    protected $rkpModel;
    protected $visitorCounterModel;
    protected $posyanduModel;
    protected $posbinduModel;
    protected $koperasiModel;
    protected $koperasiUnitModel;
    protected $koperasiBeritaModel;

    public function __construct()
    {
        // Inisialisasi semua model
        $this->wisataModel = new WisataModel();
        $this->newsModel = new NewsModel();
        $this->populationModel = new PopulationModel();
        $this->productModel = new ProductModel();
        $this->apparatusModel = new ApparatusModel();
        $this->galleryModel = new GalleryModel();
        $this->jdihPeraturanModel = new JdihPeraturanModel();
        $this->rkpModel = new RkpModel();
        $this->visitorCounterModel = new VisitorCounterModel();
        
        // Inisialisasi model kesehatan
        $this->posyanduModel = new \App\Models\PosyanduModel();
        $this->posbinduModel = new \App\Models\PosbinduModel();
        
        // Inisialisasi model koperasi
        $this->koperasiModel = new \App\Models\KoperasiModel();
        $this->koperasiUnitModel = new \App\Models\KoperasiUnitUsahaModel();
        $this->koperasiBeritaModel = new \App\Models\KoperasiBeritaModel();
    }

    public function index()
    {
        try {
            // TRACK PENGUNJUNG
            $this->visitorCounterModel->addHit();
            
            // Ambil data berita terbaru
            $news = $this->newsModel->orderBy('created_at', 'DESC')->findAll(3);
            
            // Ambil data statistik penduduk
            $populationStats = $this->getPopulationStats();
            
            // Ambil data produk unggulan
            $products = $this->productModel->where('is_featured', 1)
                                   ->orderBy('created_at', 'DESC')
                                   ->findAll(6);
            
            // Ambil data wisata terbaru
            $wisata = $this->getDataSafely($this->wisataModel, 3);
            
            // Ambil data galeri terbaru
            $galleries = $this->getDataSafely($this->galleryModel, 6);
            $allGalleries = $this->getDataSafely($this->galleryModel);
            
            // Ambil data aparatur
            $aparatur = $this->getDataSafely($this->apparatusModel);

            // Ambil data untuk fitur JDIH dan RKP
            $fiturData = $this->getFiturData();
            
            // Ambil data koperasi
            $koperasiData = $this->getKoperasiData();
            
            // Ambil data statistik pengunjung
            $visitorStats = [
                'today' => ['visits' => $this->visitorCounterModel->getTodayHits()],
                'yesterday' => ['visits' => $this->visitorCounterModel->getYesterdayHits()],
                'thisWeek' => ['total_visits' => $this->visitorCounterModel->getLast7DaysHits()],
                'thisMonth' => ['total_visits' => $this->visitorCounterModel->getLast30DaysHits()],
                'total' => ['total' => $this->visitorCounterModel->getTotalHits()]
            ];
            
            // Ambil data statistik kesehatan
            $tahun = date('Y');
            $kesehatanStats = $this->getKesehatanStats($tahun);

            // Process news content to limit words
            foreach ($news as &$item) {
                $item['short_content'] = $this->wordLimiter(strip_tags($item['content']), 15);
            }

            $data = [
                'title' => 'Desa Kaliboja | Website Resmi',
                'news' => $news,
                'population' => $populationStats,
                'products' => $products,
                'wisata' => $wisata,
                'galleries' => $galleries,
                'allGalleries' => $allGalleries,
                'totalGalleries' => count($allGalleries),
                'aparatur' => $aparatur,
                'fitur_data' => $fiturData,
                'koperasi' => $koperasiData, // Data koperasi ditambahkan
                'visitor_stats' => $visitorStats,
                'kesehatan_stats' => $kesehatanStats,
                'tahun_sekarang' => $tahun
                
            ];

            return view('home', $data);
            
        } catch (\Exception $e) {
            // Tangani error dengan graceful degradation
            log_message('error', 'Error in Home controller: ' . $e->getMessage());
            
            $data = [
                'title' => 'Desa Kaliboja | Website Resmi',
                'news' => [],
                'population' => ['total' => 0, 'male' => 0, 'female' => 0],
                'products' => [],
                'wisata' => [],
                'galleries' => [],
                'allGalleries' => [],
                'totalGalleries' => 0,
                'aparatur' => [],
                'fitur_data' => $this->getEmptyFiturData(),
                'koperasi' => $this->getEmptyKoperasiData(),
                'visitor_stats' => [
                    'today' => ['visits' => 1],
                    'yesterday' => ['visits' => 0],
                    'thisWeek' => ['total_visits' => 0],
                    'thisMonth' => ['total_visits' => 0],
                    'total' => ['total' => 1]
                ],
                'kesehatan_stats' => $this->getEmptyKesehatanStats(),
                'tahun_sekarang' => date('Y')
            ];
            
            return view('home', $data);
        }
    }

    private function getKoperasiData()
    {
        try {
            return [
                'profile' => $this->koperasiModel->getProfile(),
                'units' => $this->koperasiUnitModel->getPopular(4),
                'berita' => $this->koperasiBeritaModel->getLatest(3),
                'statistik' => $this->koperasiModel->getStatistik()
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting koperasi data: ' . $e->getMessage());
            return $this->getEmptyKoperasiData();
        }
    }

    private function getEmptyKoperasiData()
    {
        return [
            'profile' => ['nama_koperasi' => 'Koperasi Merah Putih'],
            'units' => [],
            'berita' => [],
            'statistik' => ['total_anggota' => 0, 'total_unit' => 0]
        ];
    }

    private function getKesehatanStats($tahun = null)
    {
        if (!$tahun) {
            $tahun = date('Y');
        }
        
        try {
            $stats = [
                'posyandu' => [],
                'posbindu' => [],
                'posyandu_by_dusun' => [],
                'posbindu_by_dusun' => [],
                'dusun_list' => []
            ];
            
            $stats['posyandu'] = $this->posyanduModel->getTotalStatistik($tahun) ?? [
                'total_balita' => 0,
                'total_ibu_hamil' => 0,
                'total_ibu_menyusui' => 0,
                'total_kelahiran' => 0,
                'total_imunisasi' => 0
            ];
            
            $stats['posbindu'] = $this->posbinduModel->getTotalStatistik($tahun) ?? [
                'total_lansia' => 0,
                'total_hipertensi' => 0,
                'total_diabetes' => 0,
                'total_gemuk_obesitas' => 0,
                'total_rujukan' => 0
            ];
            
            $stats['posyandu_by_dusun'] = $this->posyanduModel->getStatistikByDusun($tahun) ?? [];
            $stats['posbindu_by_dusun'] = $this->posbinduModel->getStatistikByDusun($tahun) ?? [];
            
            $stats['dusun_list'] = $this->posyanduModel->getAllDusunNames() ?? [
                'semboja_barat' => 'Semboja Barat',
                'semboja_timur' => 'Semboja Timur',
                'kaligenteng' => 'Kaligenteng',
                'silemud' => 'Silemud'
            ];
            
            return $stats;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting kesehatan stats: ' . $e->getMessage());
            return $this->getEmptyKesehatanStats();
        }
    }

    private function getEmptyKesehatanStats()
    {
        return [
            'posyandu' => [
                'total_balita' => 0,
                'total_ibu_hamil' => 0,
                'total_ibu_menyusui' => 0,
                'total_kelahiran' => 0,
                'total_imunisasi' => 0
            ],
            'posbindu' => [
                'total_lansia' => 0,
                'total_hipertensi' => 0,
                'total_diabetes' => 0,
                'total_gemuk_obesitas' => 0,
                'total_rujukan' => 0
            ],
            'posyandu_by_dusun' => [],
            'posbindu_by_dusun' => [],
            'dusun_list' => [
                'semboja_barat' => 'Semboja Barat',
                'semboja_timur' => 'Semboja Timur',
                'kaligenteng' => 'Kaligenteng',
                'silemud' => 'Silemud'
            ]
        ];
    }

    private function getDataSafely($model, $limit = null)
    {
        try {
            if ($limit) {
                return $model->orderBy('created_at', 'DESC')->findAll($limit);
            }
            return $model->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Error getting data from ' . get_class($model) . ': ' . $e->getMessage());
            return [];
        }
    }

    private function getPopulationStats()
    {
        $stats = ['total' => 0, 'male' => 0, 'female' => 0];
        
        try {
            if (method_exists($this->populationModel, 'getGenderStats')) {
                $genderData = $this->populationModel->getGenderStats();
                
                foreach ($genderData as $row) {
                    if (isset($row['gender']) || isset($row['JK'])) {
                        $gender = $row['gender'] ?? $row['JK'];
                        $total = $row['total'] ?? 0;
                        
                        if ($gender === 'L' || $gender === 'Laki-laki') {
                            $stats['male'] = $total;
                        } elseif ($gender === 'P' || $gender === 'Perempuan') {
                            $stats['female'] = $total;
                        }
                    }
                }
                $stats['total'] = $stats['male'] + $stats['female'];
            } else {
                $allPopulation = $this->populationModel->findAll();
                $stats['total'] = count($allPopulation);
                
                foreach ($allPopulation as $person) {
                    if (isset($person['JK']) && ($person['JK'] === 'L' || $person['JK'] === 'Laki-laki')) {
                        $stats['male']++;
                    } elseif (isset($person['JK']) && ($person['JK'] === 'P' || $person['JK'] === 'Perempuan')) {
                        $stats['female']++;
                    } elseif (isset($person['gender']) && ($person['gender'] === 'L' || $person['gender'] === 'Laki-laki')) {
                        $stats['male']++;
                    } elseif (isset($person['gender']) && ($person['gender'] === 'P' || $person['gender'] === 'Perempuan')) {
                        $stats['female']++;
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error getting population stats: ' . $e->getMessage());
        }
        
        return $stats;
    }

    private function getFiturData()
    {
        try {
            return [
                'jdih' => [
                    'peraturan_terbaru' => $this->jdihPeraturanModel->getPeraturanWithKategori(3)
                ],
                'rkp' => [
                    'kegiatan_terbaru' => $this->rkpModel->orderBy('created_at', 'DESC')->findAll(3)
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting fitur data: ' . $e->getMessage());
            return $this->getEmptyFiturData();
        }
    }

    private function getEmptyFiturData()
    {
        return [
            'jdih' => [
                'peraturan_terbaru' => []
            ],
            'rkp' => [
                'kegiatan_terbaru' => []
            ]
        ];
    }

    private function wordLimiter($str, $limit = 10, $end_char = '...')
    {
        if (trim($str) === '') {
            return $str;
        }
        
        preg_match('/^\s*+(?:\S++\s*+){1,' . (int) $limit . '}/', $str, $matches);
        
        if (strlen($str) === strlen($matches[0])) {
            $end_char = '';
        }
        
        return rtrim($matches[0]) . $end_char;
    }

    public function statistik()
    {
        $this->visitorCounterModel->addHit();
        
        $populationStats = $this->getPopulationStats();
        $visitorStats = [
            'today' => ['visits' => $this->visitorCounterModel->getTodayHits()],
            'yesterday' => ['visits' => $this->visitorCounterModel->getYesterdayHits()],
            'thisWeek' => ['total_visits' => $this->visitorCounterModel->getLast7DaysHits()],
            'thisMonth' => ['total_visits' => $this->visitorCounterModel->getLast30DaysHits()],
            'total' => ['total' => $this->visitorCounterModel->getTotalHits()]
        ];
        
        $data = [
            'title' => 'Statistik Pengunjung Desa Kaliboja',
            'population' => $populationStats,
            'visitor_stats' => $visitorStats
        ];

        return view('statistik', $data);
    }
    
    public function compatibility()
    {
        return view('compatibility');
    }
}