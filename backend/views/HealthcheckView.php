<?php

declare(strict_types=1);

final class HealthcheckView
{
    public function render(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data);
    }
}