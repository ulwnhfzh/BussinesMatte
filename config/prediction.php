<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Prediction Service
    |--------------------------------------------------------------------------
    |
    | URL service FastAPI dan batas waktu permintaan dari Laravel.
    | Nilai dapat diubah melalui file .env tanpa mengubah source code.
    |
    */

    'url' => env(
        'PREDICTION_API_URL',
        'http://127.0.0.1:8001/predict'
    ),

    'timeout' => (int) env('PREDICTION_API_TIMEOUT', 30),
];