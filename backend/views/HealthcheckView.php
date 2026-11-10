<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

namespace App\Views;

final class HealthcheckView
{

    // Affichage d'un message de succès pour vérifier que la route /api/healthcheck fonctionne.
    public function success(int $result): void
    {
        // Définir le format de réponse
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'status' => 'ok',
            'database' => 'connected',
            'result' => (int) $result,
            'message' => 'Connexion PostgreSQL OK TEST avec vue'
        ]);
    }

    // Affichage d'un message d'erreur
    public function error(string $message): void
    {
        http_response_code(503);
        // Définir le format de réponse
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'database' => 'disconnected',
            'message' => $message
        ]);
    }
}
