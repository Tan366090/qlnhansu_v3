<?php

namespace App\Models;

use CodeIgniter\Model;

class Session extends Model
{
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'token', 'device', 'version', 'duration', 'ip_address', 'user_agent'];

    public static function getStats($timeRange = 'month')
    {
        $db = \Config\Database::connect();
        $builder = $db->table('sessions');
        
        switch ($timeRange) {
            case 'week':
                $builder->where('created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
                break;
            case 'month':
                $builder->where('created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
                break;
            case 'year':
                $builder->where('created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)');
                break;
        }
        
        $builder->select('DATE_FORMAT(created_at, "%Y-%m-%d") as date');
        $builder->select('COUNT(*) as sessions');
        $builder->select('AVG(duration) as avg_duration');
        $builder->groupBy('date');
        $builder->orderBy('date', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    public static function getVersionStats()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('sessions');
        
        $builder->select('version');
        $builder->select('COUNT(*) as users');
        $builder->groupBy('version');
        $builder->orderBy('version', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    public static function create($userId, $device, $version)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('sessions');
        
        $session = [
            'user_id' => $userId,
            'token' => bin2hex(random_bytes(32)),
            'device' => $device,
            'version' => $version,
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $builder->insert($session);
    }

    public function updateDuration($duration)
    {
        $this->duration = $duration;
        return $this->save();
    }
} 