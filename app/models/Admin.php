<?php

class Admin extends Model {
    
    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM pay_admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM pay_admins WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
