<?php

namespace App\Models;

use App\Core\Model;

class Equipment extends Model
{
    protected $table = 'equipment';
    protected $fillable = [
        'name',
        'description',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'warranty_period',
        'status',
        'location',
        'assigned_to',
        'assigned_date',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'location');
    }

    public function getAvailableEquipment()
    {
        return $this->where('status', 'available')
                   ->whereNull('assigned_to')
                   ->get();
    }

    public function getAssignedEquipment()
    {
        return $this->whereNotNull('assigned_to')
                   ->where('status', 'assigned')
                   ->with('user')
                   ->get();
    }

    public function getDepartmentEquipment($departmentId)
    {
        return $this->where('location', $departmentId)
                   ->with('user')
                   ->get();
    }

    public function getUserEquipment($userId)
    {
        return $this->where('assigned_to', $userId)
                   ->where('status', 'assigned')
                   ->get();
    }

    public function assignEquipment($equipmentId, $userId, $departmentId)
    {
        $equipment = $this->find($equipmentId);
        if (!$equipment) {
            return ['success' => false, 'message' => 'Equipment not found'];
        }

        if ($equipment->status !== 'available') {
            return ['success' => false, 'message' => 'Equipment is not available'];
        }

        $equipment->assigned_to = $userId;
        $equipment->location = $departmentId;
        $equipment->status = 'assigned';
        $equipment->assigned_date = date('Y-m-d H:i:s');

        if ($equipment->save()) {
            return ['success' => true, 'message' => 'Equipment assigned successfully'];
        }

        return ['success' => false, 'message' => 'Failed to assign equipment'];
    }

    public function returnEquipment($equipmentId)
    {
        $equipment = $this->find($equipmentId);
        if (!$equipment) {
            return ['success' => false, 'message' => 'Equipment not found'];
        }

        $equipment->assigned_to = null;
        $equipment->status = 'available';
        $equipment->assigned_date = null;

        if ($equipment->save()) {
            return ['success' => true, 'message' => 'Equipment returned successfully'];
        }

        return ['success' => false, 'message' => 'Failed to return equipment'];
    }

    public function getEquipmentStatistics()
    {
        return [
            'total_equipment' => $this->count(),
            'available_equipment' => $this->where('status', 'available')->count(),
            'assigned_equipment' => $this->where('status', 'assigned')->count(),
            'maintenance_equipment' => $this->where('status', 'maintenance')->count(),
            'total_value' => $this->sum('purchase_cost')
        ];
    }

    public function updateStatus($equipmentId, $status)
    {
        $equipment = $this->find($equipmentId);
        if (!$equipment) {
            return ['success' => false, 'message' => 'Equipment not found'];
        }

        $equipment->status = $status;
        if ($equipment->save()) {
            return ['success' => true, 'message' => 'Status updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update status'];
    }
} 