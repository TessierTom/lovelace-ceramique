<?php

declare(strict_types=1);

final class HealthcheckModel
{
    public function checkDatabase(): int
    {
        // conexion à la base de données en utilisant PDO
        $pdo = require __DIR__ . '/../config/db.php';

        // Exécuter une requête pour vérifier la connexion

        $result = $pdo->query('SELECT 1')->fetchColumn();

        // Retourner le résultat comme entier
        return (int) $result;
    }
}