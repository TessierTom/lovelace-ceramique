<?php
require_once __DIR__ . '/../models/PieceModel.php';
require_once __DIR__ . '/../views/JsonView.php';

class PieceController
{
    /**
     * GET /api/pieces?commande_id=&statut=&etape=&type=
     */
    public static function lister()
    {
        $commandeId = isset($_GET['commande_id']) ? (int)$_GET['commande_id'] : null;
        $statut     = $_GET['statut'] ?? null;
        $etape      = isset($_GET['etape']) ? (int)$_GET['etape'] : null;
        $type       = $_GET['type'] ?? null;

        try {
            $pieces = PieceModel::lister($commandeId, $statut, $etape, $type);
            $stats  = PieceModel::stats();
            JsonView::success(200, ['pieces' => $pieces, 'stats' => $stats], 'Pièces récupérées.');
        } catch (\Exception $e) {
            JsonView::error(500, 'Erreur lors de la récupération des pièces.');
        }
    }

    /**
     * GET /api/pieces/:id
     */
    public static function obtenir(string $id)
    {
        $piece = PieceModel::obtenir((int)$id);
        if (!$piece) { JsonView::error(404, 'Pièce introuvable.'); return; }
        JsonView::success(200, $piece, 'Pièce récupérée.');
    }

    /**
     * POST /api/pieces (création)
     */
    public static function creer()
    {
        $body = json_decode(file_get_contents('php://input'), true);
        if (empty($body['commande_id']) || empty($body['nom_ticket']) || empty($body['type_piece'])) {
            JsonView::error(422, 'Champs requis : commande_id, nom_ticket, type_piece.');
            return;
        }
        try {
            $id = PieceModel::create($body);
            JsonView::success(201, ['id' => $id], 'Pièce créée.');
        } catch (\Exception $e) {
            JsonView::error(500, 'Erreur lors de la création.');
        }
    }

    /**
     * POST /api/pieces/:id/next → étape suivante
     */
    public static function avancer(string $id)
    {
        $piece = PieceModel::avancerEtape((int)$id);
        if (!$piece) { JsonView::error(404, 'Pièce introuvable.'); return; }
        JsonView::success(200, $piece, 'Étape avancée.');
    }

    /**
     * POST /api/pieces/:id/prev → étape précédente
     */
    public static function retourner(string $id)
    {
        $piece = PieceModel::retournerEtape((int)$id);
        if (!$piece) { JsonView::error(404, 'Pièce introuvable.'); return; }
        JsonView::success(200, $piece, 'Retour à l\'étape précédente.');
    }

    /**
     * PUT /api/pieces/:id/notes
     */
    public static function majNotes(string $id)
    {
        $body = json_decode(file_get_contents('php://input'), true);
        if (empty($body['notes'])) {
            JsonView::error(422, 'Le champ notes est requis.');
            return;
        }
        $piece = PieceModel::majNotes((int)$id, $body['notes']);
        if (!$piece) { JsonView::error(404, 'Pièce introuvable.'); return; }
        JsonView::success(200, $piece, 'Notes mises à jour.');
    }

    /**
     * POST /api/pieces/:id/annuler
     */
    public static function annuler(string $id)
    {
        if (!PieceModel::annuler((int)$id)) { JsonView::error(404, 'Pièce introuvable.'); return; }
        JsonView::success(200, null, 'Pièce annulée.');
    }

    /**
     * DELETE /api/pieces/:id
     */
    public static function supprimer(string $id)
    {
        if (!PieceModel::supprimer((int)$id)) { JsonView::error(404, 'Pièce introuvable.'); return; }
        JsonView::success(200, null, 'Pièce supprimée.');
    }

    /**
     * GET /api/pieces/summary (résumé par étape)
     */
    public static function resume()
    {
        $steps = PieceModel::resumeParEtape();
        JsonView::success(200, $steps);
    }
}