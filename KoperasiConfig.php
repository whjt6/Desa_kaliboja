<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class KoperasiConfig extends BaseConfig
{
    public $kategories = [
        'pupuk-dan-obat',
        'alat-pertanian',
        'sembako',
        'sewa-tenda',
        'transportasi',
        'jasa-lainnya'
    ];
    
    public $simpananPokok = 50000;
    public $simpananWajib = 10000;
    
    public $whatsappAdmin = '628123456789';
    
    public $uploadPaths = [
        'unit' => 'uploads/koperasi/unit/',
        'berita' => 'uploads/koperasi/berita/',
        'laporan' => 'uploads/koperasi/laporan/',
        'slider' => 'uploads/koperasi/slider/',
        'ktp' => 'uploads/koperasi/ktp/',
        'foto' => 'uploads/koperasi/foto/'
    ];
}