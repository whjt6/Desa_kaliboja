<?php

namespace App\Controllers;

use App\Models\VisitorModel;

class Statistik extends BaseController
{
    public function index()
    {
        $visitorModel = new VisitorModel();
        
        // Data statistik
        $data = [
            'title' => 'Statistik Pengunjung',
            'today' => $visitorModel->getTodayStats(),
            'yesterday' => $visitorModel->getYesterdayStats(),
            'thisWeek' => $visitorModel->getThisWeekStats(),
            'lastWeek' => $visitorModel->getLastWeekStats(),
            'thisMonth' => $visitorModel->getThisMonthStats(),
            'lastMonth' => $visitorModel->getLastMonthStats(),
            'total' => $visitorModel->getTotalVisits(),
            'chart7Days' => $visitorModel->getLast7DaysChartData(),
            'chart30Days' => $visitorModel->getLast30DaysChartData(),
            'hourly' => $visitorModel->getHourlyStats(),
            'popularPages' => $visitorModel->getPopularPages(10)
        ];
        
        return view('statistik/index', $data);
    }

    public function admin()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $visitorModel = new VisitorModel();
        
        $data = [
            'title' => 'Dashboard Statistik Pengunjung',
            'today' => $visitorModel->getTodayStats(),
            'yesterday' => $visitorModel->getYesterdayStats(),
            'thisWeek' => $visitorModel->getThisWeekStats(),
            'lastWeek' => $visitorModel->getLastWeekStats(),
            'thisMonth' => $visitorModel->getThisMonthStats(),
            'lastMonth' => $visitorModel->getLastMonthStats(),
            'total' => $visitorModel->getTotalVisits(),
            'chart7Days' => $visitorModel->getLast7DaysChartData(),
            'chart30Days' => $visitorModel->getLast30DaysChartData(),
            'hourly' => $visitorModel->getHourlyStats(),
            'popularPages' => $visitorModel->getPopularPages(10)
        ];
        
        return view('admin/statistik', $data);
    }

    public function export($type = 'csv')
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $visitorModel = new VisitorModel();
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        $data = $visitorModel->where('date >=', $startDate)
                             ->where('date <=', $endDate)
                             ->orderBy('date', 'ASC')
                             ->findAll();
        
        if ($type === 'csv') {
            return $this->exportToCSV($data, $startDate, $endDate);
        } elseif ($type === 'pdf') {
            return $this->exportToPDF($data, $startDate, $endDate);
        }
        
        return redirect()->back()->with('error', 'Format ekspor tidak valid');
    }

    private function exportToCSV($data, $startDate, $endDate)
    {
        $filename = "statistik_pengunjung_{$startDate}_to_{$endDate}.csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, ['Tanggal', 'Kunjungan', 'Pengunjung Unik', 'Pageviews']);
        
        // Data
        foreach ($data as $row) {
            fputcsv($output, [
                $row['date'],
                $row['visits'],
                $row['unique_visitors'],
                $row['pageviews']
            ]);
        }
        
        fclose($output);
        exit();
    }
}