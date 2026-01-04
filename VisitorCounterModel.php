<?php
namespace App\Models;

use CodeIgniter\Model;

class VisitorCounterModel extends Model
{
    protected $table = 'visitor_counter';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tanggal', 'hits'];
    
    // Tambah hit hari ini
    public function addHit()
    {
        $today = date('Y-m-d');
        
        // Cek apakah sudah ada data hari ini
        $existing = $this->where('tanggal', $today)->first();
        
        if ($existing) {
            // Update
            return $this->update($existing['id'], ['hits' => $existing['hits'] + 1]);
        } else {
            // Insert baru
            return $this->insert(['tanggal' => $today, 'hits' => 1]);
        }
    }
    
    // Ambil hits hari ini
    public function getTodayHits()
    {
        $today = date('Y-m-d');
        $data = $this->where('tanggal', $today)->first();
        return $data ? $data['hits'] : 1;
    }
    
    // Ambil hits kemarin
    public function getYesterdayHits()
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $data = $this->where('tanggal', $yesterday)->first();
        return $data ? $data['hits'] : 0;
    }
    
    // Ambil total semua hits
    public function getTotalHits()
    {
        $result = $this->selectSum('hits', 'total')->first();
        return $result ? $result['total'] : 0;
    }
    
    // Ambil hits 7 hari terakhir
    public function getLast7DaysHits()
    {
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = date('Y-m-d');
        
        $result = $this->selectSum('hits', 'total')
                      ->where('tanggal >=', $startDate)
                      ->where('tanggal <=', $endDate)
                      ->first();
        
        return $result ? $result['total'] : 0;
    }
    
    // Ambil hits 30 hari terakhir
    public function getLast30DaysHits()
    {
        $startDate = date('Y-m-d', strtotime('-29 days'));
        $endDate = date('Y-m-d');
        
        $result = $this->selectSum('hits', 'total')
                      ->where('tanggal >=', $startDate)
                      ->where('tanggal <=', $endDate)
                      ->first();
        
        return $result ? $result['total'] : 0;
    }
}