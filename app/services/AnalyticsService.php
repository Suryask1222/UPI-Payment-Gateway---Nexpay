<?php

class AnalyticsService {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    
    public function getSummary() {
        return $this->orderModel->getOverallStats();
    }

    
    public function getDailyTrendCoordinates($width = 600, $height = 200) {
        $trend = $this->orderModel->getDailyRevenueTrend(7) ?: [];
        
        
        $paddedTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateString = date('Y-m-d', strtotime("-$i days"));
            $found = false;
            foreach ($trend as $row) {
                if ($row['date'] === $dateString) {
                    $paddedTrend[] = ['date' => $dateString, 'total' => (float)$row['total']];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $paddedTrend[] = ['date' => $dateString, 'total' => 0.0];
            }
        }
        $trend = $paddedTrend;

        
        $maxTotal = 0;
        foreach ($trend as $row) {
            if ($row['total'] > $maxTotal) {
                $maxTotal = $row['total'];
            }
        }
        if ($maxTotal <= 0) {
            $maxTotal = 1000.0;
        }

        $points = [];
        $polylineString = "";
        $circles = [];
        $labelSpacing = $width - 80;
        $totalCount = count($trend);

        foreach ($trend as $index => $row) {
            $x = $totalCount > 1 ? ($index / ($totalCount - 1)) * $labelSpacing + 40 : $width / 2;
            $y = $height - (($row['total'] / $maxTotal) * ($height - 80)) - 40;
            
            $points[] = [
                'x' => $x,
                'y' => $y,
                'total' => $row['total'],
                'date' => date('d M', strtotime($row['date'])),
                'day' => date('D', strtotime($row['date']))
            ];
            $polylineString .= "{$x},{$y} ";
            
            $circles[] = [
                'cx' => $x,
                'cy' => $y,
                'value' => '₹' . number_format($row['total'], 0),
                'label' => date('d M', strtotime($row['date']))
            ];
        }

        $areaString = "";
        if (!empty($points)) {
            $startX = $points[0]['x'];
            $endX = $points[count($points) - 1]['x'];
            $bottomY = $height - 30;
            $areaString = "{$startX},{$bottomY} " . trim($polylineString) . " {$endX},{$bottomY}";
        }

        return [
            'points' => $points,
            'polyline' => trim($polylineString),
            'area' => trim($areaString),
            'circles' => $circles,
            'max' => $maxTotal,
            'data' => $trend
        ];
    }
}
