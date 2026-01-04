<?php
namespace App\Controllers;

use App\Models\JdihKategoriModel;
use App\Models\JdihPeraturanModel;

class JdihController extends BaseController
{
    protected $kategoriModel;
    protected $peraturanModel;

    public function __construct()
    {
        $this->kategoriModel = new JdihKategoriModel();
        $this->peraturanModel = new JdihPeraturanModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Dashboard JDIH',
            'total_kategori' => $this->kategoriModel->countAll(),
            'total_peraturan' => $this->peraturanModel->countAll(),
            'peraturan_terbaru' => $this->peraturanModel->getPeraturanWithKategori()
        ];
        
        return view('dashboard/jdih/index', $data);
    }

    public function kategori()
    {
        $data = [
            'title' => 'Manajemen Kategori JDIH',
            'kategories' => $this->kategoriModel->findAll()
        ];
        
        return view('dashboard/jdih/kategori', $data);
    }

    public function storeKategori()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nama_kategori' => 'required|min_length[3]',
            'deskripsi' => 'permit_empty'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'nama_kategori' => $this->request->getPost('nama_kategori'),
            'slug' => url_title($this->request->getPost('nama_kategori'), '-', true),
            'deskripsi' => $this->request->getPost('deskripsi')
        ];
        
        if ($this->kategoriModel->save($data)) {
            return redirect()->to('/dashboard/jdih/kategori')->with('success', 'Kategori berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan kategori');
        }
    }

    public function updateKategori($id)
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nama_kategori' => 'required|min_length[3]',
            'deskripsi' => 'permit_empty'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'nama_kategori' => $this->request->getPost('nama_kategori'),
            'slug' => url_title($this->request->getPost('nama_kategori'), '-', true),
            'deskripsi' => $this->request->getPost('deskripsi')
        ];
        
        if ($this->kategoriModel->update($id, $data)) {
            return redirect()->to('/dashboard/jdih/kategori')->with('success', 'Kategori berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui kategori');
        }
    }

    public function deleteKategori($id)
    {
        if ($this->kategoriModel->delete($id)) {
            return redirect()->to('/dashboard/jdih/kategori')->with('success', 'Kategori berhasil dihapus');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus kategori');
        }
    }

    public function peraturan()
    {
        $data = [
            'title' => 'Manajemen Peraturan',
            'peraturans' => $this->peraturanModel->getPeraturanWithKategori(),
            'kategories' => $this->kategoriModel->findAll()
        ];
        
        return view('dashboard/jdih/peraturan', $data);
    }

    public function createPeraturan()
    {
        $data = [
            'title' => 'Tambah Peraturan',
            'kategories' => $this->kategoriModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('dashboard/jdih/create_peraturan', $data);
    }

    public function storePeraturan()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'kategori_id' => 'required',
            'jenis_peraturan' => 'required',
            'nomor' => 'required',
            'tahun' => 'required|numeric',
            'tentang' => 'required',
            'file_dokumen' => 'uploaded[file_dokumen]|max_size[file_dokumen,10240]|ext_in[file_dokumen,pdf]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $file = $this->request->getFile('file_dokumen');
        $fileName = '';

        if ($file && $file->isValid()) {
            $fileName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/jdih', $fileName);
        }

        $data = [
            'kategori_id' => $this->request->getPost('kategori_id'),
            'jenis_peraturan' => $this->request->getPost('jenis_peraturan'),
            'nomor' => $this->request->getPost('nomor'),
            'tahun' => $this->request->getPost('tahun'),
            'tentang' => $this->request->getPost('tentang'),
            'tanggal_ditetapkan' => $this->request->getPost('tanggal_ditetapkan'),
            'tanggal_diundangkan' => $this->request->getPost('tanggal_diundangkan'),
            'file_dokumen' => $fileName,
            'abstrak' => $this->request->getPost('abstrak'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->peraturanModel->save($data)) {
            return redirect()->to('/dashboard/jdih/peraturan')->with('success', 'Peraturan berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan peraturan');
        }
    }

    public function editPeraturan($id)
    {
        $peraturan = $this->peraturanModel->find($id);
        if (!$peraturan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Peraturan',
            'peraturan' => $peraturan,
            'kategories' => $this->kategoriModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('dashboard/jdih/edit_peraturan', $data);
    }

    public function updatePeraturan($id)
    {
        $peraturan = $this->peraturanModel->find($id);
        if (!$peraturan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $validation = \Config\Services::validation();
        $rules = [
            'kategori_id' => 'required',
            'jenis_peraturan' => 'required',
            'nomor' => 'required',
            'tahun' => 'required|numeric',
            'tentang' => 'required'
        ];

        // Jika ada file baru diupload
        if ($this->request->getFile('file_dokumen')->isValid()) {
            $rules['file_dokumen'] = 'uploaded[file_dokumen]|max_size[file_dokumen,10240]|ext_in[file_dokumen,pdf]';
        }

        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $file = $this->request->getFile('file_dokumen');
        $fileName = $peraturan['file_dokumen'];

        if ($file && $file->isValid()) {
            // Hapus file lama
            if ($fileName && file_exists(ROOTPATH . 'public/uploads/jdih/' . $fileName)) {
                unlink(ROOTPATH . 'public/uploads/jdih/' . $fileName);
            }
            
            $fileName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/jdih', $fileName);
        }

        $data = [
            'kategori_id' => $this->request->getPost('kategori_id'),
            'jenis_peraturan' => $this->request->getPost('jenis_peraturan'),
            'nomor' => $this->request->getPost('nomor'),
            'tahun' => $this->request->getPost('tahun'),
            'tentang' => $this->request->getPost('tentang'),
            'tanggal_ditetapkan' => $this->request->getPost('tanggal_ditetapkan'),
            'tanggal_diundangkan' => $this->request->getPost('tanggal_diundangkan'),
            'file_dokumen' => $fileName,
            'abstrak' => $this->request->getPost('abstrak'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->peraturanModel->update($id, $data)) {
            return redirect()->to('/dashboard/jdih/peraturan')->with('success', 'Peraturan berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui peraturan');
        }
    }

    public function deletePeraturan($id)
    {
        $peraturan = $this->peraturanModel->find($id);
        if (!$peraturan) {
            return redirect()->back()->with('error', 'Peraturan tidak ditemukan');
        }
        
        // Hapus file
        if ($peraturan['file_dokumen'] && file_exists(ROOTPATH . 'public/uploads/jdih/' . $peraturan['file_dokumen'])) {
            unlink(ROOTPATH . 'public/uploads/jdih/' . $peraturan['file_dokumen']);
        }
        
        if ($this->peraturanModel->delete($id)) {
            return redirect()->to('/dashboard/jdih/peraturan')->with('success', 'Peraturan berhasil dihapus');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus peraturan');
        }
    }

    public function laporan()
    {
        $data = [
            'title' => 'Laporan JDIH',
            'peraturans' => $this->peraturanModel->getPeraturanWithKategori()
        ];
        
        return view('dashboard/jdih/laporan', $data);
    }

    public function statistik()
    {
        $db = \Config\Database::connect();
        $statistik = $db->query("
            SELECT tahun, COUNT(*) as total 
            FROM jdih_peraturan 
            GROUP BY tahun 
            ORDER BY tahun DESC
        ")->getResultArray();

        $data = [
            'title' => 'Statistik JDIH',
            'statistik' => $statistik
        ];
        
        return view('dashboard/jdih/statistik', $data);
    }
}