<?php

namespace App\Controllers;

use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Models\User;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class TrainingController extends Controller
{
    private $trainingModel;
    private $registrationModel;
    private $userModel;

    public function __construct()
    {
        $this->trainingModel = new Training();
        $this->registrationModel = new TrainingRegistration();
        $this->userModel = new User();
    }

    public function index(Request $request, Response $response)
    {
        $limit = $request->get('limit', 10);
        $trainings = $this->trainingModel->getUpcomingTrainings($limit);
        return $response->json($trainings);
    }

    public function show(Request $request, Response $response, $id)
    {
        $training = $this->trainingModel->find($id);
        if (!$training) {
            return $response->status(404)->json(['message' => 'Training not found']);
        }
        return $response->json($training);
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getBody();
        
        // Validate required fields
        if (empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $response->status(400)->json(['message' => 'Missing required fields']);
        }

        // Create new training
        $training = $this->trainingModel->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'location' => $data['location'] ?? null,
            'trainer' => $data['trainer'] ?? null,
            'max_participants' => $data['max_participants'] ?? 0,
            'status' => 'planned',
            'created_by' => $request->user()->id
        ]);

        return $response->status(201)->json($training);
    }

    public function update(Request $request, Response $response, $id)
    {
        $training = $this->trainingModel->find($id);
        if (!$training) {
            return $response->status(404)->json(['message' => 'Training not found']);
        }

        $data = $request->getBody();
        $training->update($data);

        return $response->json($training);
    }

    public function delete(Request $request, Response $response, $id)
    {
        $training = $this->trainingModel->find($id);
        if (!$training) {
            return $response->status(404)->json(['message' => 'Training not found']);
        }

        $training->delete();
        return $response->json(['message' => 'Training deleted successfully']);
    }

    public function getUpcomingTrainings(Request $request, Response $response)
    {
        $limit = $request->get('limit', 10);
        $trainings = $this->trainingModel->getUpcomingTrainings($limit);
        return $response->json($trainings);
    }

    public function getPastTrainings(Request $request, Response $response)
    {
        $limit = $request->get('limit', 10);
        $trainings = $this->trainingModel->getPastTrainings($limit);
        return $response->json($trainings);
    }

    public function getDepartmentTrainings(Request $request, Response $response, $departmentId)
    {
        $trainings = $this->trainingModel->getTrainingByDepartment($departmentId);
        return $response->json($trainings);
    }

    public function getTrainingParticipants(Request $request, Response $response, $trainingId)
    {
        $participants = $this->trainingModel->getTrainingParticipants($trainingId);
        return $response->json($participants);
    }

    public function register(Request $request, Response $response, $trainingId)
    {
        $userId = $request->user()->id;
        $result = $this->trainingModel->registerUser($trainingId, $userId);
        
        if (!$result['success']) {
            return $response->status(400)->json(['message' => $result['message']]);
        }

        return $response->json(['message' => $result['message']]);
    }

    public function cancelRegistration(Request $request, Response $response, $trainingId)
    {
        $userId = $request->user()->id;
        $result = $this->trainingModel->cancelRegistration($trainingId, $userId);
        
        if (!$result['success']) {
            return $response->status(400)->json(['message' => $result['message']]);
        }

        return $response->json(['message' => $result['message']]);
    }

    public function updateAttendance(Request $request, Response $response, $registrationId)
    {
        $data = $request->getBody();
        if (empty($data['attendance_status'])) {
            return $response->status(400)->json(['message' => 'Attendance status is required']);
        }

        $registration = $this->registrationModel->find($registrationId);
        if (!$registration) {
            return $response->status(404)->json(['message' => 'Registration not found']);
        }

        $registration->updateAttendance($data['attendance_status']);
        return $response->json(['message' => 'Attendance updated successfully']);
    }

    public function submitFeedback(Request $request, Response $response, $registrationId)
    {
        $data = $request->getBody();
        if (empty($data['feedback'])) {
            return $response->status(400)->json(['message' => 'Feedback is required']);
        }

        $registration = $this->registrationModel->find($registrationId);
        if (!$registration) {
            return $response->status(404)->json(['message' => 'Registration not found']);
        }

        $registration->submitFeedback($data['feedback']);
        return $response->json(['message' => 'Feedback submitted successfully']);
    }

    public function getStatistics(Request $request, Response $response)
    {
        $statistics = $this->trainingModel->getTrainingStatistics();
        return $response->json($statistics);
    }
} 