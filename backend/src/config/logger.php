<?php

return [
    'default' => 'file',
    
    'channels' => [
        'file' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => 'debug',
        ],
        
        'error' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../logs/error.log',
            'level' => 'error',
        ],
        
        'database' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../logs/database.log',
            'level' => 'error',
        ],
        
        'validation' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../logs/validation.log',
            'level' => 'warning',
        ],
        
        'system' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../logs/system.log',
            'level' => 'error',
        ],
    ],
]; 