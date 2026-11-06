<?php
// Modèle des étapes de fabrication (table etape).
// Seul fichier qui lit la table etape : le contrôleur lui demande la liste ou l'étape suivante.

declare(strict_types=1);

final class EtapeModel
{
    // La connexion est donnée par le contrôleur (injection de dépendance) :
    // une seule connexion partagée par tous les modèles d'une même requête
    public function __construct(private PDO $pdo) {}

    // Toutes les étapes, dans l'ordre de l'atelier : ce sont les colonnes du Kanban (US.14)
    public function findAll(): array
    {
        return $this->pdo
            ->query('SELECT id, nom, ordre FROM etape ORDER BY ordre')
            ->fetchAll();
    }

    // L'étape qui vient après (US.12).
    // "ordre > :ordre ... LIMIT 1" plutôt que "ordre = :ordre + 1" :
    // ça marche même s'il y a un trou dans la numérotation (1, 2, 4...).
    // Renvoie null si on est déjà à la dernière étape.
    public function findSuivante(int $ordre): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nom, ordre FROM etape WHERE ordre > :ordre ORDER BY ordre LIMIT 1'
        );
        $stmt->execute(['ordre' => $ordre]);
        $etape = $stmt->fetch();

        return $etape === false ? null : $etape;
    }
}
