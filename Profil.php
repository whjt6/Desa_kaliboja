<?php
namespace App\Controllers;

use App\Models\PopulationModel;

class Profil extends BaseController
{
    protected $populationModel;

    public function __construct()
    {
        $this->populationModel = new PopulationModel();
    }

    public function index()
    {
        // Ambil data statistik penduduk
        $populationStats = $this->getPopulationStats();
        
        $data = [
            'title' => 'Profil Desa Kaliboja',
            'population' => $populationStats
        ];
        
        return view('profil', $data);
    }

    /**
     * Mendapatkan statistik populasi
     */
    private function getPopulationStats()
    {
        $stats = ['total' => 0, 'male' => 0, 'female' => 0];
        
        try {
            // Method 1: Gunakan method khusus jika ada di model
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
            } 
            // Method 2: Hitung manual dari database
            else {
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
            // Fallback jika terjadi error
            log_message('error', 'Error getting population stats: ' . $e->getMessage());
        }
        
        return $stats;
    }
}