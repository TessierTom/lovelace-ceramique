<?php

declare(strict_types=1);

// Chargement des contrôleurs
require_once __DIR__ . '/../controllers/HomeController.php';
require_once __DIR__ . '/../controllers/TestController.php';
require_once __DIR__ . '/../controllers/ExampleController.php';
require_once __DIR__ . '/../controllers/CommandeController.php';
require_once __DIR__ . '/../controllers/PieceController.php';
require_once __DIR__ . '/../controllers/UtilisateurController.php';

// Réponse JSON d'erreur pour les routes non autorisées / introuvables
function repondreJsonErreur(int $code, string $message): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode([
        'status' => 'error',
        'message' => $message,
    ]);
}

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

    case '/api/utilisateurs':
        $controller = new UtilisateurController();
        switch ($method) {
            case 'GET':
                $controller->get();
                break;
            case 'POST':
                $controller->creer();
                break;
            default:
                repondreJsonErreur(405, 'Méthode non autorisée');
                break;
        }
        break;

    case '/api/pieces':
        $controller = new PieceController();
        switch ($method) {
            case 'GET':
                $controller->get();
                break;
            default:
                repondreJsonErreur(405, 'Méthode non autorisée');
                break;
        }
        break;

    case '/api/commande':
        $controller = new CommandeController();
        switch ($method) {
            case 'GET':
                $controller->get();
                break;
            case 'POST':
                $controller->creer();
                break;
            default:
                repondreJsonErreur(405, 'Méthode non autorisée');
                break;
        }
        break;

    default:
        repondreJsonErreur(404, 'Route introuvable');
        break;
}

exit;
