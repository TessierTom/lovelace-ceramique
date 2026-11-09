<?php
// Vue du healthcheck – formatte la réponse JSON.

declare(strict_types=1);

namespace App\Views;

final class HealthcheckView
{
    public function success(int $db): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'db'     => (bool) $db,
        ]);
    }

    public function error(string $message): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
        ]);
    }
}