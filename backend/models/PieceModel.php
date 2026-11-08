<?php

require_once __DIR__ . '/../config/db.php';

class PieceModel
{
    /**
     * CREATE — insère une nouvelle pièce
     */
    public static function create(array $data): int
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO pieces (commande_id, nom_ticket, type_piece, etape_actuelle, statut, notes)
             VALUES (:commande_id, :nom_ticket, :type_piece, :etape_actuelle, :statut, :notes)"
        );
        $stmt->execute([
            ':commande_id'    => $data['commande_id'],
            ':nom_ticket'     => $data['nom_ticket'],
            ':type_piece'     => $data['type_piece'],
            ':etape_actuelle' => $data['etape_actuelle'] ?? 1,
            ':statut'         => $data['statut'] ?? 'en_cours',
            ':notes'          => $data['notes'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * GET /api/pieces — liste avec filtres optionnels
     */
    public static function lister(
        ?int $commandeId = null,
        ?string $statut  = null,
        ?int $etape      = null,
        ?string $type    = null
    ): array {
        $pdo    = Database::getConnection();
        $where  = [];
        $params = [];

        if ($commandeId) { $where[] = "p.commande_id = ?";  $params[] = $commandeId; }
        if ($statut)     { $where[] = "p.statut = ?";       $params[] = $statut; }
        if ($etape)      { $where[] = "p.etape_actuelle = ?"; $params[] = $etape; }
        if ($type)       { $where[] = "p.type_piece = ?";   $params[] = $type; }

        $sql = "SELECT p.*, c.nom as commande_nom, c.client as commande_client
                FROM pieces p
                LEFT JOIN commandes c ON c.id = p.commande_id";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY p.date_creation DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * GET /api/pieces/:id — détail d'une pièce
     */
    public static function obtenir(int $id): ?array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT p.*, c.nom as commande_nom
             FROM pieces p
             JOIN commandes c ON c.id = p.commande_id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Avance la pièce à l'étape suivante (max 6 → statut "terminee")
     */
    public static function avancerEtape(int $id): ?array
    {
        $pdo   = Database::getConnection();
        $piece = self::obtenir($id);
        if (!$piece) return null;
        if ($piece['statut'] === 'terminee') return $piece;

        $next = (int) $piece['etape_actuelle'] + 1;

        if ($next > 5) {
            $stmt = $pdo->prepare("UPDATE pieces SET statut = 'terminee', date_terminee = NOW() WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE pieces SET etape_actuelle = ? WHERE id = ?");
            $stmt->execute([$next, $id]);
        }
        // dans le branchement "terminee" :
        if ($next > 5) {
            $stmt->execute([$id]);
        }

        return self::obtenir($id);
    }

    /**
     * Retourne la pièce à l'étape précédente (min 1)
     */
    public static function retournerEtape(int $id): ?array
    {
        $pdo   = Database::getConnection();
        $piece = self::obtenir($id);
        if (!$piece) return null;
        if ((int) $piece['etape_actuelle'] <= 1) return $piece;

        $prev = (int) $piece['etape_actuelle'] - 1;
        $stmt = $pdo->prepare(
            "UPDATE pieces SET etape_actuelle = ?, statut = 'en_cours', date_terminee = NULL WHERE id = ?"
        );
        $stmt->execute([$prev, $id]);

        return self::obtenir($id);
    }

    /**
     * Met à jour les notes d'une pièce
     */
    public static function majNotes(int $id, string $notes): ?array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE pieces SET notes = ? WHERE id = ?");
        $stmt->execute([$notes, $id]);
        return self::obtenir($id);
    }

    /**
     * Annule une pièce
     */
    public static function annuler(int $id): bool
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE pieces SET statut = 'annulee' WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Supprime définitivement une pièce
     */
    public static function supprimer(int $id): bool
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM pieces WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Statistiques globales (compteur par statut et par étape)
     */
    public static function stats(): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT
                COUNT(*) as total,
                SUM(statut = 'en_cours')  as en_cours,
                SUM(statut = 'terminee')  as terminee,
                SUM(statut = 'annulee')   as annulee,
                SUM(etape_actuelle = 1)   as e_modelage,
                SUM(etape_actuelle = 2)   as e_sechage,
                SUM(etape_actuelle = 3)   as e_premier_feu,
                SUM(etape_actuelle = 4)   as e_emailage,
                SUM(etape_actuelle = 5)   as e_deuxieme_feu
             FROM pieces
             WHERE statut = 'en_cours'"
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Résumé par étape (pour la page d'accueil)
     */
    public static function resumeParEtape(): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT etape_actuelle, COUNT(*) as total
             FROM pieces
             WHERE statut = 'en_cours'
             GROUP BY etape_actuelle
             ORDER BY etape_actuelle"
        );
        $steps = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $steps[(int) $row['etape_actuelle']] = (int) $row['total'];
        }
        return $steps;
    }
}