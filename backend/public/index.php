<?php

declare(strict_types=1);

// Chargement des contrôleurs
require_once __DIR__ . '/../controllers/HomeController.php';
require_once __DIR__ . '/../controllers/TestController.php';
require_once __DIR__ . '/../controllers/ExampleController.php';


// Récupérer la route demandée
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Vérifier la route
if ($path === '/api/healthcheck') {
    // Définir le format de réponse
    header('Content-Type: application/json; charset=utf-8');

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

// Routes des contrôleurs
switch ($path) {
    case '/':
        $controller = new HomeController();
        $controller->index();
        break;

    case '/test':
        $controller = new TestController();
        $controller->index();
        break;

    case '/example':
        $controller = new ExampleController();
        $controller->index();
        break;

    default:
        http_response_code(404);

        echo json_encode([
            'status' => 'error',
            'message' => 'Route introuvable'
        ]);
        break;
}

exit;