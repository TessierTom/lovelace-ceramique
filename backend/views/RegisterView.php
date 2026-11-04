<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

final class RegisterView
{

    // Affichage d'un message de succès pour vérifier que la route /api/healthcheck fonctionne.
    public function success(array $result): void
    {
        // Définir le format de réponse
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'status' => 'ok',
            'message' => 'Organisation et compte administrateur créés',
            'id' => $result['utilisateur_id'],
            'organisation_id' => $result['organisation_id'],
        ]);
    }

    // Affichage d'un message d'erreur
    public function error(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        // Définir le format de réponse
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'Erreur lors de l\'inscription : ' . $message
        ]);
    }
}
