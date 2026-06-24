<?php

class PaymentService {
    private $orderModel;
    private $upiModel;
    private $activityModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->upiModel = new UpiAccount();
        $this->activityModel = new Activity();
    }

    
    public function initiateOrder($amount, $payerType = 'intern', $payerId = null, $referenceId = null, $upiAccountId = null, $payerName = null, $payerNotes = null) {
        if ($upiAccountId) {
            $upi = $this->upiModel->getById($upiAccountId);
            if (!$upi) {
                return null;
            }
        } else {
            $activeUpis = $this->upiModel->getActive($payerType === 'client' ? 'client' : 'intern');
            
            if (empty($activeUpis)) {
                return null;
            }

            
            $upi = $activeUpis[0];
        }
        
        $this->upiModel->updateLastUsed($upi['id']);

        
        $orderNo = 'SAX' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $success = $this->orderModel->create(
            $orderNo, 
            $amount, 
            $upi['id'], 
            $ip, 
            $userAgent, 
            $payerType, 
            $payerId, 
            $referenceId,
            $payerName,
            $payerNotes
        );

        return $success ? $orderNo : null;
    }

    
    public function handleUtrSubmission($orderNo, $utr, $payerName, $payerNotes) {
        $success = $this->orderModel->submitUtr($orderNo, $utr, $payerName, $payerNotes);
        if ($success) {
            $this->activityModel->log(null, 'submit_utr', $orderNo, "User {$payerName} submitted UTR {$utr}");
            $this->syncPortalOnUtrSubmit($orderNo, $utr);
        }
        return $success;
    }

    
    public function processStatusChange($orderNo, $status, $adminNote, $adminId = null) {
        $order = $this->orderModel->getByOrderNo($orderNo);
        if (!$order) {
            return false;
        }

        $utr = $order['utr_ref'];
        $success = $this->orderModel->updateStatus($orderNo, $status, $adminNote, $utr);
        
        if ($success) {
            $actionType = $status . '_payment';
            $adminName = $_SESSION['admin_user'] ?? 'System';
            $this->activityModel->log(
                $adminId, 
                $actionType, 
                $orderNo, 
                "Admin {$adminName} changed order status to: {$status}. Note: {$adminNote}"
            );

            
            $this->syncPortalOnStatusUpdate($order, $status, $adminNote, $utr);
        }

        return $success;
    }

    
    public function getDeepLinks($upiId, $payeeName, $amount, $orderNo) {
        $formattedAmount = number_format($amount, 2, '.', '');
        
        
        $upiUri = "upi://pay?pa=" . rawurlencode($upiId) .
            "&pn=" . rawurlencode($payeeName) .
            "&am=" . $formattedAmount .
            "&cu=INR&mode=02&orgid=000000&mc=0000";

        
        $mobile = $upiId;
        if (strpos($mobile, '@') !== false) {
            $parts = explode('@', $mobile);
            if (preg_match('/^[0-9]{10}$/', $parts[0])) {
                $mobile = $parts[0];
            }
        }

        if (preg_match('/^[0-9]{10}$/', $mobile)) {
            $paytmUri = "paytmmp://cash_wallet?featuretype=sendmoney&recipient=" . $mobile . "&amount=" . $formattedAmount;
        } else {
            $paytmUri = "paytmmp://pay?pa=" . rawurlencode($upiId) .
                "&pn=" . rawurlencode($payeeName) .
                "&am=" . $formattedAmount .
                "&cu=INR&mode=02&orgid=000000";
        }

        
        $phonepeUri = "phonepe://pay?pa=" . rawurlencode($upiId) .
            "&pn=" . rawurlencode($payeeName) .
            "&am=" . $formattedAmount .
            "&cu=INR&mode=02";

        return [
            'upi' => $upiUri,
            'paytm' => $paytmUri,
            'phonepe' => $phonepeUri
        ];
    }

    
    private function syncPortalOnUtrSubmit($orderNo, $utr) {
        global $pdo;
        try {
            $order = $this->orderModel->getByOrderNo($orderNo);
            if ($order) {
                if ($order['payer_type'] === 'intern') {
                    $stmt = $pdo->prepare("
                        UPDATE intern_payments 
                        SET status = 'pending', reference_id = ? 
                        WHERE (reference_id = ? OR transaction_id = ? OR id = ?) AND status = 'pending'
                    ");
                    $stmt->execute([$utr, $orderNo, $orderNo, $order['reference_id']]);
                }
                
                $certStmt = $pdo->prepare("
                    UPDATE certificate_requests 
                    SET status = 'payment_verification', utr_ref = ? 
                    WHERE (transaction_id = ? OR id = ?) AND status = 'pending_payment'
                ");
                $certStmt->execute([$utr, $orderNo, $order['reference_id']]);
            }
        } catch (Exception $e) {
            error_log("Error in syncPortalOnUtrSubmit: " . $e->getMessage());
        }
    }

    
    private function syncPortalOnStatusUpdate($order, $status, $adminNote, $utr) {
        global $pdo;
        $orderNo = $order['order_no'];
        $amount = $order['amount'];

        if ($order['payer_type'] === 'intern' || $order['payer_type'] === 'cert_user') {
            try {
                $new_intern_status = ($status === 'approved') ? 'verified' : ($status === 'rejected' ? 'rejected' : 'pending');
                
                if ($order['payer_type'] === 'intern') {
                    $stmt = $pdo->prepare("
                        UPDATE intern_payments 
                        SET status = ?, notes = CONCAT(IFNULL(notes,''), ' | PayPanel: ', ?) 
                        WHERE (transaction_id = ? OR id = ?) AND status='pending'
                    ");
                    $stmt->execute([$new_intern_status, $adminNote, $orderNo, $order['reference_id']]);
                    
                    if ($utr) {
                        $pdo->prepare("UPDATE intern_payments SET status = ?, reference_id = ? WHERE (transaction_id = ? OR id = ?)")
                            ->execute([$new_intern_status, $utr, $orderNo, $order['reference_id']]);
                    }
                }
                
                
                if ($order['payer_type'] === 'intern' && $status === 'approved') {
                    $payInfo = $pdo->prepare("SELECT user_id, intern_id FROM intern_payments WHERE (transaction_id = ? OR id = ?) LIMIT 1");
                    $payInfo->execute([$orderNo, $order['reference_id']]);
                    $pRow = $payInfo->fetch();
                    
                    if ($pRow) {
                        $uid = $pRow['user_id'] ?: $pRow['intern_id'];
                        
                        $stmtIntern = $pdo->prepare("SELECT name, email, phone FROM intern_users WHERE id = ?");
                        $stmtIntern->execute([$uid]);
                        $intern = $stmtIntern->fetch();

                        if ($intern && !empty($intern['email'])) {
                            sendUnifiedTemplate($pdo, $intern['email'], $intern['phone'] ?? '', 'intern_fees_paid', [
                                'name' => $intern['name'],
                                'amount' => formatCurrency($amount),
                                'reference' => $utr ?: $orderNo
                            ], 'billing');
                        }

                        $pdo->prepare("
                            UPDATE intern_users 
                            SET fee_paid = (
                                SELECT COALESCE(SUM(amount),0) 
                                FROM intern_payments 
                                WHERE (user_id=? OR intern_id=?) AND (status='verified' OR status='approved')
                            ) 
                            WHERE id=?
                        ")->execute([$uid, $uid, $uid]);

                        creditReferralCommission($pdo, $uid, 'internship', $amount);

                        $chk = $pdo->prepare("
                            SELECT 
                                COALESCE(ir.fee_amount, iu.fee_amount, 0) as due, 
                                (SELECT COALESCE(SUM(amount),0) FROM intern_payments WHERE (user_id=? OR intern_id=?) AND (status='verified' OR status='approved')) as paid 
                            FROM intern_users iu 
                            LEFT JOIN internship_registrations ir ON ir.user_id=iu.id 
                            WHERE iu.id=?
                        ");
                        $chk->execute([$uid, $uid, $uid]);
                        $feeInfo = $chk->fetch();
                        if ($feeInfo && (float)$feeInfo['paid'] >= (float)$feeInfo['due'] && (float)$feeInfo['due'] > 0) {
                            $pdo->prepare("UPDATE intern_users SET status='active' WHERE id=?")->execute([$uid]);
                        }
                    }
                }
                
                
                $certCheck = $pdo->prepare("SELECT id, intern_id, user_type FROM certificate_requests WHERE (transaction_id = ? OR id = ?) LIMIT 1");
                $certCheck->execute([$orderNo, $order['reference_id']]);
                $certReq = $certCheck->fetch();
                
                if ($certReq) {
                    if ($status === 'approved') {
                        $certNo = autoGenerateCertificate($pdo, $certReq['intern_id'], false, $certReq['id']);
                        if ($certNo) {
                            $pdfPath = 'uploads/certificates/cert_' . $certNo . '.pdf';
                            $pdo->prepare("
                                UPDATE certificate_requests 
                                SET status = 'approved_issued', certificate_no = ?, pdf_path = ?, utr_ref = ?, verified_at = NOW() 
                                WHERE id = ?
                            ")->execute([$certNo, $pdfPath, $utr ?: $order['utr_ref'], $certReq['id']]);
                        } else {
                            $pdo->prepare("
                                UPDATE certificate_requests 
                                SET status = 'paid_processing', utr_ref = ?, verified_at = NOW() 
                                WHERE id = ?
                            ")->execute([$utr ?: $order['utr_ref'], $certReq['id']]);
                        }
                        creditReferralCommission($pdo, $certReq['intern_id'], 'certificate', $amount, $certReq['id']);
                    } elseif ($status === 'rejected') {
                        $pdo->prepare("UPDATE certificate_requests SET status = 'rejected', admin_notes = ?, utr_ref = ? WHERE id = ?")
                            ->execute([$adminNote, $utr ?: $order['utr_ref'], $certReq['id']]);
                    } else {
                        $certStatus = ($utr ?: $order['utr_ref']) ? 'payment_verification' : 'pending_payment';
                        $pdo->prepare("UPDATE certificate_requests SET status = ?, admin_notes = ?, utr_ref = ? WHERE id = ?")
                            ->execute([$certStatus, $adminNote, $utr ?: $order['utr_ref'], $certReq['id']]);
                    }
                } else {
                    if ($status === 'approved') {
                        $pdo->prepare("UPDATE certificate_requests SET status = 'paid_processing', utr_ref = ?, verified_at = NOW() WHERE (transaction_id = ? OR id = ?)")
                            ->execute([$utr ?: $order['utr_ref'], $orderNo, $order['reference_id']]);
                    } elseif ($status === 'rejected') {
                        $pdo->prepare("UPDATE certificate_requests SET status = 'rejected', admin_notes = ?, utr_ref = ? WHERE (transaction_id = ? OR id = ?)")
                            ->execute([$adminNote, $utr ?: $order['utr_ref'], $orderNo, $order['reference_id']]);
                    } else {
                        $certStatus = ($utr ?: $order['utr_ref']) ? 'payment_verification' : 'pending_payment';
                        $pdo->prepare("UPDATE certificate_requests SET status = ?, admin_notes = ?, utr_ref = ? WHERE (transaction_id = ? OR id = ?)")
                            ->execute([$certStatus, $adminNote, $utr ?: $order['utr_ref'], $orderNo, $order['reference_id']]);
                    }
                }
            } catch (Exception $le) {
                error_log("Error syncing intern/certificate updates: " . $le->getMessage());
            }
        } elseif ($order['payer_type'] === 'client') {
            try {
                if ($status === 'approved') {
                    $invInfo = $pdo->prepare("SELECT client_id, total, due_date FROM invoices WHERE id = ?");
                    $invInfo->execute([$order['reference_id']]);
                    $inv = $invInfo->fetch();
                    if ($inv) {
                        $cId = $inv['client_id'];
                        $pdo->prepare("
                            INSERT INTO payments (invoice_id, client_id, amount, payment_date, payment_method, reference_id, notes) 
                            VALUES (?, ?, ?, CURDATE(), 'UPI', ?, ?)
                        ")->execute([$order['reference_id'], $cId, $amount, $utr ?: $orderNo, 'Auto-verified via Portal']);
                        
                        $paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = ?");
                        $paidStmt->execute([$order['reference_id']]);
                        $totalPaid = (float)$paidStmt->fetchColumn();

                        if ($totalPaid >= (float)$inv['total']) {
                            $pdo->prepare("UPDATE invoices SET status = 'paid', updated_at = NOW() WHERE id = ?")
                                ->execute([$order['reference_id']]);
                        } else {
                            $newStatus = ($inv['due_date'] && strtotime($inv['due_date']) < strtotime(date('Y-m-d'))) ? 'overdue' : 'pending';
                            $pdo->prepare("UPDATE invoices SET status = ?, updated_at = NOW() WHERE id = ?")
                                ->execute([$newStatus, $order['reference_id']]);
                        }
                    }
                } else {
                    $invInfo = $pdo->prepare("SELECT total, due_date FROM invoices WHERE id = ?");
                    $invInfo->execute([$order['reference_id']]);
                    $inv = $invInfo->fetch();
                    if ($inv) {
                        $paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = ?");
                        $paidStmt->execute([$order['reference_id']]);
                        $totalPaid = (float)$paidStmt->fetchColumn();

                        if ($totalPaid >= (float)$inv['total']) {
                            $pdo->prepare("UPDATE invoices SET status = 'paid', updated_at = NOW() WHERE id = ?")
                                ->execute([$order['reference_id']]);
                        } else {
                            $newStatus = ($inv['due_date'] && strtotime($inv['due_date']) < strtotime(date('Y-m-d'))) ? 'overdue' : 'pending';
                            $pdo->prepare("UPDATE invoices SET status = ?, updated_at = NOW() WHERE id = ?")
                                ->execute([$newStatus, $order['reference_id']]);
                        }
                    }
                }
            } catch (Exception $le) {
                error_log("Error syncing client invoice payment updates: " . $le->getMessage());
            }
        }
    }
}
