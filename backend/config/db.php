<?php

declare(strict_types=1);

// Configuration de la connexion PostgreSQL
$host = getenv('DB_HOST') ?: 'postgres';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'lovelace';
$user = getenv('DB_USER') ?: 'lovelace';
$password = getenv('DB_PASSWORD');

// Vérification du mot de passe
if ($password === false || $password === '') {
    throw new RuntimeException(
        'La variable d’environnement DB_PASSWORD est manquante.'
    );
}

// Data Source Name
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

// Création de la connexion PDO
$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

return $pdo;