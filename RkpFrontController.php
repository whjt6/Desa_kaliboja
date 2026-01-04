<?php
namespace App\Controllers;

use App\Models\RkpModel;

class RkpFrontController extends BaseController
{
    protected $rkpModel;

    public function __construct()
    {
        $this->rkpModel = new RkpModel();
    }

    public function index()
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        
        $data = [
            'title' => 'Rencana Kerja Pemerintah (RKP) Desa Kaliboja',
            'rkps' => $this->rkpModel->where('tahun', $tahun)->findAll(),
            'tahun' => $tahun,
            'tahunList' => $this->rkpModel->getTahunList()
        ];
        
        return view('rkp/index', $data);
    }

    public function detail($id)
    {
        $rkp = $this->rkpModel->find($id);
        
        if (!$rkp) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => $rkp['nama_kegiatan'] . ' - RKP Desa',
            'rkp' => $rkp
        ];
        
        return view('rkp/detail', $data);
    }

    public function tahun($tahun)
    {
        $data = [
            'title' => 'RKP Desa Tahun ' . $tahun,
            'rkps' => $this->rkpModel->where('tahun', $tahun)->findAll(),
            'tahun' => $tahun,
            'tahunList' => $this->rkpModel->getTahunList()
        ];
        
        return view('rkp/tahun', $data);
    }
}