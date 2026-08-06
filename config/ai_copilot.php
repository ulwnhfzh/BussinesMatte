<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Copilot
    |--------------------------------------------------------------------------
    |
    | Jika Gemini dinonaktifkan atau mengalami kegagalan, chatbot akan
    | kembali menggunakan jawaban berbasis aturan pada AICopilotService.
    |
    */

    'llm_enabled' => (bool) env(
        'AI_COPILOT_LLM_ENABLED',
        false
    ),

    'provider' => env(
        'AI_COPILOT_LLM_PROVIDER',
        'gemini'
    ),

    /*
    |--------------------------------------------------------------------------
    | Gemini API
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),

        'base_url' => env(
            'GEMINI_BASE_URL',
            'https://generativelanguage.googleapis.com/v1beta'
        ),

        'model' => env(
            'GEMINI_MODEL',
            'gemini-3.5-flash-lite'
        ),

        'timeout' => (int) env(
            'GEMINI_TIMEOUT',
            30
        ),

        'max_output_tokens' => (int) env(
            'GEMINI_MAX_OUTPUT_TOKENS',
            900
        ),

        'temperature' => (float) env(
            'GEMINI_TEMPERATURE',
            0.35
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Batas Konteks Bisnis
    |--------------------------------------------------------------------------
    |
    | Membatasi jumlah data yang dikirim ke Gemini agar request lebih ringan,
    | cepat, dan hemat kuota.
    |
    */

    'context' => [
        'max_products' => (int) env(
            'AI_COPILOT_MAX_CONTEXT_PRODUCTS',
            40
        ),

        'max_top_products' => 10,

        'max_restock_products' => 10,

        'history_messages' => 8,
    ],
];