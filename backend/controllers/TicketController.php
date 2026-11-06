<?php
// Contrôleur des tickets : reçoit la requête, vérifie la méthode, demande aux modèles, répond via la vue.
// GET   /api/tickets                      → US.14 (voir les commandes à faire)
// PATCH /api/tickets/{id}/etape-suivante  → US.12 (passer la pièce à l'étape suivante)

declare(strict_types=1);

require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/EtapeModel.php';
require_once __DIR__ . '/../views/TicketView.php';

final class TicketController
{
    private TicketView $view;

    public function __construct()
    {
        $this->view = new TicketView();
    }

    // US.14 : les étapes (colonnes) + les pièces des commandes actives (cartes)
    public function index(): void
    {
        // GET = lire : on refuse les autres méthodes
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->view->error(405, 'Méthode non autorisée');
            return;
        }

        try {
            /** @var PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';

            $etapes = (new EtapeModel($pdo))->findAll();
            $tickets = (new TicketModel($pdo))->findActifs();

            $this->view->kanban($etapes, $tickets);
        } catch (Throwable $e) {
            error_log($e->getMessage()); // le détail va dans les logs Docker, jamais au navigateur
            $this->view->error(500, 'Impossible de charger les commandes');
        }
    }

    // US.12 : passe le ticket $id à l'étape suivante
    public function etapeSuivante(int $id): void
    {
        // PATCH = modifier une partie d'une donnée existante (ici : seulement l'étape du ticket)
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            $this->view->error(405, 'Méthode non autorisée');
            return;
        }

        try {
            /** @var PDO $pdo */
            $pdo = require __DIR__ . '/../config/db.php';
            $ticketModel = new TicketModel($pdo);
            $etapeModel = new EtapeModel($pdo);

            $ticket = $ticketModel->findById($id);
            if ($ticket === null) {
                $this->view->error(404, 'Ticket introuvable');
                return;
            }

            $suivante = $etapeModel->findSuivante((int) $ticket['ordre']);
            if ($suivante === null) {
                // 422 = la demande est comprise mais impossible : la pièce est déjà terminée
                $this->view->error(422, 'Cette pièce est déjà à la dernière étape');
                return;
            }

            $ticketModel->updateEtape($id, (int) $suivante['id']);
            $this->view->etapeChangee($id, $suivante);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->view->error(500, 'Impossible de changer l\'étape');
        }
    }
}
