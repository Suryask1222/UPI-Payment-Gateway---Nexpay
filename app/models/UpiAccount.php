<?php

class UpiAccount extends Model {
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM pay_upi_accounts ORDER BY sort_order ASC, created_at DESC");
        return $stmt->fetchAll();
    }

    
    public function getActive($purpose = 'all') {
        $stmt = $this->db->prepare("
            SELECT * FROM pay_upi_accounts 
            WHERE active = 1 AND (purpose = ? OR purpose = 'all') 
            ORDER BY is_default DESC, last_used_at ASC
        ");
        $stmt->execute([$purpose]);
        return $stmt->fetchAll();
    }

    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM pay_upi_accounts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    
    public function add($upiId, $payeeName, $purpose = 'all') {
        try {
            $stmt = $this->db->prepare("INSERT INTO pay_upi_accounts (upi_id, payee_name, purpose) VALUES (?, ?, ?)");
            return $stmt->execute([$upiId, $payeeName, $purpose]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    
    public function edit($id, $upiId, $payeeName, $purpose = 'all') {
        try {
            $stmt = $this->db->prepare("UPDATE pay_upi_accounts SET upi_id = ?, payee_name = ?, purpose = ? WHERE id = ?");
            return $stmt->execute([$upiId, $payeeName, $purpose, $id]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    
    public function toggle($id, $active) {
        $stmt = $this->db->prepare("UPDATE pay_upi_accounts SET active = ? WHERE id = ?");
        return $stmt->execute([$active, $id]);
    }

    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM pay_upi_accounts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    
    public function setDefault($id) {
        $this->db->beginTransaction();
        try {
            $this->db->exec("UPDATE pay_upi_accounts SET is_default = 0");
            $stmt = $this->db->prepare("UPDATE pay_upi_accounts SET is_default = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to set default UPI: " . $e->getMessage());
            return false;
        }
    }

    
    public function updateLastUsed($id) {
        $stmt = $this->db->prepare("UPDATE pay_upi_accounts SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$id]);
    }

    
    public function getStats() {
        $stmt = $this->db->query("
            SELECT 
                u.id, 
                u.upi_id, 
                u.payee_name, 
                u.active, 
                u.is_default, 
                u.purpose,
                u.last_used_at,
                COUNT(o.id) as tx_count,
                COALESCE(SUM(CASE WHEN o.status = 'approved' THEN o.amount ELSE 0 END), 0) as total_revenue
            FROM pay_upi_accounts u
            LEFT JOIN pay_orders o ON o.upi_account_id = u.id
            GROUP BY u.id
            ORDER BY u.is_default DESC, tx_count DESC
        ");
        return $stmt->fetchAll();
    }
}
