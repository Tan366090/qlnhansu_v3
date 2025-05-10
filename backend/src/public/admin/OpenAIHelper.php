<?php
class OpenAIHelper {
    private static $chatHistory = [];
    
    public static function ask($prompt) {
        $apiKey = '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';
        
        // Add the new message to chat history
        self::$chatHistory[] = ['role' => 'user', 'content' => $prompt];
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => self::$chatHistory,
            'max_tokens' => 1000, // Increased token limit
            'temperature' => 0.7, // Add some creativity
            'presence_penalty' => 0.6, // Encourage diverse responses
            'frequency_penalty' => 0.3 // Reduce repetition
        ];

        try {
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $result = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new Exception('Curl error: ' . curl_error($ch));
            }
            
            curl_close($ch);
            
            $response = json_decode($result, true);
            
            if (isset($response['error'])) {
                throw new Exception('OpenAI API error: ' . $response['error']['message']);
            }
            
            if (!isset($response['choices'][0]['message']['content'])) {
                throw new Exception('Invalid response format from OpenAI API');
            }
            
            // Add assistant's response to chat history
            self::$chatHistory[] = [
                'role' => 'assistant',
                'content' => $response['choices'][0]['message']['content']
            ];
            
            // Keep only last 10 messages to maintain context without using too much memory
            if (count(self::$chatHistory) > 10) {
                self::$chatHistory = array_slice(self::$chatHistory, -10);
            }
            
            return $response['choices'][0]['message']['content'];
            
        } catch (Exception $e) {
            error_log('OpenAI API Error: ' . $e->getMessage());
            return 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }
    
    public static function clearHistory() {
        self::$chatHistory = [];
    }
} 