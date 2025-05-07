<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'type', 'action', 'description', 'ip_address', 'user_agent'];

    public static function getActivities($filter = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('audit_logs al');
        
        $builder->select('al.*, u.name as user_name, d.name as department_name');
        $builder->join('users u', 'al.user_id = u.id');
        $builder->join('user_profiles up', 'u.id = up.user_id');
        $builder->join('departments d', 'up.department_id = d.id');
        
        if ($filter) {
            $builder->where('al.type', $filter);
        }
        
        $builder->orderBy('al.created_at', 'DESC');
        $builder->limit(50);
        
        return $builder->get()->getResultArray();
    }

    public static function log($userId, $type, $action, $description = '')
    {
        $db = \Config\Database::connect();
        $builder = $db->table('audit_logs');
        
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $builder->insert($data);
    }
} 