<?php
// Contrôleur de la page d'accueil.
// Appelé depuis index.php quand l'URL est "/".

declare(strict_types=1);

namespace App\Controllers;

final class ExampleController
{
    // Affiche la page d'accueil (test PHP + connexion PostgreSQL).
    public function index(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        echo '<h1>Lovelace Céramique — example PHP</h1>';
        echo '<p>Version PHP : ' . PHP_VERSION . '</p>';

        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/../config/db.php';
        $pdo->query('SELECT 1');

        $exemple = new Exemple('Bonjour, monde !');
        $exemple->afficher(); // Affiche "Bonjour, monde !"
        $exemple->goodBye(); // Affiche "Au revoir, monde !"

        echo '<p style="color: green;">Connexion PostgreSQL OK</p>';
        echo __DIR__;
    }
}

class Exemple
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function afficher(): void
    {
        echo '<p style="color: blue;">' . $this->message . '</p>';
    }

    public function goodBye(): void
    {
        echo '<p style="color: red;">Au revoir, monde !</p>';
    }
}
