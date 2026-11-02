# ADR

## ADR 1 — Choix de la base de données

Date : 2026-16-10
Statut : Accepté

### Contexte

Nous devons choisir une base de données relationnelle pour stocker les données de l'application (utilisatrices, organisations, commandes, pièces).

### Options considérées

1. PostgreSQL — robuste, open source, SQL standard
2. MySQL — répandu, bonne documentation
3. SQLite — simple, pas de serveur nécessaire

### Décision

Nous avons choisi PostgreSQL car l'équipe a déjà de l'expérience avec PostgreSQL contrairement à MySQL et possibilités de faire des requêtes plus avancées dans le futur.
Nous n'avons pas choisi SQLite car dans l'avenir il ne semble pas adapter à la gestion multi-utilisateurs et car la gestion des pièces peuvent grandement alourdir la base.

### Conséquences

- Possibilités de réaliser des requêtes avancées
- Nécessite une instance PostgreSQL locale pour le développement
  → L'installation et la mise en place passe par le fichier docker-compose.yml qui sont documentées dans le README

## ADR 2 — Choix de la stack front

Date : 2026-20-10
Statut : Accepté

### Contexte

Nous devons choisir une stack front

### Options considérées

1. React (TS) — Performance, flexibilité, composabilité, typé
2. Angular (TS) — Structure bien définie, robustes, son écosystème riche, typé
3. Native Javascript — simple et léger

### Décision

Nous avons choisi React car l'équipe a déjà de l'expérience avec React contrairement à Angular et plus fexibles dans la création de composants.
Le native Javascript risque d'être trop léger en terme de fonctionnalités pour la taille du projet et le non typage pourrait complexifier le développement.

### Conséquences

- Réalisation de composant plus simple
- Installation plus complexe que le Native Javascript

## ADR 3 — Choix de la stack back

Date : 2026-20-10
Statut : Accepté

### Contexte

Nous devons choisir une stack back

### Options considérées

1. PHP — dynamique (types optionnels), interprété
2. C# — typage strict, web & client lourd (.exe), s'exécute sur .Net
3. Java — typage strict, web, s'exécute sur JVM
4. Node Express— dynamique (types optionnels), un seul langage front et back, asynchrone

### Décision

Nous avons choisi PHP car notre formateur a beaucoup d'expériences dessus et pouvait bien nous conseiller dessus. Il est aussi bien plus simple à mettre en production car la plupart des serveurs sait interprêter PHP.
Le C# et le Java sont nettement plus compliquer à installer et peu voire pas d'expériences sur ces langages.
Node Express est moins commun en architecture back.

### Conséquences

- Accès à de nombreux conseils du formateur
- Mise en place plus simple
- Typage possible mais moins strict
