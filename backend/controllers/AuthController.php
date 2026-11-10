<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UtilisateurModel;
use Firebase\JWT\JWT;

// Authentification : POST /api/connexion
// Entrée  : { identifiant, mdp }
// Sortie  : { status, token, utilisateur: { id, identifiant, nom, role } }
final class AuthController
{
    public function connexion(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // 1. Seule la méthode POST est acceptée
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->repondreErreur(405, 'Méthode non autorisée');
            return;
        }

        // 2. Lecture du body JSON
        $payload = json_decode((string) file_get_contents('php://input'), true);

        if (!is_array($payload)) {
            $this->repondreErreur(400, 'Corps JSON invalide');
            return;
        }

        // 3. Champs obligatoires
        $identifiant = trim((string) ($payload['identifiant'] ?? ''));
        $mdp = (string) ($payload['mdp'] ?? '');

        if ($identifiant === '' || $mdp === '') {
            $this->repondreErreur(422, 'Identifiant et mot de passe obligatoires');
            return;
        }

        // 4. Secret JWT (vérifié tôt : inutile d'interroger la base si la config est cassée)
        $secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET');

        if ($secret === false || $secret === null || $secret === '') {
            $this->repondreErreur(500, 'Configuration serveur incomplète');
            return;
        }

        try {
            /** @var \PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';
            $utilisateurModel = new UtilisateurModel($pdo);

            // 5. Chercher l'utilisateur par identifiant
            $utilisateur = $utilisateurModel->trouverParIdentifiant($identifiant);

            // 6. Même message pour "inconnu" et "mauvais mot de passe"
            //    (on ne révèle pas si l'identifiant existe)
            if ($utilisateur === null) {
                $this->repondreErreur(401, 'Identifiants incorrects');
                return;
            }

            if (!password_verify($mdp, (string) ($utilisateur['mdp'] ?? ''))) {
                $this->repondreErreur(401, 'Identifiants incorrects');
                return;
            }

            // 7. Création du jeton (valable 8 heures)
            $now = time();

            $claims = [
                'sub' => (int) $utilisateur['id'],
                'identifiant' => $utilisateur['identifiant'],
                'role' => $utilisateur['role'],
                'iat' => $now,
                'exp' => $now + 60 * 60 * 8,
            ];

            $token = JWT::encode($claims, $secret, 'HS256');

            // 8. Réponse : jeton + infos utiles, JAMAIS le mot de passe
            http_response_code(200);
            echo json_encode([
                'status' => 'ok',
                'token' => $token,
                'utilisateur' => [
                    'id' => (int) $utilisateur['id'],
                    'identifiant' => $utilisateur['identifiant'],
                    'nom' => $utilisateur['nom'],
                    'role' => $utilisateur['role'],
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