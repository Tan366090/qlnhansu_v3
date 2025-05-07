<?php

namespace App\Libraries;

class AI
{
    public function getHRTrends()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('audit_logs');
        
        $builder->select('DATE_FORMAT(created_at, "%Y-%m") as month');
        $builder->select('COUNT(CASE WHEN type = "turnover" THEN 1 END) as turnover_count');
        $builder->select('COUNT(CASE WHEN type = "hiring" THEN 1 END) as hiring_count');
        $builder->where('type IN ("turnover", "hiring")');
        $builder->groupBy('month');
        $builder->orderBy('month', 'DESC');
        $builder->limit(12);
        
        $data = $builder->get()->getResultArray();
        
        $labels = array_column($data, 'month');
        $turnoverRates = array_column($data, 'turnover_count');
        $hiringRates = array_column($data, 'hiring_count');
        
        return [
            'labels' => $labels,
            'turnoverRates' => $turnoverRates,
            'hiringRates' => $hiringRates
        ];
    }

    public function getSentiment()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('evaluations');
        
        $builder->select('COUNT(CASE WHEN sentiment = "positive" THEN 1 END) as positive');
        $builder->select('COUNT(CASE WHEN sentiment = "neutral" THEN 1 END) as neutral');
        $builder->select('COUNT(CASE WHEN sentiment = "negative" THEN 1 END) as negative');
        $builder->where('created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        
        $data = $builder->get()->getRowArray();
        
        $total = array_sum($data);
        if ($total > 0) {
            $data['positive'] = round(($data['positive'] / $total) * 100);
            $data['neutral'] = round(($data['neutral'] / $total) * 100);
            $data['negative'] = round(($data['negative'] / $total) * 100);
        }
        
        return $data;
    }

    public function getPredictions()
    {
        $db = \Config\Database::connect();
        
        // Get turnover prediction
        $turnoverBuilder = $db->table('audit_logs');
        $turnoverBuilder->select('COUNT(*) as count');
        $turnoverBuilder->where('type', 'turnover');
        $turnoverBuilder->where('created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        $turnoverCount = $turnoverBuilder->get()->getRow()->count;
        
        // Get hiring prediction
        $hiringBuilder = $db->table('audit_logs');
        $hiringBuilder->select('COUNT(*) as count');
        $hiringBuilder->where('type', 'hiring');
        $hiringBuilder->where('created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        $hiringCount = $hiringBuilder->get()->getRow()->count;
        
        // Get sentiment prediction
        $sentimentBuilder = $db->table('evaluations');
        $sentimentBuilder->select('AVG(CASE 
            WHEN sentiment = "positive" THEN 1 
            WHEN sentiment = "neutral" THEN 0 
            ELSE -1 
        END) as score');
        $sentimentBuilder->where('created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        $sentimentScore = $sentimentBuilder->get()->getRow()->score;
        
        $predictions = [];
        
        // Turnover prediction
        if ($turnoverCount > 5) {
            $predictions[] = [
                'type' => 'warning',
                'title' => 'High Turnover Rate',
                'description' => 'The turnover rate has increased significantly in the last 30 days.',
                'confidence' => 85,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        // Hiring prediction
        if ($hiringCount > 10) {
            $predictions[] = [
                'type' => 'success',
                'title' => 'Active Hiring',
                'description' => 'The company is actively hiring new employees.',
                'confidence' => 90,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        // Sentiment prediction
        if ($sentimentScore < -0.5) {
            $predictions[] = [
                'type' => 'danger',
                'title' => 'Low Employee Sentiment',
                'description' => 'Employee sentiment has been negative in the last 30 days.',
                'confidence' => 75,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        return $predictions;
    }
} 