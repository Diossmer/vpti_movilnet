<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Rutas donde se aplicarán las reglas de CORS

    'allowed_methods' => ['*'], // Permitir todos los métodos (GET, POST, etc.)

    'allowed_origins' => ['http://localhost:5173','*'], // Cambia esto a tu dominio específico en producción

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization','*'], // Permitir todos los encabezados

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Permitir credenciales (cookies, etc.)

];
