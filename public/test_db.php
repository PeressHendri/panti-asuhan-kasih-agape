<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$logs = DB::table('cctv_activity_logs')->count();
echo "Count: " . $logs . "<br>";
$items = DB::table('cctv_activity_logs')->orderBy('created_at', 'desc')->limit(5)->get();
print_r($items);
