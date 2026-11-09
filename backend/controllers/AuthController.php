<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UtilisateurModel;
use Firebase\JWT\JWT;

final class AuthController
{
    /**
     * POST /api/connexion
     * Attend un JSON : { "identifiant": "...", "mdp": "..." }
     * Renvoie : { "token": "...", "utilisateur": { ... } } ou une erreur.
     */
    public function connexion(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // 1. Vérifier la méthode HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->repondreErreur(405, 'Méthode non autorisée');
            return;
        }

        // 2. Lire le corps JSON
        $raw = file_get_contents('php://input');

        // DEBUG TEMPORAIRE – retirez-moi après test
        fwrite(STDERR, "DEBUG: type=" . gettype($raw) . " value=[" . ($raw === false ? 'FALSE' : $raw) . "]\n");

        $payload = json_decode((string) $raw, true);

        if (!is_array($payload)) {
            $this->repondreErreur(400, 'Corps JSON invalide');
            return;
        }

        // 3. Extraire les champs
        $identifiant = trim((string) ($payload['identifiant'] ?? ''));
        $mdp         = (string) ($payload['mdp'] ?? '');

        if ($identifiant === '' || $mdp === '') {
            $this->repondreErreur(422, 'Identifiant et mot de passe obligatoires');
            return;
        }

        // 4. Chercher l'utilisateur dans la base
        $utilisateurModel = new UtilisateurModel();
        $utilisateur      = $utilisateurModel->trouverParIdentifiant($identifiant);

        if ($utilisateur === null) {
            $this->repondreErreur(401, 'Identifiant inconnu');
            return;
        }

        // 5. Vérifier le mot de passe (bcrypt)
        if (!password_verify($mdp, $utilisateur['mot_de_passe'])) {
            $this->repondreErreur(401, 'Identifiants invalides');
            return;
        }

        // 6. Générer le JWT
        $secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET');
        if (empty($secret)) {
            $this->repondreErreur(500, 'JWT_SECRET non configuré');
            return;
        }

        $claims = [
            'sub'         => (int) $utilisateur['id'],
            'identifiant' => $utilisateur['identifiant'],
            'role'        => $utilisateur['role'],
            'iat'         => time(),
            'exp'         => time() + 60 * 60 * 8,
        ];

        $token = JWT::encode($claims, $secret, 'HS256');

        // 7. Renvoyer le token + utilisateur (SANS le mot de passe)
        http_response_code(200);
        echo json_encode([
            'token' => $token,
            'utilisateur' => [
                'id'     => $utilisateur['id'],
                'nom'    => $utilisateur['nom'],
                'prenom' => $utilisateur['prenom'],
                'role'   => $utilisateur['role'],
            ],
        ]);
    }

    /**
     * Renvoie une réponse d'erreur JSON.
     */
    private function repondreErreur(int $code, string $message): void
    {
        http_response_code($code);
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
        ]);
    }
}