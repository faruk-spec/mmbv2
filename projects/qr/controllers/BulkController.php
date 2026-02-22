<?php
/**
 * Bulk Generator Controller
 * Handles bulk QR code generation
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Core\Logger;
use Projects\QR\Models\BulkJobModel;
use Projects\QR\Models\QRModel;
use Projects\QR\Models\CampaignModel;

class BulkController
{
    private BulkJobModel $model;
    private QRModel $qrModel;
    private CampaignModel $campaignModel;
    
    public function __construct()
    {
        $this->model = new BulkJobModel();
        $this->qrModel = new QRModel();
        $this->campaignModel = new CampaignModel();
    }
    
    /**
     * Show bulk generation page
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Get recent jobs
        $jobs = $this->model->getByUser($userId, 20);
        
        // Get campaigns for dropdown
        $campaigns = $this->campaignModel->getByUser($userId);
        
        $this->render('bulk', [
            'title' => 'Bulk Generate',
            'user' => Auth::user(),
            'jobs' => $jobs,
            'campaigns' => $campaigns
        ]);
    }
    
    /**
     * Upload and process CSV file
     */
    public function upload(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
            exit;
        }
        
        $file = $_FILES['csv_file'];
        // Campaign ID is optional - convert empty string to null
        $campaignId = !empty($_POST['campaign_id']) ? (int) $_POST['campaign_id'] : null;
        
        // Validate file type
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Only CSV files are allowed']);
            exit;
        }
        
        // Parse CSV
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Failed to read file']);
            exit;
        }
        
        $rows = [];
        $headers = fgetcsv($handle); // First row as headers
        
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) > 0 && !empty($data[0])) {
                $rows[] = $data;
            }
        }
        fclose($handle);
        
        if (count($rows) === 0) {
            echo json_encode(['success' => false, 'message' => 'No data found in CSV file']);
            exit;
        }
        
        // Create bulk job
        $jobId = $this->model->create($userId, [
            'campaign_id' => $campaignId,
            'total_count' => count($rows)
        ]);
        
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'Failed to create bulk job']);
            exit;
        }

        Logger::activity($userId, 'qr_bulk_upload', ['job_id' => $jobId, 'total' => count($rows), 'campaign_id' => $campaignId]);
        
        // Store CSV data in session for processing
        $_SESSION['bulk_job_' . $jobId] = [
            'rows' => $rows,
            'headers' => $headers,
            'campaign_id' => $campaignId
        ];
        
        echo json_encode([
            'success' => true, 
            'job_id' => $jobId, 
            'total' => count($rows),
            'message' => 'File uploaded successfully. Ready to generate.'
        ]);
        exit;
    }
    
    /**
     * Generate QR codes from uploaded data
     */
    public function generate(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $jobId = $_POST['job_id'] ?? null;
        
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'Job ID required']);
            exit;
        }
        
        // Get job data from session
        $jobData = $_SESSION['bulk_job_' . $jobId] ?? null;
        
        if (!$jobData) {
            echo json_encode(['success' => false, 'message' => 'Job data not found']);
            exit;
        }
        
        $rows = $jobData['rows'];
        $campaignId = $jobData['campaign_id'];
        $completed = 0;
        $failed = 0;
        $qrIds = [];
        
        // Process each row
        foreach ($rows as $row) {
            $content = $row[0] ?? ''; // First column is content
            
            if (empty($content)) {
                $failed++;
                continue;
            }
            
            $qrData = [
                'content' => $content,
                'type' => 'url', // Default type
                'campaign_id' => $campaignId,
                'size' => 300,
                'foreground_color' => '#000000',
                'background_color' => '#ffffff',
                'error_correction' => 'H',
                // Make bulk QR codes dynamic for tracking
                'is_dynamic' => 1,
                'redirect_url' => $content,
                'status' => 'active'
            ];
            
            $qrId = $this->qrModel->save($userId, $qrData);
            
            if ($qrId) {
                $completed++;
                $qrIds[] = $qrId;
                
                // Generate short code for access
                $shortCode = $this->generateShortCode($qrId);
                $this->qrModel->updateShortCode($qrId, $shortCode);
                
                // Update content to access URL for dynamic QRs
                $accessUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/projects/qr/access/' . $shortCode;
                $this->qrModel->update($qrId, $userId, ['content' => $accessUrl]);
            } else {
                $failed++;
            }
        }
        
        // Update job progress
        $this->model->updateProgress($jobId, $completed, $failed);
        
        // Mark as completed
        $this->model->markCompleted($jobId, ''); // File path can be added later
        
        // Clean up session
        unset($_SESSION['bulk_job_' . $jobId]);
        
        echo json_encode([
            'success' => true,
            'completed' => $completed,
            'failed' => $failed,
            'qr_ids' => $qrIds,
            'message' => "Generated {$completed} QR codes successfully"
        ]);
        Logger::activity($userId, 'qr_bulk_generated', ['job_id' => $jobId, 'completed' => $completed, 'failed' => $failed]);
        exit;
    }
    
    /**
     * Get job status
     */
    public function status(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $jobId = $_GET['id'] ?? null;
        
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'Job ID required']);
            exit;
        }
        
        $job = $this->model->getById($jobId, $userId);
        
        if ($job) {
            echo json_encode(['success' => true, 'job' => $job]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
        }
        exit;
    }
    
    /**
     * Download sample CSV file
     */
    public function downloadSample(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Get sample type from query parameter
        $type = $_GET['type'] ?? 'url';
        
        // Define sample data for different types
        $samples = [
            'url' => [
                'filename' => 'sample-urls.csv',
                'headers' => ['URL', 'Description'],
                'rows' => [
                    ['https://example.com', 'Example Website'],
                    ['https://google.com', 'Google Search'],
                    ['https://github.com', 'GitHub'],
                    ['https://twitter.com', 'Twitter'],
                    ['https://facebook.com', 'Facebook']
                ]
            ],
            'text' => [
                'filename' => 'sample-text.csv',
                'headers' => ['Text', 'Notes'],
                'rows' => [
                    ['Hello World!', 'Simple greeting'],
                    ['This is a sample text for QR code', 'Example text'],
                    ['Contact us: info@example.com', 'Contact info'],
                    ['Special Offer: 20% OFF', 'Promotional text'],
                    ['Event Code: ABC123', 'Event code']
                ]
            ],
            'email' => [
                'filename' => 'sample-emails.csv',
                'headers' => ['Email Address', 'Subject', 'Message'],
                'rows' => [
                    ['john@example.com', 'Hello', 'Sample email message'],
                    ['jane@example.com', 'Meeting', 'Let\'s schedule a meeting'],
                    ['support@example.com', 'Support', 'Need help with...'],
                    ['info@example.com', 'Information', 'Request for information'],
                    ['sales@example.com', 'Quote', 'Price quote request']
                ]
            ],
            'location' => [
                'filename' => 'sample-locations.csv',
                'headers' => ['Latitude', 'Longitude', 'Place Name'],
                'rows' => [
                    ['40.7128', '-74.0060', 'New York City'],
                    ['51.5074', '-0.1278', 'London'],
                    ['35.6762', '139.6503', 'Tokyo'],
                    ['48.8566', '2.3522', 'Paris'],
                    ['19.0760', '72.8777', 'Mumbai']
                ]
            ],
            'phone' => [
                'filename' => 'sample-phones.csv',
                'headers' => ['Phone Number', 'Contact Name'],
                'rows' => [
                    ['+1-555-0101', 'John Doe'],
                    ['+1-555-0102', 'Jane Smith'],
                    ['+1-555-0103', 'Bob Johnson'],
                    ['+44-20-7123-4567', 'Alice Brown'],
                    ['+91-9876543210', 'Raj Kumar']
                ]
            ],
            'sms' => [
                'filename' => 'sample-sms.csv',
                'headers' => ['Phone Number', 'Message'],
                'rows' => [
                    ['+1-555-0101', 'Hello! This is a sample SMS message'],
                    ['+1-555-0102', 'Your order has been confirmed'],
                    ['+1-555-0103', 'Meeting at 3 PM today'],
                    ['+44-20-7123-4567', 'Special offer just for you!'],
                    ['+91-9876543210', 'Thank you for your purchase']
                ]
            ],
            'whatsapp' => [
                'filename' => 'sample-whatsapp.csv',
                'headers' => ['Phone Number', 'Message'],
                'rows' => [
                    ['+1-555-0101', 'Hi! Contact us on WhatsApp'],
                    ['+1-555-0102', 'Join our WhatsApp group'],
                    ['+1-555-0103', 'Get support via WhatsApp'],
                    ['+44-20-7123-4567', 'WhatsApp for business inquiries'],
                    ['+91-9876543210', 'Chat with us on WhatsApp']
                ]
            ],
            'skype' => [
                'filename' => 'sample-skype.csv',
                'headers' => ['Skype Username', 'Action Type'],
                'rows' => [
                    ['john.doe', 'chat'],
                    ['jane.smith', 'call'],
                    ['support.team', 'chat'],
                    ['sales.dept', 'call'],
                    ['tech.support', 'chat']
                ]
            ],
            'zoom' => [
                'filename' => 'sample-zoom.csv',
                'headers' => ['Meeting ID', 'Passcode', 'Description'],
                'rows' => [
                    ['123-456-7890', 'abc123', 'Team Meeting'],
                    ['234-567-8901', 'def456', 'Client Presentation'],
                    ['345-678-9012', 'ghi789', 'Training Session'],
                    ['456-789-0123', 'jkl012', 'Weekly Standup'],
                    ['567-890-1234', 'mno345', 'Product Demo']
                ]
            ],
            'wifi' => [
                'filename' => 'sample-wifi.csv',
                'headers' => ['SSID', 'Password', 'Security Type'],
                'rows' => [
                    ['MyHomeWiFi', 'password123', 'WPA'],
                    ['OfficeNetwork', 'officepass456', 'WPA2'],
                    ['GuestNetwork', 'guest789', 'WPA'],
                    ['CafeWiFi', 'cafe2024', 'WPA2'],
                    ['PublicWiFi', '', 'nopass']
                ]
            ],
            'vcard' => [
                'filename' => 'sample-contacts.csv',
                'headers' => ['Full Name', 'Email', 'Phone', 'Company'],
                'rows' => [
                    ['John Doe', 'john@example.com', '+1-555-0101', 'Acme Corp'],
                    ['Jane Smith', 'jane@example.com', '+1-555-0102', 'Tech Inc'],
                    ['Bob Johnson', 'bob@example.com', '+1-555-0103', 'Design Co'],
                    ['Alice Brown', 'alice@example.com', '+44-20-7123-4567', 'Global Ltd'],
                    ['Raj Kumar', 'raj@example.com', '+91-9876543210', 'Innovation Hub']
                ]
            ],
            'event' => [
                'filename' => 'sample-events.csv',
                'headers' => ['Event Name', 'Start Date', 'End Date', 'Location'],
                'rows' => [
                    ['Product Launch', '2026-03-15 10:00', '2026-03-15 12:00', 'Conference Center'],
                    ['Team Building', '2026-03-20 09:00', '2026-03-20 17:00', 'Outdoor Park'],
                    ['Training Workshop', '2026-04-01 14:00', '2026-04-01 16:00', 'Office Building'],
                    ['Client Meeting', '2026-04-05 11:00', '2026-04-05 13:00', 'Restaurant'],
                    ['Annual Conference', '2026-05-10 08:00', '2026-05-12 18:00', 'Convention Center']
                ]
            ],
            'paypal' => [
                'filename' => 'sample-paypal.csv',
                'headers' => ['PayPal Email', 'Amount', 'Description'],
                'rows' => [
                    ['payments@example.com', '50.00', 'Product Payment'],
                    ['donate@example.com', '25.00', 'Donation'],
                    ['shop@example.com', '100.00', 'Order #12345'],
                    ['service@example.com', '75.00', 'Service Fee'],
                    ['support@example.com', '30.00', 'Support Contribution']
                ]
            ],
            'payment' => [
                'filename' => 'sample-payment.csv',
                'headers' => ['UPI ID', 'Amount', 'Description'],
                'rows' => [
                    ['user@upi', '500', 'Product Purchase'],
                    ['merchant@upi', '1000', 'Service Payment'],
                    ['shop@upi', '250', 'Order Payment'],
                    ['donate@upi', '100', 'Donation'],
                    ['payment@upi', '750', 'Invoice Payment']
                ]
            ]
        ];
        
        // Get the sample data or default to URL
        $sample = $samples[$type] ?? $samples['url'];
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $sample['filename'] . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Create file pointer connected to output stream
        $output = fopen('php://output', 'w');
        
        // Write BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write headers
        fputcsv($output, $sample['headers']);
        
        // Write data rows
        foreach ($sample['rows'] as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Generate short code
     */
    private function generateShortCode(int $id): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($characters);
        $shortCode = '';
        
        while ($id > 0) {
            $shortCode = $characters[$id % $base] . $shortCode;
            $id = floor($id / $base);
        }
        
        return $shortCode ?: '0';
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
