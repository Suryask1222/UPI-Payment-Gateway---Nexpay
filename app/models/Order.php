<?php

class Order extends Model {
    
    public function getByOrderNo($orderNo) {
        $stmt = $this->db->prepare("
            SELECT o.*, u.upi_id, u.payee_name 
            FROM pay_orders o 
            JOIN pay_upi_accounts u ON o.upi_account_id = u.id 
            WHERE o.order_no = ?
        ");
        $stmt->execute([$orderNo]);
        return $stmt->fetch() ?: null;
    }

    
    public function create($orderNo, $amount, $upiAccountId, $ip, $userAgent, $payerType = 'intern', $payerId = null, $referenceId = null, $payerName = null, $payerNotes = null) {
        $stmt = $this->db->prepare("
            INSERT INTO pay_orders (order_no, amount, upi_account_id, user_ip, user_agent, payer_type, payer_id, reference_id, payer_name, payer_notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$orderNo, $amount, $upiAccountId, $ip, $userAgent, $payerType, $payerId, $referenceId, $payerName, $payerNotes]);
    }

    
    public function submitUtr($orderNo, $utr, $payerName, $payerNotes) {
        $stmt = $this->db->prepare("
            UPDATE pay_orders 
            SET utr_ref = ?, payer_name = ?, payer_notes = ?, status = 'pending' 
            WHERE order_no = ?
        ");
        return $stmt->execute([$utr, $payerName, $payerNotes, $orderNo]);
    }

    
    public function updateStatus($orderNo, $status, $adminNote, $utr = null) {
        if ($utr !== null) {
            $stmt = $this->db->prepare("
                UPDATE pay_orders 
                SET status = ?, admin_note = ?, utr_ref = ? 
                WHERE order_no = ?
            ");
            return $stmt->execute([$status, $adminNote, $utr, $orderNo]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE pay_orders 
                SET status = ?, admin_note = ? 
                WHERE order_no = ?
            ");
            return $stmt->execute([$status, $adminNote, $orderNo]);
        }
    }

    
    public function getFilteredList($status = null, $search = null, $limit = 50) {
        $query = "
            SELECT o.*, u.upi_id, u.payee_name 
            FROM pay_orders o 
            JOIN pay_upi_accounts u ON o.upi_account_id = u.id 
            WHERE 1=1
        ";
        $params = [];

        if ($status) {
            $query .= " AND o.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $query .= " AND (o.order_no LIKE ? OR o.utr_ref LIKE ? OR o.payer_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY o.created_at DESC LIMIT " . (int)$limit;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    
    public function getDuplicates() {
        $stmt = $this->db->query("
            SELECT utr_ref, COUNT(*) as cnt 
            FROM pay_orders 
            WHERE utr_ref IS NOT NULL AND utr_ref != '' AND status != 'expired'
            GROUP BY utr_ref 
            HAVING cnt > 1
        ");
        return array_column($stmt->fetchAll() ?: [], 'utr_ref');
    }

    
    public function getUserHistory($payerType, $payerId, $ip, $payerName) {
        $clauses = [];
        $params = [];

        if ($payerId && $payerType) {
            $clauses[] = "(o.payer_id = ? AND o.payer_type = ?)";
            $params[] = $payerId;
            $params[] = $payerType;
        }
        
        if ($payerName && strlen(trim($payerName)) > 2) {
            $clauses[] = "(o.payer_name LIKE ?)";
            $params[] = "%" . trim($payerName) . "%";
        }
        
        if ($ip) {
            $clauses[] = "(o.user_ip = ?)";
            $params[] = $ip;
        }

        if (empty($clauses)) {
            return [];
        }

        $query = "
            SELECT o.*, u.upi_id 
            FROM pay_orders o
            JOIN pay_upi_accounts u ON o.upi_account_id = u.id
            WHERE " . implode(' OR ', $clauses) . "
            ORDER BY o.created_at DESC 
            LIMIT 5
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    
    public function getOverallStats() {
        $stats = [
            'total_revenue' => 0.0,
            'today_revenue' => 0.0,
            'pending_count' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'success_rate' => 0.0,
            'active_upi_count' => 0
        ];

        
        $stmt = $this->db->query("
            SELECT 
                status, 
                COUNT(*) as count, 
                SUM(amount) as total 
            FROM pay_orders 
            GROUP BY status
        ");
        $rows = $stmt->fetchAll() ?: [];
        
        $totalApproved = 0;
        $totalRejected = 0;
        $totalHold = 0;
        
        foreach ($rows as $row) {
            if ($row['status'] === 'approved') {
                $stats['total_revenue'] = (float)$row['total'];
                $stats['approved_count'] = (int)$row['count'];
                $totalApproved = (int)$row['count'];
            } elseif ($row['status'] === 'pending' || $row['status'] === 'under_review') {
                $stats['pending_count'] += (int)$row['count'];
            } elseif ($row['status'] === 'rejected') {
                $stats['rejected_count'] = (int)$row['count'];
                $totalRejected = (int)$row['count'];
            } elseif ($row['status'] === 'on_hold') {
                $totalHold = (int)$row['count'];
            }
        }

        
        $stmtToday = $this->db->query("
            SELECT SUM(amount) as total 
            FROM pay_orders 
            WHERE status = 'approved' AND DATE(created_at) = CURDATE()
        ");
        $stats['today_revenue'] = (float)$stmtToday->fetchColumn();

        
        $totalVerifications = $totalApproved + $totalRejected + $totalHold;
        if ($totalVerifications > 0) {
            $stats['success_rate'] = round(($totalApproved / $totalVerifications) * 100, 1);
        }

        
        $stmtUpi = $this->db->query("SELECT COUNT(*) FROM pay_upi_accounts WHERE active = 1");
        $stats['active_upi_count'] = (int)$stmtUpi->fetchColumn();

        return $stats;
    }

    
    public function getDailyRevenueTrend($days = 7) {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, SUM(amount) as total 
            FROM pay_orders 
            WHERE status = 'approved' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([(int)$days]);
        return $stmt->fetchAll() ?: [];
    }

    
    public function expirePendingSessions() {
        $stmt = $this->db->query("
            SELECT order_no FROM pay_orders 
            WHERE status = 'pending' AND utr_ref IS NULL 
              AND TIMESTAMPDIFF(SECOND, created_at, NOW()) > 900
        ");
        $expired = $stmt->fetchAll() ?: [];
        
        if (!empty($expired)) {
            $this->db->beginTransaction();
            try {
                foreach ($expired as $order) {
                    
                    $up = $this->db->prepare("UPDATE pay_orders SET status = 'expired' WHERE order_no = ?");
                    $up->execute([$order['order_no']]);
                    
                    
                    $upCert = $this->db->prepare("
                        UPDATE certificate_requests 
                        SET status = 'pending_payment' 
                        WHERE transaction_id = ? AND status IN ('pending_payment', 'payment_verification')
                    ");
                    $upCert->execute([$order['order_no']]);
                }
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Failed to bulk expire sessions: " . $e->getMessage());
            }
        }
        return count($expired);
    }

    
    public function deletePending($orderNo) {
        $stmt = $this->db->prepare("DELETE FROM pay_orders WHERE order_no = ? AND status = 'pending' AND utr_ref IS NULL");
        return $stmt->execute([$orderNo]);
    }
}
