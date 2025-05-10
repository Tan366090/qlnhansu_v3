<?php
class OpenAIHelper {
    public static function ask($prompt) {
        $apiKey = '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';
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => 150
        ];
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result, true);
        return $response['choices'][0]['message']['content'] ?? '';
    }
} 