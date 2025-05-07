<?php

namespace App\Models;

use App\Core\Model;

class TrainingRegistration extends Model
{
    protected $table = 'training_registrations';
    
    protected $fillable = [
        'training_id',
        'user_id',
        'status',
        'registered_at',
        'attendance_status',
        'feedback',
        'created_at',
        'updated_at'
    ];

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updateAttendance($status)
    {
        $this->attendance_status = $status;
        return $this->save();
    }

    public function submitFeedback($feedback)
    {
        $this->feedback = $feedback;
        return $this->save();
    }

    public function getTrainingRegistrationsByUser($userId)
    {
        return $this->where('user_id', $userId)
                   ->with('training')
                   ->orderBy('registered_at', 'DESC')
                   ->get();
    }

    public function getTrainingRegistrationsByTraining($trainingId)
    {
        return $this->where('training_id', $trainingId)
                   ->with('user')
                   ->orderBy('registered_at', 'ASC')
                   ->get();
    }

    public function getTrainingAttendance($trainingId)
    {
        return $this->where('training_id', $trainingId)
                   ->whereNotNull('attendance_status')
                   ->with('user')
                   ->get();
    }

    public function getTrainingFeedback($trainingId)
    {
        return $this->where('training_id', $trainingId)
                   ->whereNotNull('feedback')
                   ->with('user')
                   ->get();
    }
} 