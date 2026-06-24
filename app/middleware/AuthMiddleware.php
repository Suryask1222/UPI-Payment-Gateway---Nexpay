<?php

class AuthMiddleware {
    
    public static function check() {
        if (!isset($_SESSION['admin_id'])) {
            $isApi = (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) || 
                     (isset($_GET['route']) && strpos($_GET['route'], 'api/') !== false);
            
            if ($isApi) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
                exit;
            } else {
                header("Location: " . BASE_URL . "/admin_login.php");
                exit;
            }
        }
    }
}
