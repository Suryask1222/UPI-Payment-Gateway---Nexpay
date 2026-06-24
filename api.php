<?php

require_once __DIR__ . '/config/bootstrap.php';

$action = $_REQUEST['action'] ?? '';

$adminActions = ['admin_update_status', 'admin_upi_crud', 'get_stats'];
if (in_array($action, $adminActions)) {
    if (!isset($_SESSION['admin_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

switch ($action) {
    case 'create_order':
        $amount = (float)($_POST['amount'] ?? 0);
        $payerType = clean($_POST['payer_type'] ?? 'intern');
        $payerId = (int)($_POST['payer_id'] ?? 0);
        $referenceId = clean($_POST['reference_id'] ?? '');

        if ($amount <= 0) {
            jsonResponse(false, ['message' => 'Invalid amount']);
        }

        $paymentService = new PaymentService();
        $orderNo = $paymentService->initiateOrder($amount, $payerType, $payerId ?: null, $referenceId ?: null);
        
        if ($orderNo) {
            jsonResponse(true, ['order_no' => $orderNo]);
        } else {
            jsonResponse(false, ['message' => 'No active UPI ID found']);
        }
        break;

    case 'status':
    case 'get_status':
        
        $_GET['order'] = $_GET['order'] ?? $_GET['order_no'] ?? '';
        $controller = new PaymentController();
        $controller->status();
        break;

    case 'submit_utr':
        $_POST['order'] = $_POST['order'] ?? $_POST['order_no'] ?? '';
        $controller = new PaymentController();
        $controller->submitUtr();
        break;

    case 'admin_update_status':
        $controller = new TransactionController();
        $controller->takeAction();
        break;

    case 'admin_upi_crud':
        $op = $_POST['op'] ?? '';
        $controller = new UpiController();
        if ($op === 'add') {
            $controller->add();
        } elseif ($op === 'toggle') {
            $controller->toggle();
        } elseif ($op === 'delete') {
            $controller->delete();
        } else {
            jsonResponse(false, ['message' => 'Invalid operation']);
        }
        break;

    case 'get_stats':
        $analyticsService = new AnalyticsService();
        $stats = $analyticsService->getSummary();
        jsonResponse(true, ['stats' => $stats]);
        break;

    default:
        jsonResponse(false, ['message' => 'Invalid action']);
        break;
}
