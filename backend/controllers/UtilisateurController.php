<?php

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\OrganisationModel;
use App\Models\UtilisateurModel;
use App\Services\UtilisateurValidator;
use PDO;
use Throwable;

// CRUD utilisateur — pour l'instant : créer (avec son organisation).
// À venir : lister, modifier, supprimer.
final class UtilisateurController
{
    public function creer(): void
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

        $organisationNom = trim((string) ($payload['organisation_nom'] ?? ''));
        $identifiant = trim((string) ($payload['identifiant'] ?? ''));
        $mdp = (string) ($payload['mdp'] ?? '');
        $email = trim((string) ($payload['email'] ?? ''));
        $nom = trim((string) ($payload['nom'] ?? ''));

        $validator = new UtilisateurValidator();
        $erreurs = $validator->valider($organisationNom, $identifiant, $mdp, $email, $nom);

        if ($erreurs !== []) {
            $this->repondreErreur(422, 'Données invalides', $erreurs);
            return;
        }

        try {
            /** @var PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';
            $organisationModel = new OrganisationModel($pdo);
            $utilisateurModel = new UtilisateurModel($pdo);

            if ($utilisateurModel->identifiantExiste($identifiant)) {
                $this->repondreErreur(409, 'Cet identifiant est déjà utilisé');
                return;
            }

            if ($utilisateurModel->emailExiste($email)) {
                $this->repondreErreur(409, 'Cet email est déjà utilisé');
                return;
            }

            $pdo->beginTransaction();

            $organisationId = $organisationModel->creer($organisationNom);
            $utilisateurId = $utilisateurModel->creer(
                $identifiant,
                password_hash($mdp, PASSWORD_DEFAULT),
                $email,
                $nom,
                $organisationId,
                'Administrateur',
            );

            $pdo->commit();

            http_response_code(201);
            echo json_encode([
                'status' => 'ok',
                'organisation' => [
                    'id' => $organisationId,
                    'nom' => $organisationNom,
                ],
                'utilisateur' => [
                    'id' => $utilisateurId,
                    'identifiant' => $identifiant,
                    'email' => $email,
                    'nom' => $nom,
                    'organisation_id' => $organisationId,
                    'role' => 'Administrateur',
                ],
            ]);
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());
            $this->repondreErreur(500, 'Impossible de créer l\'utilisateur');
        }
    }

    /** @param list<string>|null $erreurs */
    private function repondreErreur(int $code, string $message, ?array $erreurs = null): void
    {
        http_response_code($code);

        $corps = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($erreurs !== null) {
            $corps['erreurs'] = $erreurs;
        }

        echo json_encode($corps);
    }
}
