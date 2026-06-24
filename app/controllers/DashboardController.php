<?php

class DashboardController extends Controller {
    private $analyticsService;
    private $activityModel;

    public function __construct() {
        
        AuthMiddleware::check();
        
        $this->analyticsService = new AnalyticsService();
        $this->activityModel = new Activity();
    }

    
    public function index() {
        
        $stats = $this->analyticsService->getSummary();
        
        
        $chart = $this->analyticsService->getDailyTrendCoordinates(600, 200);
        
        
        $activities = $this->activityModel->getLatest(20);

        $this->render('dashboard/index', [
            'title' => 'Dashboard Overview',
            'activePage' => 'dashboard',
            'stats' => $stats,
            'chart' => $chart,
            'activities' => $activities
        ], 'admin_layout');
    }
}
