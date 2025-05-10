<?php
require_once 'TextPreprocessor.php';
require_once 'IntentClassifier.php';
require_once 'DataRetriever.php';
require_once 'ResponseGenerator.php';
require_once 'ContextManager.php';
// require_once 'OpenAIHelper.php'; // Nếu dùng OpenAI

class ChatEngine {
    private $conn;
    private $preprocessor;
    private $classifier;
    private $retriever;
    private $generator;
    private $contextManager;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->preprocessor = new TextPreprocessor();
        $this->classifier = new IntentClassifier();
        $this->retriever = new DataRetriever($conn);
        $this->generator = new ResponseGenerator();
        $this->contextManager = new ContextManager($conn);
    }

    public function chat($userId, $query) {
        // 1. Lấy context cũ (nếu cần)
        $context = $this->contextManager->getContext($userId);
        // 2. Tiền xử lý
        $tokens = $this->preprocessor->preprocess($query);
        // 3. Phân loại ý định
        $intent = $this->classifier->classify($tokens);
        // 4. Lấy dữ liệu
        $data = $this->retriever->getData($intent, $tokens);
        // 5. Sinh câu trả lời (có thể tích hợp AI/LLM ở đây)
        $response = $this->generator->generate($intent, $data, $query);
        // 6. Lưu context mới
        $this->contextManager->setContext($userId, ['last_intent' => $intent, 'last_query' => $query]);
        return $response;
    }
} 