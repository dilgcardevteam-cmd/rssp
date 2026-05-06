<?php
$root = realpath(__DIR__ . '/../../..');
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$request = Illuminate\Http\Request::create('/export-pds?force_fpdi=1', 'GET', ['force_fpdi' => '1']);
$request->headers->set('User-Agent', 'Android');
Illuminate\Support\Facades\Auth::loginUsingId(23);
$controller = $app->make(App\Http\Controllers\Forms\ExportPDSController::class);
$response = $controller->exportPDS($request);
if (is_object($response) && method_exists($response, 'getTargetUrl')) {
    echo "redirect=" . $response->getTargetUrl() . PHP_EOL;
}
