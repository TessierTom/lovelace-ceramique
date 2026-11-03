<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

require_once __DIR__ . '/../models/UtilisateurModel.php';
require_once __DIR__ . '/../views/UtilisateurView.php';

final class UtilisateurController
{
    // Affiche un message pour vérifier que la route /api/utilisateur fonctionne.
    public function index(): void
    {
        $utilisateurView = new UtilisateurView();
        try {
            $utilisateurModel = new UtilisateurModel();
            $result = $utilisateurModel->getUtilisateurs();

            $utilisateurView->success($result);
        } catch (Throwable $e) {
            $message = $e->getMessage();
            error_log($message);
            $utilisateurView->error($message);
        }
    }
}
