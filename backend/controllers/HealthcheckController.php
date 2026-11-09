<?php
// Contrôleur du healthcheck.
// Appelé depuis index.php quand l'URL est "/api/healthcheck".

declare(strict_types=1);

namespace App\Controllers;

use App\Models\HealthcheckModel;
use App\Views\HealthcheckView;

final class HealthcheckController
{
    /**
     * Vérifie que PostgreSQL répond et renvoie un JSON de statut.
     */
    public function index(): void
    {
        $healthcheckView = new HealthcheckView();

        try {
            $healthcheckModel = new HealthcheckModel();
            $result = $healthcheckModel->ping();
            $healthcheckView->success($result);
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            error_log($message);
            $healthcheckView->error($message);
        }
    }
}