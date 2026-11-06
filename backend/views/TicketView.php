<?php
// Vue des tickets : seul endroit qui fabrique les réponses JSON envoyées à React.

declare(strict_types=1);

final class TicketView
{
    // US.14 : les colonnes (étapes) et les cartes (tickets) en une seule réponse
    public function kanban(array $etapes, array $tickets): void
    {
        $this->send(200, ['status' => 'ok', 'etapes' => $etapes, 'tickets' => $tickets]);
    }

    // US.12 : confirme le changement et renvoie la nouvelle étape, pour que React déplace la carte
    public function etapeChangee(int $ticketId, array $etape): void
    {
        $this->send(200, [
            'status' => 'ok',
            'message' => 'Pièce passée à l\'étape : ' . $etape['nom'],
            'ticket_id' => $ticketId,
            'etape' => $etape,
        ]);
    }

    // Toutes les erreurs passent par ici, avec un message lisible (jamais le détail technique)
    public function error(int $code, string $message): void
    {
        $this->send($code, ['status' => 'error', 'message' => $message]);
    }

    // JSON_UNESCAPED_UNICODE : les accents restent lisibles ("Séchage" et non "S\u00e9chage")
    private function send(int $code, array $body): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
