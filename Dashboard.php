<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PopulationModel;
use App\Models\NewsModel;
use App\Models\SuratArsipModel;
use App\Models\ProductModel;
use App\Models\KeuanganModel;

class Dashboard extends BaseController
{
    protected $populationModel;
    protected $newsModel;
    protected $suratArsipModel;
    protected $productModel;
    protected $keuanganModel;

    public function __construct()
    {
        $this->populationModel = new PopulationModel();
        $this->newsModel = new NewsModel();
        $this->suratArsipModel = new SuratArsipModel();
        $this->productModel = new ProductModel();
        $this->keuanganModel = new KeuanganModel();
    }

    public function index()
    {
        // Menggunakan model keuangan yang sudah diinisialisasi di constructor
        $dataKeuangan = $this->keuanganModel->findAll();
        
        // Hitung total pemasukan, pengeluaran, dan saldo
        $totalPemasukan = array_sum(array_column($dataKeuangan, 'pemasukan'));
        $totalPengeluaran = array_sum(array_column($dataKeuangan, 'pengeluaran'));
        $saldo = $totalPemasukan - $totalPengeluaran;
        
        // Siapkan data untuk grafik
        $grafikData = $this->siapkanDataGrafik($dataKeuangan);
        
        // Data statistik penduduk
        $genderData = $this->populationModel->getGenderStats();
        $laki_laki = 0;
        $perempuan = 0;

        foreach ($genderData as $row) {
            $genderKey = null;
            if (isset($row['JK'])) {
                $genderKey = 'JK';
            } elseif (isset($row['gender'])) {
                $genderKey = 'gender';
            }
            
            if ($genderKey === null) {
                continue;
            }

            if ($row[$genderKey] === 'L') {
                $laki_laki = $row['total'];
            } elseif ($row[$genderKey] === 'P') {
                $perempuan = $row['total'];
            }
        }

        $stats = [
            'total'     => $laki_laki + $perempuan,
            'laki_laki' => $laki_laki,
            'perempuan' => $perempuan,
        ];

        // Data untuk dashboard
        $data = [
            'title' => 'Dashboard Admin',
            'total_penduduk' => $stats['total'],
            'total_berita' => $this->newsModel->countAll(),
            'layanan_surat' => $this->suratArsipModel->countAll(),
            'total_produk' => $this->productModel->countAll(),
            'stats' => $stats,
            'latest_news' => $this->newsModel->orderBy('created_at', 'DESC')->findAll(5),
            'featured_products' => $this->productModel->where('is_featured', 1)->findAll(4),
            'keuangan' => [
                'data' => $dataKeuangan,
                'pemasukan_total' => $totalPemasukan,
                'pengeluaran_total' => $totalPengeluaran,
                'saldo' => $saldo,
                'grafik_data' => $grafikData
            ]
        ];

        return view('dashboard/index', $data);
    }
    
    private function siapkanDataGrafik($dataKeuangan)
    {
        // Kelompokkan data keuangan per bulan
        $pemasukanPerBulan = array_fill(1, 12, 0);
        $pengeluaranPerBulan = array_fill(1, 12, 0);
        
        foreach ($dataKeuangan as $data) {
            $bulan = date('n', strtotime($data['tanggal']));
            $pemasukanPerBulan[$bulan] += $data['pemasukan'];
            $pengeluaranPerBulan[$bulan] += $data['pengeluaran'];
        }
        
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'pemasukan' => array_values($pemasukanPerBulan),
            'pengeluaran' => array_values($pengeluaranPerBulan)
        ];
    }
}