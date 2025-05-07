<?php

namespace Tests\Traits;

trait AuthTrait
{
    protected $token = null;

    protected function login()
    {
        $ch = curl_init('http://localhost/QLNhanSu_version1/api/auth.php?action=login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'username' => 'admin',
            'password' => 'admin123'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Login failed with HTTP code: ' . $httpCode);
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['token'])) {
            throw new \Exception('Invalid login response');
        }

        $this->token = $data['token'];
    }

    protected function makeRequest($url, $method = 'GET', $data = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        
        $headers = [
            'Content-Type: application/json'
        ];
        
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new \Exception('Request failed with HTTP code: ' . $httpCode);
        }
        
        return json_decode($response, true);
    }

    protected function authenticate()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=login', 'POST', [
            'username' => 'admin',
            'password' => 'admin123'
        ]);
        
        if ($response['success']) {
            $this->token = $response['token'];
        } else {
            throw new \Exception('Authentication failed');
        }
    }
} 