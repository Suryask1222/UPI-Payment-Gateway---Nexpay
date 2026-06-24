<?php

class PaymentController extends Controller {
    private $orderModel;
    private $paymentService;

    public function __construct() {
        $this->orderModel = new Order();
        $this->paymentService = new PaymentService();
    }

    
    public function pay() {
        $this->orderModel->expirePendingSessions();

        $orderNo = clean($_GET['order'] ?? '');
        if (empty($orderNo)) {
            $this->render('payments/direct_pay', [
                'title' => 'Direct Payment Portal'
            ], 'payment_layout');
            return;
        }

        $order = $this->orderModel->getByOrderNo($orderNo);
        if (!$order) {
            die("Error: Transaction order not found.");
        }

        if ($order['status'] === 'approved') {
            $this->redirect(BASE_URL . '/success.php?order=' . $orderNo);
        }

        $links = $this->paymentService->getDeepLinks(
            $order['upi_id'], 
            $order['payee_name'], 
            $order['amount'], 
            $order['order_no']
        );

        $mobile = $order['upi_id'];
        if (strpos($mobile, '@') !== false) {
            $parts = explode('@', $mobile);
            if (preg_match('/^[0-9]{10}$/', $parts[0])) {
                $mobile = $parts[0];
            }
        }

        $this->render('payments/pay', [
            'title' => 'Complete Payment',
            'order' => $order,
            'upiUri' => $links['upi'],
            'paytmUri' => $links['paytm'],
            'phonepeUri' => $links['phonepe'],
            'mobile' => $mobile
        ], 'payment_layout');
    }

    public function initiateDirectPay() {
        $amount = (float)($_POST['amount'] ?? 0);
        $name = clean($_POST['payer_name'] ?? '');
        $payerType = clean($_POST['payer_type'] ?? 'intern');
        $reference = clean($_POST['reference_id'] ?? '');
        $notes = clean($_POST['payer_notes'] ?? '');

        if ($amount <= 0 || empty($name)) {
            die("Error: Payer Name and a valid Amount are required fields.");
        }

        $orderNo = $this->paymentService->initiateOrder($amount, $payerType, null, $reference ?: null, null, $name, $notes);

        if ($orderNo) {
            $this->redirect(BASE_URL . '/pay.php?order=' . $orderNo);
        } else {
            die("Error: No active UPI accounts are available to receive payments at the moment.");
        }
    }

    
    public function submitUtr() {
        $orderNo = clean($_POST['order'] ?? '');
        $utr = clean($_POST['utr'] ?? '');
        $name = clean($_POST['name'] ?? '');
        $notes = clean($_POST['notes'] ?? '');

        if (empty($orderNo) || empty($utr) || empty($name)) {
            $this->json(false, ['message' => 'Missing transaction ID, UTR number, or Payer Name.']);
        }

        if (!preg_match('/^\d{12}$/', $utr)) {
            $this->json(false, ['message' => 'UTR reference must be exactly 12 digits.']);
        }

        if (strlen($name) < 3) {
            $this->json(false, ['message' => 'Please enter your full name (minimum 3 characters).']);
        }

        $success = $this->paymentService->handleUtrSubmission($orderNo, $utr, $name, $notes);
        if ($success) {
            $this->json(true, ['message' => 'Verification details recorded successfully.']);
        } else {
            $this->json(false, ['message' => 'Unable to record payment details. Please contact desk support.']);
        }
    }

    
    public function status() {
        $orderNo = clean($_GET['order'] ?? '');
        if (empty($orderNo)) {
            $this->json(false, ['status' => 'error', 'message' => 'Missing transaction ID.']);
        }

        $order = $this->orderModel->getByOrderNo($orderNo);
        if ($order) {
            $this->json(true, ['status' => $order['status']]);
        } else {
            $this->json(true, ['status' => 'not_found']);
        }
    }

    
    public function success() {
        global $pdo;

        $orderNo = clean($_GET['order'] ?? '');
        if (empty($orderNo)) {
            $this->redirect(BASE_URL . '/');
        }

        $order = $this->orderModel->getByOrderNo($orderNo);
        if (!$order) {
            die("Error: Transaction record not found.");
        }

        
        $certData = null;
        if ($order['status'] === 'approved') {
            $userType = $order['payer_type'] ?: 'intern';
            
            try {
                $certStmt = $pdo->prepare("
                    SELECT * FROM intern_certificates 
                    WHERE intern_id = ? AND user_type = ? 
                    ORDER BY issued_at DESC LIMIT 1
                ");
                $certStmt->execute([$order['payer_id'], $userType]);
                $certData = $certStmt->fetch();

                if (!$certData && !empty($order['reference_id'])) {
                    
                    $reqStmt = $pdo->prepare("
                        SELECT student_name as intern_name, domain, certificate_no, pdf_path, verified_at as issued_at 
                        FROM certificate_requests WHERE id = ?
                    ");
                    $reqStmt->execute([$order['reference_id']]);
                    $certData = $reqStmt->fetch() ?: null;
                }
            } catch (PDOException $e) {
                error_log("Payment success certificate queries ignored (tables may not exist): " . $e->getMessage());
            }
        }

        $this->render('payments/success', [
            'title' => 'Payment Successful',
            'order' => $order,
            'certData' => $certData,
            'pdo' => $pdo
        ], 'payment_layout');
    }
}
