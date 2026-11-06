<?php

declare(strict_types=1);

// Accès à la table utilisateur (requêtes SQL uniquement).
final class UtilisateurModel
{
    public function __construct(
        private PDO $pdo,
    ) {}

    // Crée un utilisateur et renvoie son id.
    public function creer(
        string $identifiant,
        string $mdpHash,
        string $email,
        string $nom,
        int $organisationId,
        string $role,
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateur (identifiant, mdp, email, nom, organisation_id, role)
             VALUES (:identifiant, :mdp, :email, :nom, :organisation_id, :role)
             RETURNING id'
        );
        $stmt->execute([
            'identifiant' => $identifiant,
            'mdp' => $mdpHash,
            'email' => $email,
            'nom' => $nom,
            'organisation_id' => $organisationId,
            'role' => $role,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function identifiantExiste(string $identifiant): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM utilisateur WHERE identifiant = :identifiant LIMIT 1'
        );
        $stmt->execute(['identifiant' => $identifiant]);

        return $stmt->fetchColumn() !== false;
    }

    public function emailExiste(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM utilisateur WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetchColumn() !== false;
    }
}
