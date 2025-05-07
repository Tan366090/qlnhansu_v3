<?php
header("Content-Security-Policy: default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://code.jquery.com https://cdn.jsdelivr.net; " .
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; " .
    "img-src 'self' data: https:; " .
    "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
    "connect-src 'self' ws://localhost:* http://localhost:* http://127.0.0.1:* https://cdn.jsdelivr.net; " .
    "frame-src 'self'; " .
    "object-src 'none'"); 