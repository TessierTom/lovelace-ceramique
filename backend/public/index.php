<?php
declare(strict_types=1);

// Chargement des contrôleurs
require_once __DIR__ . '/../controllers/HomeController.php';
require_once __DIR__ . '/../controllers/TestController.php';
require_once __DIR__ . '/../controllers/ExampleController.php';
require_once __DIR__ . '/../controllers/HealthcheckController.php';
require_once __DIR__ . '/../controllers/UtilisateurController.php';

// Récupérer la route demandée
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

        // Vérifier la route
    case '/api/healthcheck': 
        $controller = new HealthcheckController();
        $controller->index();
        break;
    
    case '/api/utilisateur': 
        $controller = new UtilisateurController();
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