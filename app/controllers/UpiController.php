<?php

class UpiController extends Controller {
    private $upiModel;
    private $activityModel;

    public function __construct() {
        AuthMiddleware::check();
        
        $this->upiModel = new UpiAccount();
        $this->activityModel = new Activity();
    }

    
    public function index() {
        
        $accounts = $this->upiModel->getStats();
        
        $this->render('admin/upi/index', [
            'title' => 'UPI Account Shuffling Pool',
            'activePage' => 'upi',
            'accounts' => $accounts
        ], 'admin_layout');
    }

    
    public function add() {
        $upiId = clean($_POST['upi_id'] ?? '');
        $payeeName = clean($_POST['payee'] ?? '');
        $purpose = clean($_POST['purpose'] ?? 'all');

        if (empty($upiId) || empty($payeeName)) {
            $this->json(false, ['message' => 'Both UPI address and payee name are required fields.']);
        }

        if (strpos($upiId, '@') === false) {
            $this->json(false, ['message' => 'Please enter a valid format for UPI ID (must contain @).']);
        }

        $success = $this->upiModel->add($upiId, $payeeName, $purpose);
        if ($success) {
            $adminId = $_SESSION['admin_id'] ?? null;
            $this->activityModel->log($adminId, 'add_upi', null, "Added UPI ID: {$upiId} (Payee: {$payeeName}, Pool: {$purpose})");
            $this->json(true);
        } else {
            $this->json(false, ['message' => 'UPI ID address already exists in pool records.']);
        }
    }

    
    public function edit() {
        $id = (int)($_POST['id'] ?? 0);
        $upiId = clean($_POST['upi_id'] ?? '');
        $payeeName = clean($_POST['payee'] ?? '');
        $purpose = clean($_POST['purpose'] ?? 'all');

        if ($id <= 0 || empty($upiId) || empty($payeeName)) {
            $this->json(false, ['message' => 'Missing account ID or details parameters.']);
        }

        $success = $this->upiModel->edit($id, $upiId, $payeeName, $purpose);
        if ($success) {
            $adminId = $_SESSION['admin_id'] ?? null;
            $this->activityModel->log($adminId, 'edit_upi', null, "Modified UPI Account ID {$id}: {$upiId}");
            $this->json(true);
        } else {
            $this->json(false, ['message' => 'Failed to save modifications. Please verify details.']);
        }
    }

    
    public function toggle() {
        $id = (int)($_POST['id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);

        if ($id <= 0) {
            $this->json(false, ['message' => 'Missing target UPI ID.']);
        }

        $success = $this->upiModel->toggle($id, $active);
        if ($success) {
            $adminId = $_SESSION['admin_id'] ?? null;
            $actionWord = $active ? 'Enabled' : 'Disabled';
            $this->activityModel->log($adminId, 'toggle_upi', null, "{$actionWord} UPI Account ID {$id}");
            $this->json(true);
        } else {
            $this->json(false, ['message' => 'Status toggle update failed.']);
        }
    }

    
    public function delete() {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(false, ['message' => 'Missing target UPI ID.']);
        }

        $success = $this->upiModel->delete($id);
        if ($success) {
            $adminId = $_SESSION['admin_id'] ?? null;
            $this->activityModel->log($adminId, 'delete_upi', null, "Deleted UPI Account ID {$id}");
            $this->json(true);
        } else {
            $this->json(false, ['message' => 'Failed to delete. UPI account is reference linked by orders.']);
        }
    }

    
    public function setDefault() {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(false, ['message' => 'Missing target UPI ID.']);
        }

        $success = $this->upiModel->setDefault($id);
        if ($success) {
            $adminId = $_SESSION['admin_id'] ?? null;
            $this->activityModel->log($adminId, 'set_default_upi', null, "Designated UPI Account ID {$id} as system default");
            $this->json(true);
        } else {
            $this->json(false, ['message' => 'Failed to mark as system default.']);
        }
    }
}
