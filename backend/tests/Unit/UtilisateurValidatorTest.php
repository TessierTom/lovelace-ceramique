<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\UtilisateurValidator;
use PHPUnit\Framework\TestCase;

final class UtilisateurValidatorTest extends TestCase
{
    private UtilisateurValidator $validator;

    protected function setUp(): void
    {
        // Exécuté avant chaque test : on repart d'un validator propre
        $this->validator = new UtilisateurValidator();
    }

    public function test_donnees_valides_renvoient_aucune_erreur(): void
    {
        $erreurs = $this->validator->valider(
            'Atelier Claire',
            'claire',
            'motdepasse1',
            'claire@atelier.fr',
            'Claire Martin',
        );

        $this->assertSame([], $erreurs);
    }

    public function test_mot_de_passe_trop_court_est_refuse(): void
    {
        $erreurs = $this->validator->valider(
            'Atelier Claire',
            'claire',
            'court', // moins de 8 caractères
            'claire@atelier.fr',
            'Claire Martin',
        );

        $this->assertNotSame([], $erreurs);
        $this->assertContains(
            'Le mot de passe doit contenir au moins 8 caractères',
            $erreurs,
        );
    }

    public function test_mot_de_passe_exactement_8_caracteres_est_accepte(): void
    {
        $erreurs = $this->validator->valider(
            'Atelier Claire',
            'claire',
            'testtest', // exactement 8 caractères
            'claire@atelier.fr',
            'Claire Martin',
        );

        $this->assertSame([], $erreurs);
    }

    public function test_email_invalide_est_refuse(): void
    {
        $erreurs = $this->validator->valider(
            'Atelier Claire',
            'claire',
            'motdepasse1',
            'pas-un-email',
            'Claire Martin',
        );

        $this->assertNotSame([], $erreurs);
        $this->assertContains('L\'email est invalide', $erreurs);
    }

    public function test_organisation_vide_est_refuse(): void
    {
        $erreurs = $this->validator->valider(
            '',
            'claire',
            'motdepasse1',
            'pas-un-email',
            'Claire Martin',
        );

        $this->assertNotSame([], $erreurs);
        $this->assertContains('Le nom de l\'organisation est obligatoire (max. 50 caractères)', $erreurs);
    }

    public function test_organisation_trop_long_est_refuse(): void
    {
        $erreurs = $this->validator->valider(
            'un-nom-de-organisation-un-peu-trop-long-pour-etre-valide',
            'claire',
            'motdepasse1',
            'pas-un-email',
            'Claire Martin',
        );

        $this->assertNotSame([], $erreurs);
        $this->assertContains('Le nom de l\'organisation est obligatoire (max. 50 caractères)', $erreurs);
    }

    public function test_identifiant_vide_est_refuse(): void
    {
        $erreurs = $this->validator->valider(
            'Atelier Claire',
            '',
            'motdepasse1',
            'pas-un-email',
            'Claire Martin',
        );

        $this->assertNotSame([], $erreurs);
        $this->assertContains('L\'identifiant est obligatoire (max. 25 caractères)', $erreurs);
    }

    public function test_identifiant_trop_long_est_refuse(): void
    {
        $erreurs = $this->validator->valider(
            'Atelier Claire',
            'un-identifiant-trop-long-pour-etre-valide',
            'motdepasse1',
            'pas-un-email',
            'Claire Martin',
        );

        $this->assertNotSame([], $erreurs);
        $this->assertContains('L\'identifiant est obligatoire (max. 25 caractères)', $erreurs);
    }

    public function test_nom_vide_est_refuse(): void
    {
        $erreurs = $this->validator->valider(
            'Atelier Claire',
            'claire',
            'motdepasse1',
            'pas-un-email',
            '',
        );

        $this->assertNotSame([], $erreurs);
        $this->assertContains('Le nom est obligatoire (max. 50 caractères)', $erreurs);
    }

    public function test_tout_vide_renvoie_plusieurs_erreurs(): void
    {
        $erreurs = $this->validator->valider(
            '',
            '',
            '',
            '',
            '',
        );

        $this->assertNotSame([], $erreurs);
        $this->assertContains('Le nom de l\'organisation est obligatoire (max. 50 caractères)', $erreurs);
        $this->assertContains('L\'identifiant est obligatoire (max. 25 caractères)', $erreurs);
        $this->assertContains('Le mot de passe doit contenir au moins 8 caractères', $erreurs);
        $this->assertContains('L\'email est invalide', $erreurs);
        $this->assertContains('Le nom est obligatoire (max. 50 caractères)', $erreurs);
        $this->assertCount(5, $erreurs);
    }
}
