<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

final class UtilisateurModel
{
    private PDO $pdo;
    
    public function __construct()
    {
        // Connexion à PostgreSQL
        $this->pdo = require __DIR__ . '/../config/db.php';
    }

    // Récupère la liste des utilisateurs.
    public function getUtilisateurs(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM utilisateur');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}