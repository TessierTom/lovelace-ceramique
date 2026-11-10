<?php
// Contrôleur de la page d'accueil.
// Appelé depuis index.php quand l'URL est "/".

declare(strict_types=1);

namespace App\Controllers;

final class HomeController
{
    // Affiche la page d'accueil (test PHP + connexion PostgreSQL).
    public function index(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        echo '<h1>Lovelace Céramique — home PHP</h1>';
        echo '<p>Version PHP : ' . PHP_VERSION . '</p>';

        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/../config/db.php';
        $pdo->query('SELECT 1');

        echo '<p style="color: green;">Connexion PostgreSQL OK</p>';
    }
}
