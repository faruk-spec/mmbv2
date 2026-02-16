<?php
/**
 * Analytics Controller
 * Handles QR code analytics and statistics
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Projects\QR\Models\QRModel;

class AnalyticsController
{
    private QRModel $qrModel;
    
    public function __construct()
    {
        $this->qrModel = new QRModel();
    }
    
    /**
     * Show analytics dashboard
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Date filter parameters
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $quickFilter = isset($_GET['quick_filter']) ? $_GET['quick_filter'] : 'all';
        
        // Apply quick filters
        if ($quickFilter === 'last_7_days') {
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
        } elseif ($quickFilter === 'last_30_days') {
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d');
        } elseif ($quickFilter === 'last_90_days') {
            $startDate = date('Y-m-d', strtotime('-90 days'));
            $endDate = date('Y-m-d');
        }
        
        // Pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(10, min(100, (int)$_GET['per_page'])) : 25;
        $offset = ($page - 1) * $perPage;
        
        // Get analytics data with date filtering
        $totalQRs = $this->qrModel->countAllByUserWithDateFilter($userId, $startDate, $endDate);
        $activeQRs = $this->qrModel->countActiveByUser($userId);
        $recentQRs = $this->qrModel->getAllByUserWithDateFilter($userId, $perPage, $offset, $startDate, $endDate);
        
        // Calculate pagination
        $totalPages = ceil($totalQRs / $perPage);
        
        $this->render('analytics', [
            'title' => 'Analytics',
            'user' => Auth::user(),
            'totalQRs' => $totalQRs,
            'activeQRs' => $activeQRs,
            'recentQRs' => $recentQRs,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'offset' => $offset,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'quickFilter' => $quickFilter
        ]);
    }
    
    /**
     * Export analytics to CSV
     */
    public function exportCsv(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Date filter parameters
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        
        // Get all QR codes with filters
        $qrCodes = $this->qrModel->getAllByUserWithDateFilter($userId, 10000, 0, $startDate, $endDate);
        
        // Set CSV headers
        $filename = 'qr-analytics-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, ['ID', 'Content', 'Type', 'Scans', 'Status', 'Created', 'Deleted']);
        
        // Add data rows
        foreach ($qrCodes as $qr) {
            fputcsv($output, [
                $qr['id'],
                $qr['content'],
                $qr['type'],
                $qr['scan_count'] ?? 0,
                $qr['deleted_at'] ? 'Deleted' : 'Active',
                $qr['created_at'],
                $qr['deleted_at'] ?? '-'
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get scan trends data for chart
     */
    public function scanTrends(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        // Get days parameter (default 30)
        $days = isset($_GET['days']) ? min(365, max(7, (int)$_GET['days'])) : 30;
        
        // Get scan trends data
        $trends = $this->qrModel->getScanTrends($userId, $days);
        
        header('Content-Type: application/json');
        echo json_encode($trends);
        exit;
    }
    
    /**
     * Get top QR codes data for chart
     */
    public function topQRs(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        // Get limit parameter (default 10)
        $limit = isset($_GET['limit']) ? min(20, max(5, (int)$_GET['limit'])) : 10;
        
        // Get top QR codes
        $topQRs = $this->qrModel->getTopByScans($userId, $limit);
        
        // Format for chart
        $data = [
            'labels' => [],
            'values' => []
        ];
        
        foreach ($topQRs as $qr) {
            $data['labels'][] = mb_strimwidth($qr['content'], 0, 30, '...');
            $data['values'][] = (int)$qr['scan_count'];
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Render a project view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        
        include PROJECT_PATH . '/views/layout.php';
    }
}
