<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiUnitUsahaModel extends Model
{
    protected $table = 'koperasi_unit_usaha';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_unit', 'nama_unit', 'kategori', 'deskripsi',
        'harga', 'satuan', 'stok', 'status', 'gambar'
    ];
    protected $useTimestamps = true;

    public function getAllWithKategori($kategori = null, $search = null)
    {
        $builder = $this->builder();
        
        if ($kategori) {
            $builder->where('kategori', $kategori);
        }
        
        if ($search) {
            $builder->groupStart()
                   ->like('nama_unit', $search)
                   ->orLike('deskripsi', $search)
                   ->orLike('kode_unit', $search)
                   ->groupEnd();
        }
        
        $builder->where('status', 'tersedia')
               ->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    public function getPopular($limit = 6)
    {
        return $this->where('status', 'tersedia')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function findWithKategori($id)
    {
        return $this->find($id);
    }

    public function getRelated($kategori, $excludeId, $limit = 4)
    {
        return $this->where('kategori', $kategori)
                   ->where('id !=', $excludeId)
                   ->where('status', 'tersedia')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getKategoriList()
    {
        // Bisa dari database atau config
        return [
            'pupuk-dan-obat',
            'alat-pertanian',
            'sembako',
            'sewa-tenda',
            'transportasi',
            'jasa-lainnya'
        ];
    }

    public function getStatistik()
    {
        $result = $this->select("kategori, COUNT(*) as total")
                      ->groupBy('kategori')
                      ->get()
                      ->getResultArray();
        
        $statistik = [];
        foreach ($result as $row) {
            $statistik[$row['kategori']] = (int)$row['total'];
        }
        
        return $statistik;
    }

    public function getGrafikPenjualan()
    {
        // Logika untuk grafik penjualan unit
        return [
            ['bulan' => '2024-01', 'total' => 10],
            ['bulan' => '2024-02', 'total' => 15],
            ['bulan' => '2024-03', 'total' => 8],
            ['bulan' => '2024-04', 'total' => 12],
            ['bulan' => '2024-05', 'total' => 20],
            ['bulan' => '2024-06', 'total' => 18]
        ];
    }

    public function saveKategori($kategories)
    {
        // Simpan ke database atau file config
        // Contoh sederhana:
        $configFile = APPPATH . 'Config/KoperasiConfig.php';
        $content = "<?php\n\nreturn " . var_export(['kategories' => $kategories], true) . ";\n";
        
        return file_put_contents($configFile, $content);
    }
    // Tambahkan method ini di KoperasiUnitUsahaModel.php

public function getBySlug($slug)
{
    return $this->where('slug', $slug)->first();
}

public function incrementView($id)
{
    $this->builder()->set('views', 'views + 1', false)->where('id', $id)->update();
}

public function getPopularByViews($limit = 6)
{
    return $this->where('status', 'tersedia')
               ->orderBy('views', 'DESC')
               ->limit($limit)
               ->findAll();
}
}