<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class CommandeModel
{
    public static function creer(
        string $nom,
        ?string $client,
        ?string $description,
        int $nbPieces,
        string $nomPiece,
        string $typePiece
    ): int {
        $pdo = require __DIR__ . '/../config/db.php';

        $pdo->beginTransaction();
        try {
            // 1. Créer la commande
            $stmt = $pdo->prepare(
                "INSERT INTO commandes (nom, client, description)
                 VALUES (:nom, :client, :description)
                 RETURNING id"
            );
            $stmt->execute([
                ':nom'        => $nom,
                ':client'     => $client,
                ':description' => $description,
            ]);
            $commandeId = (int) $stmt->fetchColumn();

            // 2. Créer N tickets pièces
            $stmt = $pdo->prepare(
                "INSERT INTO pieces (commande_id, nom, type_piece)
                 VALUES (:commande_id, :nom, :type_piece)"
            );
            for ($i = 1; $i <= $nbPieces; $i++) {
                $stmt->execute([
                    ':commande_id' => $commandeId,
                    ':nom'         => "$nomPiece #$i",
                    ':type_piece'  => $typePiece,
                ]);
            }

            $pdo->commit();
            return $commandeId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function lister(): array
    {
        $pdo = require __DIR__ . '/../config/db.php';
        $stmt = $pdo->query(
            "SELECT c.*,
                    COUNT(p.id) AS nb_pieces,
                    COUNT(p.id) FILTER (WHERE p.statut = 'terminee') AS nb_terminees,
                    COUNT(p.id) FILTER (WHERE p.statut = 'en_cours') AS nb_en_cours
             FROM commandes c
             LEFT JOIN pieces p ON p.commande_id = c.id
             GROUP BY c.id
             ORDER BY c.date_creation DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenir(int $id): ?array
    {
        $pdo = require __DIR__ . '/../config/db.php';
        $stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}