<?php
// Contrôleur de la page de test.
// Appelé depuis index.php quand l'URL est "/test".

declare(strict_types=1);

final class TestController
{
    // Affiche un message pour vérifier que la route /test fonctionne.
    public function index(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        echo '<h1>Page de test</h1>';
        echo '<p>La route <code>/test</code> fonctionne.</p>';
    }
}
