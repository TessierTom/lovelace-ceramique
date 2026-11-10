<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

namespace App\Models;

use PDO;

final class HealthcheckModel
{
    private PDO $pdo;

    public function __construct()
    {
        // Connexion à PostgreSQL
        $this->pdo = require __DIR__ . '/../config/db.php';
    }

    // Test de la connexion à la base de données PostgreSQL.
    public function ping(): int
    {
        // Vérification de la base de données
        $result = $this->pdo->query('SELECT 1')->fetchColumn();

        return (int) $result;
    }
}
