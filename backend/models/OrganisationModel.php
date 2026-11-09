<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

// Accès à la table organisation (requêtes SQL uniquement).
final class OrganisationModel
{
    public function __construct(
        private PDO $pdo,
    ) {}

    // Crée une organisation et renvoie son id.
    public function creer(string $nom): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO organisation (nom) VALUES (:nom) RETURNING id'
        );
        $stmt->execute(['nom' => $nom]);

        return (int) $stmt->fetchColumn();
    }

    public function existe(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM organisation WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() !== false;
    }
}
