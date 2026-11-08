<?php

declare(strict_types=1);

// Accès à la table piece (modèles de pièce : Tasse, Bol, Vase…).
final class PieceModel
{
    public function __construct(
        private PDO $pdo,
    ) {}

    // Liste des modèles de pièce, triés par nom.
    public function lister(): array
    {
        $stmt = $this->pdo->query('SELECT id, nom FROM piece ORDER BY nom');
        $pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pieces as &$piece) {
            $piece['id'] = (int) $piece['id'];
        }
        unset($piece);

        return $pieces;
    }
}
