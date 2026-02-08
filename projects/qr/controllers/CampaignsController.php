<?php
/**
 * Campaigns Controller
 * Handles QR code campaigns
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Projects\QR\Models\CampaignModel;

class CampaignsController
{
    private CampaignModel $model;
    
    public function __construct()
    {
        $this->model = new CampaignModel();
    }
    
    /**
     * Show campaigns page
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Get all campaigns for the user
        $campaigns = $this->model->getByUser($userId);
        
        $this->render('campaigns', [
            'title' => 'Campaigns',
            'user' => Auth::user(),
            'campaigns' => $campaigns
        ]);
    }
    
    /**
     * Show campaign details
     */
    public function view(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $campaignId = $_GET['id'] ?? null;
        
        if (!$campaignId) {
            header('Location: /projects/qr/campaigns');
            exit;
        }
        
        $campaign = $this->model->getById($campaignId, $userId);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found';
            header('Location: /projects/qr/campaigns');
            exit;
        }
        
        // Get QR codes for this campaign
        $qrCodes = $this->model->getQRCodes($campaignId, $userId);
        
        $this->render('campaign-view', [
            'title' => $campaign['name'],
            'user' => Auth::user(),
            'campaign' => $campaign,
            'qrCodes' => $qrCodes
        ]);
    }
    
    /**
     * Create new campaign
     */
    public function create(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];
            
            $campaignId = $this->model->create($userId, $data);
            
            if ($campaignId) {
                $_SESSION['success'] = 'Campaign created successfully';
                header('Location: /projects/qr/campaigns');
            } else {
                $_SESSION['error'] = 'Failed to create campaign';
                header('Location: /projects/qr/campaigns');
            }
            exit;
        }
        
        $this->render('campaign-form', [
            'title' => 'Create Campaign',
            'user' => Auth::user()
        ]);
    }
    
    /**
     * Edit campaign
     */
    public function edit(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $campaignId = $_GET['id'] ?? null;
        
        if (!$campaignId) {
            header('Location: /projects/qr/campaigns');
            exit;
        }
        
        $campaign = $this->model->getById($campaignId, $userId);
        
        if (!$campaign) {
            $_SESSION['error'] = 'Campaign not found';
            header('Location: /projects/qr/campaigns');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];
            
            if ($this->model->update($campaignId, $userId, $data)) {
                $_SESSION['success'] = 'Campaign updated successfully';
                header('Location: /projects/qr/campaigns');
            } else {
                $_SESSION['error'] = 'Failed to update campaign';
                header('Location: /projects/qr/campaigns');
            }
            exit;
        }
        
        $this->render('campaign-form', [
            'title' => 'Edit Campaign',
            'user' => Auth::user(),
            'campaign' => $campaign
        ]);
    }
    
    /**
     * Delete campaign
     */
    public function delete(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $campaignId = $_POST['id'] ?? null;
        
        if (!$campaignId) {
            echo json_encode(['success' => false, 'message' => 'Campaign ID required']);
            exit;
        }
        
        if ($this->model->delete($campaignId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Campaign deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete campaign']);
        }
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
