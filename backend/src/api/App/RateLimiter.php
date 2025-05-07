<?php
class RateLimiter {
    private $limit;
    private $window;
    private $cacheDir;

    public function __construct($limit, $window) {
        $this->limit = $limit;
        $this->window = $window;
        $this->cacheDir = __DIR__ . '/../cache/rate_limit';
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function canMakeRequest() {
        $ip = $this->getClientIp();
        $cacheFile = $this->cacheDir . '/' . md5($ip) . '.json';
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data['timestamp'] > time() - $this->window) {
                if ($data['count'] >= $this->limit) {
                    return false;
                }
                $data['count']++;
            } else {
                $data = [
                    'timestamp' => time(),
                    'count' => 1
                ];
            }
        } else {
            $data = [
                'timestamp' => time(),
                'count' => 1
            ];
        }
        
        file_put_contents($cacheFile, json_encode($data));
        return true;
    }

    private function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function resetLimit($ip = null) {
        if ($ip === null) {
            $ip = $this->getClientIp();
        }
        $cacheFile = $this->cacheDir . '/' . md5($ip) . '.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
} 