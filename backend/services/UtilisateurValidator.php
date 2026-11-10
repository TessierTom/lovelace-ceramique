<?php

declare(strict_types=1);

namespace App\Services;

final class UtilisateurValidator
{
    /**
     * Vérifie les données de création d'un utilisateur admin + organisation.
     * Renvoie une liste d'erreurs. Liste vide = données OK.
     *
     * @return list<string>
     */
    public function valider(
        string $organisationNom,
        string $identifiant,
        string $mdp,
        string $email,
        string $nom,
    ): array {
        $erreurs = [];

        if ($organisationNom === '' || mb_strlen($organisationNom) > 50) {
            $erreurs[] = 'Le nom de l\'organisation est obligatoire (max. 50 caractères)';
        }

        if ($identifiant === '' || mb_strlen($identifiant) > 25) {
            $erreurs[] = 'L\'identifiant est obligatoire (max. 25 caractères)';
        }

        if ($mdp === '' || mb_strlen($mdp) < 8) {
            $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
            $erreurs[] = 'L\'email est invalide';
        }

        if ($nom === '' || mb_strlen($nom) > 50) {
            $erreurs[] = 'Le nom est obligatoire (max. 50 caractères)';
        }

        return $erreurs;
    }
}
