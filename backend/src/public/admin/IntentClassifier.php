<?php
class IntentClassifier {
    public function classify($tokens) {
        // Rule-based: có thể mở rộng hoặc thay thế bằng AI/LLM
        if (in_array('lương', $tokens)) return 'salary';
        if (in_array('phòng', $tokens)) return 'department';
        if (in_array('nghỉ', $tokens)) return 'leave';
        if (in_array('đào tạo', $tokens)) return 'training';
        return 'general';
    }
} 