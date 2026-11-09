<?php

declare(strict_types=1);

// Chargement des contrôleurs
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\TestController;
use App\Controllers\ExampleController;
use App\Controllers\UtilisateurController;
use App\Controllers\HealthcheckController;
use App\Controllers\AuthController;

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

    case '/api/connexion':
        $controller = new AuthController();
        $controller->connexion();
        break;

    case '/api/healthcheck':
        $controller = new HealthcheckController();
        $controller->index();
        break;

    case '/api/utilisateurs':
        $controller = new UtilisateurController();
        switch ($method) {
            case 'GET':
                // $controller->get();
                break;
            case 'POST':
                $controller->creer();
                break;
            default:
                http_response_code(405);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Méthode non autorisée'
                ]);
                break;
        }
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
