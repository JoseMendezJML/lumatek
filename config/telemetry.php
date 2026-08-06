<?php

return [
    'mode' => env('TELEMETRY_MODE', 'simulation'),
    'poll_seconds' => (int) env('TELEMETRY_POLL_SECONDS', 5),
];
