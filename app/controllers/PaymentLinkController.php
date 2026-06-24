<?php
// Handles generation and management of manual payment checkout links

class PaymentLinkController extends Controller {
    private $orderModel;
    private $upiModel;
    private $paymentService;
    private $activityModel;

    public function __construct() {
        AuthMiddleware::check();

        $this->orderModel = new Order();
        $this->upiModel = new UpiAccount();
        $this->paymentService = new PaymentService();
        $this->activityModel = new Activity();
    }

    // Render the payment links workspace and listing
    public function index() {
        $search = clean($_GET['search'] ?? '');
        $statusFilter = clean($_GET['status'] ?? '');

        $orders = $this->orderModel->getFilteredList($statusFilter ?: null, $search ?: null, 60);
        $upiAccounts = $this->upiModel->getAll();

        $this->render('admin/links/index', [
            'title' => 'Payment Links',
            'activePage' => 'payment-links',
            'orders' => $orders,
            'upiAccounts' => $upiAccounts,
            'search' => $search,
            'statusFilter' => $statusFilter
        ], 'admin_layout');
    }

    // Create a new order and generate its checkout URL
    public function generate() {
        $amount = (float)($_POST['amount'] ?? 0);
        $payerType = clean($_POST['payer_type'] ?? 'intern');
        $payerId = (int)($_POST['payer_id'] ?? 0);
        $referenceId = clean($_POST['reference_id'] ?? '');
        $upiAccountId = (int)($_POST['upi_account_id'] ?? 0);
        $payerName = clean($_POST['payer_name'] ?? '');
        $payerNotes = clean($_POST['payer_notes'] ?? '');

        if ($amount <= 0) {
            $this->json(false, ['message' => 'Please enter a valid amount greater than zero.']);
        }

        if (!in_array($payerType, ['intern', 'cert_user', 'client'])) {
            $this->json(false, ['message' => 'Invalid payer type selected.']);
        }

        // Initialize the payment session
        $orderNo = $this->paymentService->initiateOrder(
            $amount,
            $payerType,
            $payerId ?: null,
            $referenceId ?: null,
            $upiAccountId ?: null,
            $payerName ?: null,
            $payerNotes ?: null
        );

        if ($orderNo) {
            $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $paymentUrl = $proto . '://' . $host . BASE_URL . '/pay.php?order=' . $orderNo;

            // Log action in audit history
            $adminId = $_SESSION['admin_id'] ?? null;
            $adminName = $_SESSION['admin_user'] ?? 'Admin';
            $this->activityModel->log(
                $adminId,
                'generate_payment_link',
                $orderNo,
                "Admin {$adminName} generated a payment link of amount " . formatCurrency($amount) . " for Payer: " . ($payerName ?: 'N/A')
            );

            $this->json(true, [
                'order_no' => $orderNo,
                'payment_url' => $paymentUrl
            ]);
        } else {
            $this->json(false, ['message' => 'Failed to generate payment link. Ensure active UPI IDs are configured.']);
        }
    }

    // Safely delete a pending, unused payment link
    public function delete() {
        $orderNo = clean($_POST['order_no'] ?? '');

        if (empty($orderNo)) {
            $this->json(false, ['message' => 'Missing transaction ID.']);
        }

        $order = $this->orderModel->getByOrderNo($orderNo);
        if (!$order) {
            $this->json(false, ['message' => 'Transaction record not found.']);
        }

        if ($order['status'] !== 'pending' || !empty($order['utr_ref'])) {
            $this->json(false, ['message' => 'Only pending payment links with no submitted UTR can be deleted.']);
        }

        $success = $this->orderModel->deletePending($orderNo);
        if ($success) {
            $adminId = $_SESSION['admin_id'] ?? null;
            $adminName = $_SESSION['admin_user'] ?? 'Admin';
            $this->activityModel->log(
                $adminId,
                'delete_payment_link',
                $orderNo,
                "Admin {$adminName} deleted payment link {$orderNo}"
            );

            $this->json(true);
        } else {
            $this->json(false, ['message' => 'Unable to delete the payment link.']);
        }
    }
}
