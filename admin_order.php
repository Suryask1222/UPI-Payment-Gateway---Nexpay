<?php

require_once __DIR__ . '/config/bootstrap.php';

$orderNo = clean($_GET['order'] ?? '');
header("Location: " . BASE_URL . "/admin_transactions.php?search=" . urlencode($orderNo));
exit;
