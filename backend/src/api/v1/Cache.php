<?php
class Cache {
    private $cacheDir;
    private $ttl;

    public function __construct($ttl = 300) {
        $this->ttl = $ttl;
        $this->cacheDir = __DIR__ . '/../cache/responses';
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key) {
        $cacheFile = $this->getCacheFile($key);
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data['expires'] > time()) {
                return $data['content'];
            }
            unlink($cacheFile);
        }
        
        return null;
    }

    public function set($key, $content) {
        $cacheFile = $this->getCacheFile($key);
        $data = [
            'expires' => time() + $this->ttl,
            'content' => $content
        ];
        
        file_put_contents($cacheFile, json_encode($data));
    }

    public function delete($key) {
        $cacheFile = $this->getCacheFile($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    public function clear() {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getCacheFile($key) {
        return $this->cacheDir . '/' . md5($key) . '.json';
    }
} 