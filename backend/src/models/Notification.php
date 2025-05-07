<?php

namespace App\Models;

use CodeIgniter\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'title', 'message', 'type', 'read', 'data'];

    public static function getUserNotifications($userId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('notifications');
        
        $builder->where('user_id', $userId);
        $builder->orderBy('created_at', 'DESC');
        $builder->limit(50);
        
        return $builder->get()->getResultArray();
    }

    public function markAsRead()
    {
        $this->read = 1;
        $this->read_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    public static function create($userId, $title, $message, $type = 'info', $data = [])
    {
        $db = \Config\Database::connect();
        $builder = $db->table('notifications');
        
        $notification = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $builder->insert($notification);
    }

    public static function getUnreadCount($userId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('notifications');
        
        $builder->where('user_id', $userId);
        $builder->where('read', 0);
        
        return $builder->countAllResults();
    }
} 