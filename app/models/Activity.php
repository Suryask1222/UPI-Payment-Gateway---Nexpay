<?php

class Activity extends Model {
    
    public function log($adminId, $actionType, $orderNo = null, $details = null) {
        $stmt = $this->db->prepare("
            INSERT INTO pay_activities (admin_id, action_type, order_no, details) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$adminId, $actionType, $orderNo, $details]);
    }

    
    public function getLatest($limit = 30) {
        $stmt = $this->db->prepare("
            SELECT a.*, adm.username as admin_user 
            FROM pay_activities a 
            LEFT JOIN pay_admins adm ON a.admin_id = adm.id 
            ORDER BY a.created_at DESC 
            LIMIT ?
        ");
        
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }
}
