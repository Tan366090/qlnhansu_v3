<?php

namespace App\Controllers;

use App\Models\Certificate;
use App\Models\User;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class CertificateController extends Controller
{
    private $certificateModel;
    private $userModel;

    public function __construct()
    {
        $this->certificateModel = new Certificate();
        $this->userModel = new User();
    }

    public function index(Request $request, Response $response)
    {
        $certificates = $this->certificateModel->all();
        return $response->json($certificates);
    }

    public function show(Request $request, Response $response, $id)
    {
        $certificate = $this->certificateModel->find($id);
        if (!$certificate) {
            return $response->status(404)->json(['message' => 'Certificate not found']);
        }
        return $response->json($certificate);
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getBody();
        
        // Validate required fields
        if (empty($data['user_id']) || empty($data['title']) || empty($data['issuing_organization']) || empty($data['issue_date'])) {
            return $response->status(400)->json(['message' => 'Missing required fields']);
        }

        // Check if user exists
        $user = $this->userModel->find($data['user_id']);
        if (!$user) {
            return $response->status(404)->json(['message' => 'User not found']);
        }

        // Create new certificate
        $certificate = $this->certificateModel->create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'issuing_organization' => $data['issuing_organization'],
            'issue_date' => $data['issue_date'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'certificate_number' => $data['certificate_number'] ?? null,
            'credential_url' => $data['credential_url'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'status' => 'active'
        ]);

        return $response->status(201)->json($certificate);
    }

    public function update(Request $request, Response $response, $id)
    {
        $certificate = $this->certificateModel->find($id);
        if (!$certificate) {
            return $response->status(404)->json(['message' => 'Certificate not found']);
        }

        $data = $request->getBody();
        $certificate->update($data);

        return $response->json($certificate);
    }

    public function delete(Request $request, Response $response, $id)
    {
        $certificate = $this->certificateModel->find($id);
        if (!$certificate) {
            return $response->status(404)->json(['message' => 'Certificate not found']);
        }

        $certificate->delete();
        return $response->json(['message' => 'Certificate deleted successfully']);
    }

    public function getEmployeeCertificates(Request $request, Response $response, $userId)
    {
        $certificates = $this->certificateModel->getEmployeeCertificates($userId);
        return $response->json($certificates);
    }

    public function getDepartmentCertificates(Request $request, Response $response, $departmentId)
    {
        $certificates = $this->certificateModel->getDepartmentCertificates($departmentId);
        return $response->json($certificates);
    }

    public function getExpiringCertificates(Request $request, Response $response)
    {
        $days = $request->get('days', 30);
        $certificates = $this->certificateModel->getExpiringCertificates($days);
        return $response->json($certificates);
    }

    public function updateStatus(Request $request, Response $response)
    {
        $updated = $this->certificateModel->updateCertificateStatus();
        return $response->json(['message' => "Updated $updated certificates"]);
    }

    public function getStatistics(Request $request, Response $response)
    {
        $statistics = $this->certificateModel->getCertificateStatistics();
        return $response->json($statistics);
    }

    public function search(Request $request, Response $response)
    {
        $keyword = $request->get('keyword');
        if (empty($keyword)) {
            return $response->status(400)->json(['message' => 'Search keyword is required']);
        }

        $certificates = $this->certificateModel->searchCertificates($keyword);
        return $response->json($certificates);
    }

    public function upload(Request $request, Response $response)
    {
        $file = $request->file('file');
        if (!$file) {
            return $response->status(400)->json(['message' => 'No file uploaded']);
        }

        // Validate file
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileType, $allowedTypes)) {
            return $response->status(400)->json(['message' => 'Invalid file type']);
        }

        // Create upload directory if not exists
        $uploadDir = 'uploads/certificates/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $filename = uniqid() . '.' . $fileType;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return $response->status(500)->json(['message' => 'Failed to upload file']);
        }

        return $response->json([
            'message' => 'File uploaded successfully',
            'file_path' => $filepath
        ]);
    }
} 