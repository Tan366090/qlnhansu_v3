<?php
class TextPreprocessor {
    public function preprocess($text) {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        return preg_split('/\s+/', trim($text));
    }
} 