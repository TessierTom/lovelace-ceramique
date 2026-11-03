<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

require_once __DIR__ . '/../models/HealthcheckModel.php';
require_once __DIR__ . '/../views/HealthcheckView.php';

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
