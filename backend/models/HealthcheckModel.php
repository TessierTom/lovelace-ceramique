<?php

declare(strict_types=1);

final class HealthcheckModel
{
    public function checkDatabase(): int
    {
        // Obtener la conexión PostgreSQL
        $pdo = require __DIR__ . '/../config/db.php';

        // Ejecutar una consulta para comprobar la conexión
        $result = $pdo->query('SELECT 1')->fetchColumn();

        // Devolver el resultado como entero
        return (int) $result;
    }
}