<?php
class ContextManager {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getContext($userId) {
        $stmt = $this->conn->prepare("SELECT context FROM chat_context WHERE user_id=?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $stmt->bind_result($context);
        if ($stmt->fetch()) {
            return json_decode($context, true);
        }
        return [];
    }
    public function setContext($userId, $context) {
        $contextJson = json_encode($context);
        $stmt = $this->conn->prepare("REPLACE INTO chat_context (user_id, context) VALUES (?, ?)");
        $stmt->bind_param("ss", $userId, $contextJson);
        $stmt->execute();
    }
}