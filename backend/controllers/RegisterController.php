<?php
// Contrôleur d'inscription.
// Inscription : POST /api/register
// Hash bcrypt + création organisation + compte Administrateur.

declare(strict_types=1);

require_once __DIR__ . '/../models/RegisterModel.php';
require_once __DIR__ . '/../views/RegisterView.php';

final class RegisterController
{
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
            return;
        }

        $data = json_decode(file_get_contents('php://input') ?: '', true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Corps JSON invalide']);
            return;
        }

        $organisationNom = trim((string) ($data['organisation_nom'] ?? ''));
        $identifiant = trim((string) ($data['identifiant'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $nom = trim((string) ($data['nom'] ?? ''));
        $mdpClair = $data['mdp'] ?? null;

        $registerView = new RegisterView();
        try {
            if ($organisationNom === '' || $identifiant === '' || $email === '' || $nom === '') {
                throw new Exception('Champs manquants', 400);
            }

            if (!is_string($mdpClair) || strlen($mdpClair) < 8) {
                throw new Exception('Mot de passe trop court (min. 8)', 400);
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new Exception('Email invalide', 400);
            }

            $registerModel = new RegisterModel();
            $result = $registerModel->register($organisationNom, $identifiant, $email, $nom, $mdpClair);

            $registerView->success($result);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $registerView->error($e->getCode() ?: 500, $e->getMessage());
        }
    }
}
