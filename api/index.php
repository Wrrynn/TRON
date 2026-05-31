<?php

// Entry point untuk Vercel Serverless PHP
// Redirect semua request ke public/index.php Laravel

define('LARAVEL_START', microtime(true));

// Override $_SERVER agar path-nya benar
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

// Serve static files langsung jika ada di public/
$staticFile = __DIR__ . '/../public' . $uri;
if ($uri !== '/' && file_exists($staticFile) && !is_dir($staticFile)) {
    return false; // Biarkan Vercel serve file statis
}

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
