<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Vercel Serverless — writable storage di /tmp
|--------------------------------------------------------------------------
| Vercel filesystem adalah read-only. Laravel butuh storage/ yang writable
| untuk cache, session, log, dan compiled views.
| Kita buat semua di /tmp yang writable.
*/
$tmpStorage   = '/tmp/storage';
$tmpBootstrap = '/tmp/bootstrap';

foreach ([
    $tmpStorage . '/app',
    $tmpStorage . '/app/public',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/logs',
    $tmpBootstrap . '/cache',
] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Override storage & bootstrap path ke /tmp
$app->useStoragePath($tmpStorage);

$kernel   = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request  = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
