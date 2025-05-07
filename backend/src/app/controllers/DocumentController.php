<?php

namespace App\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Models\Department;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class DocumentController extends Controller
{
    private $documentModel;
    private $userModel;
    private $departmentModel;

    public function __construct()
    {
        $this->documentModel = new Document();
        $this->userModel = new User();
        $this->departmentModel = new Department();
    }

    public function index(Request $request, Response $response)
    {
        $documents = $this->documentModel->getRecentDocuments();
        return $response->json($documents);
    }

    public function show(Request $request, Response $response, $id)
    {
        $document = $this->documentModel->find($id);
        if (!$document) {
            return $response->status(404)->json(['message' => 'Document not found']);
        }
        return $response->json($document);
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getBody();
        
        // Validate required fields
        if (empty($data['title']) || empty($data['file_path']) || empty($data['file_type'])) {
            return $response->status(400)->json(['message' => 'Missing required fields']);
        }

        // Create new document
        $document = $this->documentModel->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size'] ?? 0,
            'category' => $data['category'] ?? 'general',
            'uploaded_by' => $data['uploaded_by'],
            'department_id' => $data['department_id'] ?? null,
            'is_public' => $data['is_public'] ?? false
        ]);

        return $response->status(201)->json($document);
    }

    public function update(Request $request, Response $response, $id)
    {
        $document = $this->documentModel->find($id);
        if (!$document) {
            return $response->status(404)->json(['message' => 'Document not found']);
        }

        $data = $request->getBody();
        $document->update($data);

        return $response->json($document);
    }

    public function delete(Request $request, Response $response, $id)
    {
        $document = $this->documentModel->find($id);
        if (!$document) {
            return $response->status(404)->json(['message' => 'Document not found']);
        }

        // Delete the file from storage
        if (file_exists($document->file_path)) {
            unlink($document->file_path);
        }

        $document->delete();
        return $response->json(['message' => 'Document deleted successfully']);
    }

    public function getPublicDocuments(Request $request, Response $response)
    {
        $documents = $this->documentModel->getPublicDocuments();
        return $response->json($documents);
    }

    public function getDepartmentDocuments(Request $request, Response $response, $departmentId)
    {
        $documents = $this->documentModel->getDepartmentDocuments($departmentId);
        return $response->json($documents);
    }

    public function getUserDocuments(Request $request, Response $response, $userId)
    {
        $documents = $this->documentModel->getUserDocuments($userId);
        return $response->json($documents);
    }

    public function getDocumentsByCategory(Request $request, Response $response, $category)
    {
        $documents = $this->documentModel->getDocumentsByCategory($category);
        return $response->json($documents);
    }

    public function getDocumentsByType(Request $request, Response $response)
    {
        $type = $request->getQuery('type');
        if (empty($type)) {
            return $response->status(400)->json(['message' => 'File type is required']);
        }

        $documents = $this->documentModel->getDocumentsByType($type);
        return $response->json($documents);
    }

    public function search(Request $request, Response $response)
    {
        $keyword = $request->getQuery('keyword');
        if (empty($keyword)) {
            return $response->status(400)->json(['message' => 'Search keyword is required']);
        }

        $documents = $this->documentModel->searchDocuments($keyword);
        return $response->json($documents);
    }

    public function upload(Request $request, Response $response)
    {
        $file = $request->getFile('file');
        if (!$file) {
            return $response->status(400)->json(['message' => 'No file uploaded']);
        }

        // Validate file type and size
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file->getExtension(), $allowedTypes)) {
            return $response->status(400)->json(['message' => 'Invalid file type']);
        }

        if ($file->getSize() > $maxSize) {
            return $response->status(400)->json(['message' => 'File size exceeds limit']);
        }

        // Create upload directory if it doesn't exist
        $uploadDir = 'uploads/documents/' . date('Y/m');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $filename = uniqid() . '.' . $file->getExtension();
        $filepath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if ($file->moveTo($filepath)) {
            return $response->json([
                'message' => 'File uploaded successfully',
                'file_path' => $filepath,
                'file_type' => $file->getExtension(),
                'file_size' => $file->getSize()
            ]);
        }

        return $response->status(500)->json(['message' => 'Failed to upload file']);
    }

    public function getStatistics(Request $request, Response $response)
    {
        $statistics = $this->documentModel->getDocumentStatistics();
        return $response->json($statistics);
    }
} 