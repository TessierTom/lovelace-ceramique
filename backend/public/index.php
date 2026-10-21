<?php

declare(strict_types=1);

// Définir le format de réponse
header('Content-Type: application/json; charset=utf-8');

// Récupérer la route demandée
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Vérifier la route
if ($path === '/api/healthcheck') {

    try {
        // Connexion à PostgreSQL
        $pdo = require __DIR__ . '/../config/db.php';

        // Vérification de la base de données
        $result = $pdo->query('SELECT 1')->fetchColumn();

        // Réponse en cas de succès
        echo json_encode([
            'status' => 'ok',
            'database' => 'connected',
            'result' => (int) $result,
            'message' => 'Connexion PostgreSQL OK'
        ]);

    } catch (Throwable $e) {

        // Réponse en cas d'erreur
        http_response_code(503);

        error_log($e->getMessage());

        echo json_encode([
            'status' => 'error',
            'database' => 'disconnected',
            'message' => 'Service temporairement indisponible'
        ]);
    }

    exit;
}

// Route inconnue
http_response_code(404);

echo json_encode([
    'status' => 'error',
    'message' => 'Route introuvable'
]);