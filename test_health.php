<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = \App\Models\User::first();
echo "Testing HTTP routes as user: " . ($user ? $user->email : 'No user') . "\n";

$routesToTest = [
    '/my-account/vehicle-management/waybill',
    '/my-account/goods-inspection/inspection',
];

$wb = \App\Models\Waybill::first();
if ($wb) {
    $routesToTest[] = '/my-account/vehicle-management/waybill/show/' . $wb->id;
    $routesToTest[] = '/my-account/vehicle-management/waybill/edit/' . $wb->id;
}

$gi = \App\Models\GoodsInspection::first();
if ($gi) {
    $routesToTest[] = '/my-account/goods-inspection/inspection/show/' . $gi->id;
    $routesToTest[] = '/my-account/goods-inspection/inspection/edit/' . $gi->id;
}

foreach ($routesToTest as $uri) {
    echo "Requesting GET $uri ... ";
    $request = \Illuminate\Http\Request::create($uri, 'GET');
    if ($user) {
        $app['auth']->guard()->setUser($user);
    }
    
    try {
        $response = $app->handle($request);
        $status = $response->getStatusCode();
        echo "Status: $status\n";
        if ($status >= 500) {
            echo "  -> Error response content preview:\n" . substr($response->getContent(), 0, 500) . "\n";
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}
