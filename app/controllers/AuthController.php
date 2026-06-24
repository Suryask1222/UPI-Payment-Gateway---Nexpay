<?php

class AuthController extends Controller {
    private $adminModel;
    private $activityModel;

    public function __construct() {
        $this->adminModel = new Admin();
        $this->activityModel = new Activity();
    }

    
    public function loginView() {
        if (isset($_SESSION['admin_id'])) {
            $this->redirect(BASE_URL . '/admin_dashboard.php');
        }
        $this->render('auth/login', ['title' => 'Admin Login'], 'payment_layout');
    }

    
    public function login() {
        if (isset($_SESSION['admin_id'])) {
            $this->redirect(BASE_URL . '/admin_dashboard.php');
        }

        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $admin = $this->adminModel->getByUsername($username);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            
            
            $this->activityModel->log($admin['id'], 'login', null, "Admin {$username} logged in successfully");

            $this->redirect(BASE_URL . '/admin_dashboard.php');
        } else {
            $this->render('auth/login', [
                'title' => 'Admin Login', 
                'error' => 'Invalid administrative credentials.'
            ], 'payment_layout');
        }
    }

    
    public function logout() {
        if (isset($_SESSION['admin_id'])) {
            $this->activityModel->log($_SESSION['admin_id'], 'logout', null, "Admin {$_SESSION['admin_user']} logged out");
        }

        
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        $this->redirect(BASE_URL . '/admin_login.php');
    }
}
