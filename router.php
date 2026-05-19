<?php
$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root = __DIR__;

// Serve static assets directly (css, js, fonts, images, etc.)
if ($uri !== '/' && file_exists($root . $uri) && !is_dir($root . $uri)) {
    return false;
}

// Map clean URLs to directory index files
$candidates = [
    $root . $uri . '/index.php',
    $root . rtrim($uri, '/') . '/index.php',
    $root . $uri . '.php',
];

foreach ($candidates as $file) {
    if (file_exists($file)) {
        // Adjust cwd so relative requires in the page work
        chdir(dirname($file));
        include $file;
        return;
    }
}

// Nothing matched — serve 404
http_response_code(404);
chdir($root);
include $root . '/404.php';
