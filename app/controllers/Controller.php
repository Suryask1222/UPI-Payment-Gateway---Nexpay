<?php

class Controller {
    
    protected function render($view, $data = [], $layout = null) {
        
        extract($data);

        
        ob_start();
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "Error: View template '{$view}' not found at [{$viewPath}].";
        }
        $content = ob_get_clean();

        
        if ($layout) {
            $layoutPath = __DIR__ . '/../../views/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                echo "Error: Layout '{$layout}' not found at [{$layoutPath}]. Showing default page content:\n" . $content;
            }
        } else {
            echo $content;
        }
    }

    
    protected function json($success, $data = []) {
        jsonResponse($success, $data);
    }

    
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
}
