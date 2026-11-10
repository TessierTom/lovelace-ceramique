<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\HealthcheckModel;
use App\Views\HealthcheckView;
use Throwable;


final class HealthcheckController
{
    // Affiche un message pour vérifier que la route /api/healthcheck fonctionne.
    public function index(): void
    {
        $healthcheckView = new HealthcheckView();
        try {
            $healthcheckModel = new HealthcheckModel();
            $result = $healthcheckModel->ping();

            $healthcheckView->success($result);
        } catch (Throwable $e) {
            $message = $e->getMessage();
            error_log($message);
            $healthcheckView->error($message);
        }
    }
}
