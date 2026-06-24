<?php

class TransactionController extends Controller {
    private $orderModel;
    private $paymentService;

    public function __construct() {
        AuthMiddleware::check();
        
        $this->orderModel = new Order();
        $this->paymentService = new PaymentService();
    }

    
    public function index() {
        $search = clean($_GET['search'] ?? '');
        $statusFilter = clean($_GET['status'] ?? '');

        
        $orders = $this->orderModel->getFilteredList($statusFilter ?: null, $search ?: null, 60);
        
        
        $duplicates = $this->orderModel->getDuplicates();

        
        $userHistories = [];
        foreach ($orders as $order) {
            $history = $this->orderModel->getUserHistory(
                $order['payer_type'], 
                $order['payer_id'], 
                $order['user_ip'], 
                $order['payer_name']
            );

            $approved = 0;
            foreach ($history as $h) {
                if ($h['status'] === 'approved') {
                    $approved++;
                }
            }

            $userHistories[$order['order_no']] = [
                'approved' => $approved,
                'total' => count($history)
            ];
        }

        $this->render('admin/transactions/index', [
            'title' => 'Verification Workspace',
            'activePage' => 'transactions',
            'orders' => $orders,
            'duplicates' => $duplicates,
            'userHistories' => $userHistories,
            'search' => $search,
            'statusFilter' => $statusFilter
        ], 'admin_layout');
    }

    
    public function takeAction() {
        $orderNo = clean($_POST['order'] ?? '');
        $action = clean($_POST['action'] ?? '');
        $note = clean($_POST['note'] ?? '');
        $utrOverride = clean($_POST['utr'] ?? '');

        if (empty($orderNo) || !in_array($action, ['approved', 'rejected', 'on_hold', 'under_review'])) {
            $this->json(false, ['message' => 'Missing transaction ID or action parameters.']);
        }

        
        $order = $this->orderModel->getByOrderNo($orderNo);
        if (!$order) {
            $this->json(false, ['message' => 'Transaction order could not be located.']);
        }

        
        if (!empty($utrOverride) && $utrOverride !== $order['utr_ref']) {
            if (!preg_match('/^\d{12}$/', $utrOverride)) {
                $this->json(false, ['message' => 'UTR override reference must be exactly 12 digits.']);
            }
            $this->orderModel->submitUtr($orderNo, $utrOverride, $order['payer_name'], $order['payer_notes']);
        }

        $adminId = $_SESSION['admin_id'] ?? null;
        $success = $this->paymentService->processStatusChange($orderNo, $action, $note, $adminId);

        if ($success) {
            $this->json(true, ['message' => 'Status updated successfully.']);
        } else {
            $this->json(false, ['message' => 'Unable to modify transaction status details.']);
        }
    }
}
