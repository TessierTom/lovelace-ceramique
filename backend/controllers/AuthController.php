<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UtilisateurModel;
use Firebase\JWT\JWT;

final class AuthController
{
    public function connexion(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->repondreErreur(405, 'Méthode non autorisée');
            return;
        }

        $payload = json_decode((string) file_get_contents('php://input'), true);

        if (!is_array($payload)) {
            $this->repondreErreur(400, 'Corps JSON invalide');
            return;
        }

        $identifiant = trim((string) ($payload['identifiant'] ?? ''));
        $mdp = (string) ($payload['mdp'] ?? '');

        if ($identifiant === '' || $mdp === '') {
            $this->repondreErreur(422, 'Identifiant et mot de passe obligatoires');
            return;
        }

        try {
            /** @var \PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';
            $utilisateurModel = new UtilisateurModel($pdo);

            $utilisateur = $utilisateurModel->trouverParIdentifiant($identifiant);

            if ($utilisateur === null) {
                $this->repondreErreur(401, 'Identifiants incorrects');
                return;
            }

            if (!password_verify($mdp, $utilisateur['mdp'])) {
                $this->repondreErreur(401, 'Identifiants incorrects');
                return;
            }

            $secret = getenv('JWT_SECRET');
            if ($secret === false || $secret === '') {
                $this->repondreErreur(500, 'Configuration serveur incomplète');
                return;
            }

            $claims = [
                'sub' => (int) $utilisateur['id'],
                'identifiant' => $utilisateur['identifiant'],
                'role' => $utilisateur['role'],
                'iat' => time(),                 // émis maintenant
                'exp' => time() + 60 * 60 * 8,   // expire dans 8 heures
            ];
            $token = JWT::encode($claims, $secret, 'HS256');

            http_response_code(200);
            echo json_encode([
                'status' => 'ok',
                'token' => $token,
                'utilisateur' => [
                    'id' => $utilisateur['id'],
                    'identifiant' => $utilisateur['identifiant'],
                    'email' => $utilisateur['email'],
                    'nom' => $utilisateur['nom'],
                    'role' => $utilisateur['role'],
                    'organisation_id' => $utilisateur['organisation_id'],
                ],
            ]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $this->repondreErreur(500, 'Connexion impossible');
        }
    }

    private function repondreErreur(int $code, string $message): void
    {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
        ]);
    }
}
