<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PosyanduModel;
use App\Models\PosbinduModel;

class KesehatanController extends BaseController
{
    protected $posyanduModel;
    protected $posbinduModel;
    
    public function __construct()
    {
        $this->posyanduModel = new PosyanduModel();
        $this->posbinduModel = new PosbinduModel();
    }
    
    // Halaman utama layanan kesehatan
    public function index()
    {
        $tahun = date('Y');
        
        $data = [
            'title' => 'Layanan Kesehatan - Posyandu & Posbindu Desa Kaliboja',
            'posyanduStatistik' => $this->posyanduModel->getTotalStatistik($tahun),
            'posbinduStatistik' => $this->posbinduModel->getTotalStatistik($tahun),
            'posyanduByDusun' => $this->posyanduModel->getStatistikByDusun($tahun),
            'posbinduByDusun' => $this->posbinduModel->getStatistikByDusun($tahun),
            'dusunList' => $this->posyanduModel->getAllDusunNames(),
            'tahun' => $tahun
        ];
        
        return view('pages/kesehatan/index', $data);
    }
    
    // Halaman khusus Posyandu
    public function posyandu()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        
        $data = [
            'title' => 'Posyandu Desa Kaliboja',
            'statistik' => $this->posyanduModel->getTotalStatistik($tahun),
            'statistikDusun' => $this->posyanduModel->getStatistikByDusun($tahun),
            'monthlyData' => $this->posyanduModel->getMonthlyData(null, $tahun),
            'dusunList' => $this->posyanduModel->getAllDusunNames(),
            'tahun' => $tahun
        ];
        
        return view('pages/kesehatan/posyandu', $data);
    }
    
    // Halaman khusus Posbindu
    public function posbindu()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        
        $data = [
            'title' => 'Posbindu Lansia Desa Kaliboja',
            'statistik' => $this->posbinduModel->getTotalStatistik($tahun),
            'statistikDusun' => $this->posbinduModel->getStatistikByDusun($tahun),
            'riskDistribution' => $this->posbinduModel->getRiskDistribution($tahun),
            'dusunList' => $this->posbinduModel->getAllDusunNames(),
            'tahun' => $tahun
        ];
        
        return view('pages/kesehatan/posbindu', $data);
    }
    
    // Detail per Dusun
    public function dusun($dusun)
    {
        $tahun = date('Y');
        $dusunList = $this->posyanduModel->getAllDusunNames();
        
        if (!isset($dusunList[$dusun])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Dusun tidak ditemukan!');
        }
        
        $data = [
            'title' => 'Data Kesehatan Dusun ' . $dusunList[$dusun],
            'dusun' => $dusun,
            'dusunName' => $dusunList[$dusun],
            'tahun' => $tahun,
            'posyanduData' => $this->posyanduModel->getMonthlyData($dusun, $tahun),
            'posbinduData' => $this->posbinduModel->where('dusun', $dusun)->where('tahun', $tahun)->findAll(),
            'posyanduStatistik' => $this->getPosyanduStatistikByDusun($dusun, $tahun),
            'posbinduStatistik' => $this->getPosbinduStatistikByDusun($dusun, $tahun)
        ];
        
        return view('pages/kesehatan/dusun', $data);
    }
    
    private function getPosyanduStatistikByDusun($dusun, $tahun)
    {
        $builder = $this->posyanduModel;
        $builder->select("
            SUM(jumlah_balita_l + jumlah_balita_p) as total_balita,
            SUM(jumlah_ibu_hamil) as total_ibu_hamil,
            SUM(jumlah_ibu_menyusui) as total_ibu_menyusui,
            SUM(kelahiran_l + kelahiran_p) as total_kelahiran,
            SUM(balita_gizi_buruk) as total_gizi_buruk,
            SUM(balita_gizi_baik) as total_gizi_baik
        ");
        $builder->where('dusun', $dusun);
        $builder->where('tahun', $tahun);
        
        return $builder->get()->getRowArray();
    }
    
    private function getPosbinduStatistikByDusun($dusun, $tahun)
    {
        $builder = $this->posbinduModel;
        $builder->select("
            SUM(jumlah_lansia_total) as total_lansia,
            SUM(tekanan_darah_tingkat2 + tekanan_darah_tingkat3) as total_hipertensi,
            SUM(gula_darah_diabetes) as total_diabetes,
            SUM(imt_obesitas) as total_obesitas,
            SUM(jumlah_rujukan) as total_rujukan
        ");
        $builder->where('dusun', $dusun);
        $builder->where('tahun', $tahun);
        
        return $builder->get()->getRowArray();
    }
}