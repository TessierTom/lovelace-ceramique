<?php
declare(strict_types=1);

namespace App\Models;

final class UtilisateurModel
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = require __DIR__ . '/../config/db.php';
    }

    /**
     * Renvoie l'utilisateur (avec hash) ou null si introuvable.
     * Utilisé par AuthController pour la vérification du mot de passe.
     */
    public function trouverParIdentifiant(string $identifiant): ?array
    {
        $sql = 'SELECT id, identifiant, mot_de_passe, nom, prenom, role
                FROM utilisateurs
                WHERE identifiant = :identifiant
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['identifiant' => $identifiant]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Renvoie la liste des utilisateurs (sans mot_de_passe).
     * @return array<int, array<string, mixed>>
     */
    public function lister(): array
    {
        $sql = 'SELECT id, identifiant, nom, prenom, role, cree_le
                FROM utilisateurs
                ORDER BY id';

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}