<?php

namespace App\Models;

use CodeIgniter\Model;

class Achievement extends Model
{
    protected $table = 'achievements';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'title', 'description', 'points', 'completed', 'completed_at'];

    public static function getLeaderboard($timeRange = 'month')
    {
        $db = \Config\Database::connect();
        $builder = $db->table('achievements a');
        
        $builder->select('u.id, u.name, up.avatar, d.name as department, COUNT(a.id) as points');
        $builder->join('users u', 'a.user_id = u.id');
        $builder->join('user_profiles up', 'u.id = up.user_id');
        $builder->join('departments d', 'up.department_id = d.id');
        
        switch ($timeRange) {
            case 'week':
                $builder->where('a.completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
                break;
            case 'month':
                $builder->where('a.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
                break;
            case 'all':
                break;
        }
        
        $builder->where('a.completed', 1);
        $builder->groupBy('u.id');
        $builder->orderBy('points', 'DESC');
        $builder->limit(10);
        
        return $builder->get()->getResultArray();
    }

    public static function getUserAchievements($userId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('achievements');
        
        $builder->where('user_id', $userId);
        $builder->orderBy('completed_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    public static function getUserProgress($userId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('achievements');
        
        $builder->select('COUNT(CASE WHEN completed = 1 THEN 1 END) as completed');
        $builder->select('COUNT(*) as total');
        $builder->select('SUM(points) as total_points');
        $builder->where('user_id', $userId);
        
        return $builder->get()->getRowArray();
    }
} 