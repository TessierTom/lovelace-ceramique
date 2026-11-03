<?php


// Chargement des contrôleurs
require_once __DIR__ . '/../controllers/HomeController.php';
require_once __DIR__ . '/../controllers/TestController.php';
require_once __DIR__ . '/../controllers/ExampleController.php';
require_once __DIR__ . '/../controllers/HealthcheckController.php';

// Récupérer la route demandée
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


    case '/api/healthcheck':
        $controller = new HealthcheckController();
        $controller->index();
        break;

    default:
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>404 Not Found</h1>';
        echo '<p>La page demandée n\'existe pas.</p>';
        break;
}
exit;