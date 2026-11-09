<?php

declare(strict_types=1);

namespace App\Models;

// Accès aux tables commande et commande_ticket (requêtes SQL uniquement).
final class CommandeModel
{
    public function __construct(
        private PDO $pdo,
    ) {}

    // Liste des commandes avec le nom du créateur et le nombre de tickets.
    public function lister(): array
    {
        $stmt = $this->pdo->query(
            'SELECT c.id,
                    c.nom_client,
                    c.date_creation,
                    c.statut,
                    u.nom AS utilisateur_nom,
                    COUNT(t.id) AS nb_tickets
             FROM commande c
             JOIN utilisateur u ON u.id = c.utilisateur_id
             LEFT JOIN commande_ticket t ON t.commande_id = c.id
             GROUP BY c.id, u.nom
             ORDER BY c.date_creation DESC, c.id DESC'
        );

        $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($commandes as &$commande) {
            $commande['id'] = (int) $commande['id'];
            $commande['nb_tickets'] = (int) $commande['nb_tickets'];
        }
        unset($commande);

        return $commandes;
    }

    public function utilisateurExiste(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM utilisateur WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Renvoie les ids de pièces qui n'existent pas en base.
     *
     * @param list<int> $pieceIds
     * @return list<int>
     */
    public function piecesInconnues(array $pieceIds): array
    {
        $pieceIds = array_values(array_unique($pieceIds));

        if ($pieceIds === []) {
            return [];
        }

        $marqueurs = implode(',', array_fill(0, count($pieceIds), '?'));
        $stmt = $this->pdo->prepare("SELECT id FROM piece WHERE id IN ($marqueurs)");
        $stmt->execute($pieceIds);

        $trouvees = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        return array_values(array_diff($pieceIds, $trouvees));
    }

    /**
     * Crée la commande ET ses tickets dans une seule transaction :
     * soit tout est créé, soit rien.
     * Chaque ticket démarre à la première étape (plus petit "ordre") et n'est pas assigné.
     *
     * @param list<array{piece_id:int, quantite:int}> $lignes
     * @return array{id:int, nom_client:string, date_creation:string, statut:string, nb_tickets:int}
     */
    public function creer(string $nomClient, int $utilisateurId, array $lignes): array
    {
        $this->pdo->beginTransaction();

        try {
            // Première étape de fabrication
            $etapeId = $this->pdo
                ->query('SELECT id FROM etape ORDER BY ordre ASC LIMIT 1')
                ->fetchColumn();

            if ($etapeId === false) {
                throw new RuntimeException('Aucune étape de fabrication en base');
            }

            // 1. La commande (date_creation et statut : valeurs par défaut de la base)
            $stmt = $this->pdo->prepare(
                'INSERT INTO commande (nom_client, utilisateur_id)
                 VALUES (:nom_client, :utilisateur_id)
                 RETURNING id, nom_client, date_creation, statut'
            );
            $stmt->execute([
                'nom_client' => $nomClient,
                'utilisateur_id' => $utilisateurId,
            ]);
            $commande = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Les tickets : un par exemplaire (utilisateur_id reste NULL)
            $insertion = $this->pdo->prepare(
                'INSERT INTO commande_ticket (piece_id, commande_id, etape_id)
                 VALUES (:piece_id, :commande_id, :etape_id)'
            );

            $nbTickets = 0;
            foreach ($lignes as $ligne) {
                for ($i = 0; $i < $ligne['quantite']; $i++) {
                    $insertion->execute([
                        'piece_id' => $ligne['piece_id'],
                        'commande_id' => $commande['id'],
                        'etape_id' => $etapeId,
                    ]);
                    $nbTickets++;
                }
            }

            $this->pdo->commit();

            $commande['id'] = (int) $commande['id'];
            $commande['nb_tickets'] = $nbTickets;

            return $commande;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}
