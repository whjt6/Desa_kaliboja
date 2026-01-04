<?php

namespace App\Controllers;

use App\Models\SuratJenisModel;
use App\Models\SuratArsipModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class SuratController extends BaseController
{
    /**
     * Menampilkan halaman utama untuk memilih dan membuat surat.
     */
        public function index()
{
    $suratJenisModel = new SuratJenisModel();
    $suratArsipModel = new SuratArsipModel();
    
    $data = [
        'title'      => 'Buat Surat Baru',
        'jenisSurat' => $suratJenisModel->findAll(),
        'arsip' => $suratArsipModel
            ->select('surat_arsip.*, surat_jenis.nama_surat as jenis_surat_nama')
            ->join('surat_jenis', 'surat_jenis.id = surat_arsip.surat_jenis_id')
            ->orderBy('surat_arsip.created_at', 'DESC')
            ->findAll(10), // Hanya ambil 10 data terbaru
    ];
    return view('dashboard/surat/pembuatan', $data);
}
    /**
     * Mengambil field form secara dinamis via AJAX.
     */
    public function getFormFields($jenisId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }
        
        $suratJenisModel = new SuratJenisModel();
        $jenis = $suratJenisModel->find($jenisId);

        if (!$jenis) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jenis surat tidak ditemukan.']);
        }

        return $this->response->setJSON(['status' => 'success', 'fields' => json_decode($jenis['fields'])]);
    }

    /**
     * Memproses data dari form, menyimpan ke arsip, dan menghasilkan PDF untuk diunduh.
     */
    public function generate()
    {
        $suratJenisModel = new SuratJenisModel();
        $suratArsipModel = new SuratArsipModel();

        $jenisId = $this->request->getPost('surat_jenis_id');
        $dataPemohon = $this->request->getPost('data');
        
        $jenisSurat = $suratJenisModel->find($jenisId);
        if (!$jenisSurat) {
            return redirect()->back()->with('error', 'Jenis surat tidak valid.');
        }

        // Logika Penomoran Surat
        $bulanRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bulanIni = (int)date('m');
        $tahunIni = date('Y');
        
        // Hitung nomor urut berdasarkan jenis surat dan tahun
        $nomorUrut = $suratArsipModel->where('YEAR(tanggal_surat)', $tahunIni)
                                     ->where('surat_jenis_id', $jenisId)
                                     ->countAllResults() + 1;
        
        $nomorSurat = sprintf('%03d', $nomorUrut) . '/' . $jenisSurat['kode_surat'] . '/' . $bulanRomawi[$bulanIni - 1] . '/' . $tahunIni;
        
        // Simpan data ke arsip
        $arsipData = [
            'surat_jenis_id' => $jenisId,
            'nomor_surat'    => $nomorSurat,
            'tanggal_surat'  => date('Y-m-d'),
            'data_pemohon'   => json_encode($dataPemohon),
            'created_at'     => date('Y-m-d H:i:s')
        ];
        $suratArsipModel->save($arsipData);
        
        // **FIX: Konversi logo ke Base64 untuk Dompdf**
        $pathToImage = FCPATH . 'img/logo.png';
        $logoBase64 = '';
        if (file_exists($pathToImage)) {
            $type = pathinfo($pathToImage, PATHINFO_EXTENSION);
            $data = file_get_contents($pathToImage);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Siapkan data untuk template PDF
        $dataUntukPDF = [
            'logo'          => $logoBase64,
            'nomor_surat'   => $nomorSurat,
            'tanggal_surat' => date('d F Y'),
            'data_pemohon'  => (object) $dataPemohon,
            'kepala_desa'   => 'Afit', 
            'sekretaris_desa' => 'Budi'
        ];
        
        // Render view HTML menjadi string
        $templatePath = 'dashboard/surat/templates/' . strtolower($jenisSurat['kode_surat']);
        $html = view($templatePath, $dataUntukPDF);

        // Konfigurasi Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Hapus output buffer sebelum mengirim PDF
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        return $dompdf->stream($jenisSurat['nama_surat'] . ' - ' . $nomorSurat . '.pdf', ['Attachment' => 1]);
    }

    /**
     * Menampilkan halaman arsip surat yang sudah dibuat.
     */
    public function arsip()
    {
        $suratArsipModel = new SuratArsipModel();
        $data = [
            'title' => 'Arsip Layanan Surat',
            'arsip' => $suratArsipModel
                ->select('surat_arsip.*, surat_jenis.nama_surat as jenis_surat_nama')
                ->join('surat_jenis', 'surat_jenis.id = surat_arsip.surat_jenis_id')
                ->orderBy('surat_arsip.tanggal_surat', 'DESC')
                ->findAll(),
        ];
        return view('dashboard/surat/arsip', $data);
    }

    /**
     * Menghapus data arsip surat.
     */
    public function deleteArsip($id)
    {
        $suratArsipModel = new SuratArsipModel();
        $surat = $suratArsipModel->find($id);

        if ($surat) {
            $suratArsipModel->delete($id);
            return redirect()->to('/dashboard/surat/arsip')->with('success', 'Data arsip berhasil dihapus.');
        }

        return redirect()->to('/dashboard/surat/arsip')->with('error', 'Data arsip tidak ditemukan.');
    }

    /**
     * Download arsip surat.
     */
    public function downloadArsip($id)
    {
        $suratArsipModel = new SuratArsipModel();
        $suratJenisModel = new SuratJenisModel();
        
        $arsip = $suratArsipModel->find($id);
        if (!$arsip) {
            return redirect()->to('/dashboard/surat/arsip')->with('error', 'Data arsip tidak ditemukan.');
        }
        
        $jenisSurat = $suratJenisModel->find($arsip['surat_jenis_id']);
        
        // Konversi logo ke Base64 untuk Dompdf
        $pathToImage = FCPATH . 'img/logo.png';
        $logoBase64 = '';
        if (file_exists($pathToImage)) {
            $type = pathinfo($pathToImage, PATHINFO_EXTENSION);
            $data = file_get_contents($pathToImage);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Siapkan data untuk template PDF
        $dataUntukPDF = [
            'logo'          => $logoBase64,
            'nomor_surat'   => $arsip['nomor_surat'],
            'tanggal_surat' => \CodeIgniter\I18n\Time::parse($arsip['tanggal_surat'])->toLocalizedString('d F Y'),
            'data_pemohon'  => json_decode($arsip['data_pemohon']),
            'kepala_desa'   => 'Afit', 
            'sekretaris_desa' => 'Budi'
        ];
        
        // Render view HTML menjadi string
        $templatePath = 'dashboard/surat/templates/' . strtolower($jenisSurat['kode_surat']);
        $html = view($templatePath, $dataUntukPDF);

        // Konfigurasi Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Hapus output buffer sebelum mengirim PDF
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        return $dompdf->stream($jenisSurat['nama_surat'] . ' - ' . $arsip['nomor_surat'] . '.pdf', ['Attachment' => 1]);
    }
    // Di controller manapun

}