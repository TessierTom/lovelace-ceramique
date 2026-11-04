<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

final class RegisterModel
{
    private PDO $pdo;

    public function __construct()
    {
        // Connexion à PostgreSQL
        $this->pdo = require __DIR__ . '/../config/db.php';
    }

    // Test de la connexion à la base de données PostgreSQL.
    public function register(
        string $organisationNom,
        string $identifiant,
        string $email,
        string $nom,
        string $mdpClair
    ): array {
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/../config/db.php';

        // Identifiant déjà pris ?
        $stmt = $pdo->prepare('SELECT 1 FROM utilisateur WHERE identifiant = :identifiant');
        $stmt->execute(['identifiant' => $identifiant]);
        if ($stmt->fetchColumn() !== false) {
            throw new Exception('Identifiant déjà utilisé', 409);
        }

        // Email déjà pris ?
        $stmt = $pdo->prepare('SELECT 1 FROM utilisateur WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() !== false) {
            throw new Exception('Email déjà utilisé', 409);
        }

        // Hash bcrypt — jamais stocker le mot de passe en clair.
        $mdpHash = password_hash($mdpClair, PASSWORD_BCRYPT);

        if ($mdpHash === false) {
            throw new Exception('Service temporairement indisponible', 503);
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO organisation (nom)
                     VALUES (:nom)
                     RETURNING id'
            );
            $stmt->execute(['nom' => $organisationNom]);
            $organisationId = (int) $stmt->fetchColumn();

            $stmt = $pdo->prepare(
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
                'role' => 'Administrateur',
            ]);
            $utilisateurId = (int) $stmt->fetchColumn();

            $pdo->commit();

            return [
                'utilisateur_id' => $utilisateurId,
                'organisation_id' => $organisationId,
            ];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
