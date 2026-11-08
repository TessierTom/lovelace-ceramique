<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/PieceModel.php';

// API pièce : lister les modèles de pièce (pour le formulaire de commande).
final class PieceController
{
    /**
     * GET /api/pieces
     * Réponse : { status, pieces: [ { id, nom }, ... ] }
     */
    public function get(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            /** @var PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';
            $pieces = (new PieceModel($pdo))->lister();

            http_response_code(200);
            echo json_encode([
                'status' => 'ok',
                'pieces' => $pieces,
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Impossible de récupérer les pièces',
            ]);
        }
    }
}
