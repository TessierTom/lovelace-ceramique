<?php

declare(strict_types=1);

namespace App\Controllers;



// API commande : lister (GET) et créer (POST) une commande avec ses tickets.
final class CommandeController
{
    private const QUANTITE_MAX = 500;
    private const TICKETS_MAX = 500;

    /**
     * GET /api/commande
     * Réponse : { status, message, commandes: [...] }
     */
    public function get(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            /** @var PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';
            $commandes = (new CommandeModel($pdo))->lister();

            http_response_code(200);
            echo json_encode([
                'status' => 'ok',
                'message' => count($commandes) . ' commande(s) trouvée(s)',
                'commandes' => $commandes,
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->repondreErreur(500, 'Impossible de récupérer les commandes');
        }
    }

    /**
     * POST /api/commande
     * Corps JSON attendu :
     * {
     *   "nom_client": "Galerie Terreau",
     *   "utilisateur_id": 1,
     *   "lignes": [ { "piece_id": 1, "quantite": 4 }, { "piece_id": 4, "quantite": 2 } ]
     * }
     * Crée la commande + un ticket par exemplaire (première étape, non assigné).
     * Réponse : { status, message, commande: { id, nom_client, date_creation, statut, nb_tickets } }
     */
    public function creer(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $payload = json_decode((string) file_get_contents('php://input'), true);

        if (!is_array($payload)) {
            $this->repondreErreur(400, 'Corps JSON invalide');
            return;
        }

        $nomClient = trim((string) ($payload['nom_client'] ?? ''));
        $utilisateurId = (int) ($payload['utilisateur_id'] ?? 0);
        $lignesBrutes = $payload['lignes'] ?? [];

        [$lignes, $erreurs] = $this->normaliserLignes($lignesBrutes);

        if ($nomClient === '' || mb_strlen($nomClient) > 100) {
            $erreurs[] = 'Le nom du client est obligatoire (max. 100 caractères)';
        }

        if ($utilisateurId <= 0) {
            $erreurs[] = 'L\'utilisateur est obligatoire';
        }

        if ($erreurs !== []) {
            $this->repondreErreur(422, 'Données invalides', $erreurs);
            return;
        }

        try {
            /** @var PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';
            $model = new CommandeModel($pdo);

            // TODO (authentification) : l'utilisateur viendra de la session, pas du corps de la requête.
            if (!$model->utilisateurExiste($utilisateurId)) {
                $this->repondreErreur(422, 'L\'utilisateur indiqué n\'existe pas');
                return;
            }

            $inconnues = $model->piecesInconnues(array_column($lignes, 'piece_id'));
            if ($inconnues !== []) {
                $this->repondreErreur(422, 'Pièce(s) inconnue(s) : ' . implode(', ', $inconnues));
                return;
            }

            $commande = $model->creer($nomClient, $utilisateurId, $lignes);

            http_response_code(201);
            echo json_encode([
                'status' => 'ok',
                'message' => 'Commande créée avec ' . $commande['nb_tickets'] . ' ticket(s)',
                'commande' => $commande,
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->repondreErreur(500, 'Impossible de créer la commande');
        }
    }

    /**
     * Vérifie les lignes et regroupe celles qui portent sur la même pièce.
     *
     * @return array{0: list<array{piece_id:int, quantite:int}>, 1: list<string>}
     */
    private function normaliserLignes(mixed $lignesBrutes): array
    {
        $erreurs = [];
        $parPiece = [];

        if (!is_array($lignesBrutes) || $lignesBrutes === []) {
            return [[], ['La commande doit contenir au moins une pièce']];
        }

        foreach (array_values($lignesBrutes) as $index => $ligne) {
            $numero = $index + 1;

            if (!is_array($ligne)) {
                $erreurs[] = "Ligne $numero : format invalide";
                continue;
            }

            $pieceId = (int) ($ligne['piece_id'] ?? 0);
            $quantite = (int) ($ligne['quantite'] ?? 0);

            if ($pieceId <= 0) {
                $erreurs[] = "Ligne $numero : la pièce est obligatoire";
                continue;
            }

            if ($quantite < 1 || $quantite > self::QUANTITE_MAX) {
                $erreurs[] = "Ligne $numero : la quantité doit être comprise entre 1 et " . self::QUANTITE_MAX;
                continue;
            }

            $parPiece[$pieceId] = ($parPiece[$pieceId] ?? 0) + $quantite;
        }

        if (array_sum($parPiece) > self::TICKETS_MAX) {
            $erreurs[] = 'Une commande ne peut pas dépasser ' . self::TICKETS_MAX . ' tickets';
        }

        $lignes = [];
        foreach ($parPiece as $pieceId => $quantite) {
            $lignes[] = ['piece_id' => $pieceId, 'quantite' => $quantite];
        }

        return [$lignes, $erreurs];
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
