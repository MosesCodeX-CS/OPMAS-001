<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Telemetry;
use App\Services\TelemetryInterpreter;

$rows = Telemetry::with('definition.versions')->orderBy('collector_timestamp','desc')->limit(20)->get();
$interpreter = new TelemetryInterpreter();

foreach ($rows as $row) {
    $out = $interpreter->interpret($row);
    $cts = $row->collector_timestamp;
    if (is_object($cts) && method_exists($cts, 'toDateTimeString')) {
        $cts = $cts->toDateTimeString();
    }
    echo json_encode([
        'telemetry_id' => $row->id,
        'register_definition_id' => $row->register_definition_id,
        'collector_timestamp' => $cts,
        'interpretation' => $out,
    ], JSON_PRETTY_PRINT) . PHP_EOL;
}
