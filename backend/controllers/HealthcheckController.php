<?php
// Contrôleur pour le point de terminaison /api/healthcheck.
// appller depuis index.php quand l'URL est "/api/healthcheck".

declare(strict_types=1);

require_once __DIR__ . '/../models/HealthcheckModel.php';
require_once __DIR__ . '/../views/HealthcheckView.php';

final class HealthcheckController
{
    public function index(): void
    {
        $model = new HealthcheckModel();
        $view = new HealthcheckView();

        try {
            // Solicitar al modelo que compruebe PostgreSQL
            $result = $model->checkDatabase();

            // Preparar los datos
            $data = [
                'status' => 'ok',
                'database' => 'connected',
                'result' => $result,
                'message' => 'Connexion PostgreSQL OK TEST'
            ];

            // Enviar respuesta HTTP 200
            $view->render($data, 200);

        } catch (Throwable $e) {

            // Registrar el error en los logs
            error_log($e->getMessage());

            // Preparar respuesta de error
            $data = [
                'status' => 'error',
                'database' => 'disconnected',
                'message' => 'Connexion PostgreSQL indisponible'
            ];

            // Enviar respuesta HTTP 503
            $view->render($data, 503);
        }
    }
}