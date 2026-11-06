<?php
// Modèle des tickets (table commande_ticket) : un ticket = une pièce à fabriquer dans une commande.
// Toutes les requêtes SQL sur les tickets sont ici, jamais dans le contrôleur.

declare(strict_types=1);

final class TicketModel
{
    public function __construct(private PDO $pdo) {}

    // Les pièces des commandes encore actives (à faire ou en cours) : les cartes du Kanban (US.14).
    // LEFT JOIN sur utilisateur : une pièce peut ne pas encore être assignée (utilisateur_id NULL),
    // avec un JOIN simple elle disparaîtrait du tableau.
    public function findActifs(): array
    {
        return $this->pdo->query(
            "SELECT ct.id, ct.commande_id, c.nom_client, c.statut,
                    p.nom AS piece, ct.etape_id, u.nom AS artisan
             FROM commande_ticket ct
             JOIN commande c ON c.id = ct.commande_id
             JOIN piece p ON p.id = ct.piece_id
             JOIN etape e ON e.id = ct.etape_id
             LEFT JOIN utilisateur u ON u.id = ct.utilisateur_id
             WHERE c.statut IN ('a_faire', 'en_cours')
             ORDER BY e.ordre, c.id, ct.id"
        )->fetchAll();
    }

    // Un ticket avec l'ordre de son étape actuelle (sert à trouver l'étape suivante).
    // Renvoie null si le ticket n'existe pas.
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ct.id, ct.etape_id, e.ordre
             FROM commande_ticket ct
             JOIN etape e ON e.id = ct.etape_id
             WHERE ct.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $ticket = $stmt->fetch();

        return $ticket === false ? null : $ticket;
    }

    // Passe le ticket à une autre étape (US.12)
    public function updateEtape(int $ticketId, int $etapeId): void
    {
        $stmt = $this->pdo->prepare('UPDATE commande_ticket SET etape_id = :etape_id WHERE id = :id');
        $stmt->execute(['etape_id' => $etapeId, 'id' => $ticketId]);
    }
}
