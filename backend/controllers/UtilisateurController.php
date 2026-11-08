<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/OrganisationModel.php';
require_once __DIR__ . '/../models/UtilisateurModel.php';

// CRUD utilisateur — pour l'instant : lister, créer (avec son organisation).
// À venir : modifier, supprimer.
final class UtilisateurController
{
    /**
     * GET /api/utilisateurs
     * Réponse : { status, utilisateurs: [ { id, nom, role }, ... ] }
     */
    public function get(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            /** @var PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';
            $utilisateurs = (new UtilisateurModel($pdo))->lister();

            http_response_code(200);
            echo json_encode([
                'status' => 'ok',
                'utilisateurs' => $utilisateurs,
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->repondreErreur(500, 'Impossible de récupérer les utilisateurs');
        }
    }

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

        $erreurs = $this->valider($organisationNom, $identifiant, $mdp, $email, $nom);

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

    /** @return list<string> */
    private function valider(
        string $organisationNom,
        string $identifiant,
        string $mdp,
        string $email,
        string $nom,
    ): array {
        $erreurs = [];

        if ($organisationNom === '' || mb_strlen($organisationNom) > 50) {
            $erreurs[] = 'Le nom de l\'organisation est obligatoire (max. 50 caractères)';
        }

        if ($identifiant === '' || mb_strlen($identifiant) > 25) {
            $erreurs[] = 'L\'identifiant est obligatoire (max. 25 caractères)';
        }

        if ($mdp === '' || mb_strlen($mdp) < 8) {
            $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
            $erreurs[] = 'L\'email est invalide';
        }

        if ($nom === '' || mb_strlen($nom) > 50) {
            $erreurs[] = 'Le nom est obligatoire (max. 50 caractères)';
        }

        return $erreurs;
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
