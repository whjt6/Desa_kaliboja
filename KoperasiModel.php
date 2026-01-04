<?php

namespace App\Models;

use CodeIgniter\Model;

class KoperasiModel extends Model
{
    protected $table = 'koperasi_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['key', 'value'];
    protected $useTimestamps = true;

    public function getProfile()
    {
        $settings = $this->findAll();
        $profile = [];
        
        foreach ($settings as $setting) {
            $profile[$setting['key']] = $setting['value'];
        }
        
        return $profile;
    }

    public function saveSettings($data)
    {
        foreach ($data as $key => $value) {
            $existing = $this->where('key', $key)->first();
            
            if ($existing) {
                $this->update($existing['id'], ['value' => $value]);
            } else {
                $this->insert(['key' => $key, 'value' => $value]);
            }
        }
        
        return true;
    }

    public function getStatistik()
    {
        $anggotaModel = new KoperasiAnggotaModel();
        $simpananModel = new KoperasiSimpananModel();
        $unitModel = new KoperasiUnitUsahaModel();
        
        return [
            'total_anggota' => $anggotaModel->countAll(),
            'anggota_aktif' => $anggotaModel->where('status', 'aktif')->countAllResults(),
            'total_simpanan' => $simpananModel->getTotalSaldo(),
            'total_unit' => $unitModel->countAll(),
            'unit_tersedia' => $unitModel->where('status', 'tersedia')->countAllResults()
        ];
    }

    public function getVisiMisi()
    {
        $settings = $this->getProfile();
        
        return [
            'visi' => $settings['visi'] ?? 'Belum diatur',
            'misi' => $settings['misi'] ?? 'Belum diatur'
        ];
    }

    public function getStruktur()
    {
        $settings = $this->getProfile();
        
        return $settings['struktur_organisasi'] ?? 'Belum diatur';
    }

    public function getPersyaratan()
    {
        $settings = $this->getProfile();
        
        return $settings['persyaratan_anggota'] ?? 'Belum diatur';
    }

    public function getManfaatAnggota()
    {
        $settings = $this->getProfile();
        
        return $settings['manfaat_anggota'] ?? 'Belum diatur';
    }

    public function getInfoSimpanan()
    {
        $settings = $this->getProfile();
        
        return [
            'pokok' => $settings['simpanan_pokok'] ?? 0,
            'wajib' => $settings['simpanan_wajib'] ?? 0
        ];
    }

    public function getKontak()
    {
        $settings = $this->getProfile();
        
        return [
            'alamat' => $settings['alamat'] ?? '',
            'telepon' => $settings['telepon'] ?? '',
            'whatsapp' => $settings['whatsapp'] ?? '',
            'email' => $settings['email'] ?? '',
            'website' => $settings['website'] ?? ''
        ];
    }

    public function getJamOperasional()
    {
        $settings = $this->getProfile();
        
        return $settings['jam_operasional'] ?? 'Senin - Jumat: 08:00 - 16:00';
    }

    public function getAllSettings()
    {
        return $this->getProfile();
    }
}