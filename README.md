# Lovelace Céramique

Application web SaaS destinée aux artisans potiers et céramistes — projet collectif Ada Tech School (CDA).

**Nom de l'application :** PotterPilot. « Lovelace Céramique » désigne actuellement le projet.

## 1. Contexte et objectifs

L'application vise à faciliter le suivi de la production dans les petits ateliers de céramique : **façonnage → séchage → engobage/peinture → émaillage → cuisson → contrôle qualité**. Elle doit aider les artisans à connaître l'étape de chaque objet, les échéances (par exemple, 24 h de séchage), les tâches attribuées et les pertes (casse, fissure, craquelage), afin de respecter les délais.

### Cible prioritaire

Les céramistes qui fabriquent quotidiennement plusieurs objets et suivent plusieurs processus de production simultanément.

### Problème central

Chaque matin, Claire, céramiste et fondatrice d'un atelier, perd du temps à retrouver l'avancement des différentes pièces. Ce manque de visibilité peut provoquer des retards de fabrication et de livraison, et réduire la satisfaction des clients.

### Contraintes non négociables

- **Coût maîtrisé :** les petits ateliers disposent d'un budget limité.
- **Simplicité :** l'application doit être rapide et pratique à utiliser au quotidien.

## 2. Profils et droits envisagés

| Profil           | Responsabilités prévues                                                                                                                                                                        |
| ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Admin**        | Créer, supprimer et configurer les étapes, définir les échéances, déplacer les objets entre étapes, ajouter des membres et attribuer des rôles ; dispose également des droits de contribution. |
| **Contributeur** | Créer et attribuer des tâches, les valider et faire progresser les objets dans le workflow, selon les droits retenus.                                                                          |

> La répartition exacte des permissions devra être précisée avant l'implémentation de l'authentification et de l'autorisation.

## 3. Périmètre fonctionnel

### V1 — Must have

- Authentification au niveau de l'organisation : création et modification des utilisateurs et de leurs identifiants, gestion des mots de passe.
- Page de connexion.
- Création et gestion des étapes de production (droits Admin).
- Interface pour créer, lister et attribuer des tâches à un artisan.
- Suivi des délais de fabrication, de séchage et de cuisson, pour une pièce ou un volume.
- Suivi des pertes par étape (casse, fissure, craquelage, etc.).

### V1 — Should have

- Gestion des rôles et des droits.
- Authentification individuelle des utilisateurs.

### Hors V1 / à reconsidérer

- Notification de fin de cuisson (**Won't have**).
- Accumulation d'objets et notification indiquant qu'une cuisson peut être lancée (**Won't have**).
- Gestion des stocks de matières premières (**Won't have**).
- Gestion des commandes et des livraisons (**Won't have**, à confirmer).
- Calendrier et priorisation des commandes (**Should have**, mais actuellement classé hors V1 : périmètre à clarifier en équipe).

## 4. Environnement technique

| Couche             | Technologies                                    |
| ------------------ | ----------------------------------------------- |
| Frontend           | React, Vite, JavaScript (JSX), CSS, ESLint      |
| Backend            | PHP 8.5, PHP-FPM, PDO PostgreSQL, API HTTP/JSON |
| Base de données    | PostgreSQL 17                                   |
| Administration BDD | pgAdmin 4                                       |
| Infrastructure     | Docker Compose, Nginx                           |
| Développement      | Git, GitHub, Visual Studio Code, Node.js, npm   |

### Architecture de développement

```text
Navigateur
   |
   v
React + Vite (localhost:5173)
   |
   | GET /api/healthcheck
   v
Proxy Vite (/api -> localhost:8080)
   |
   v
Nginx (localhost:8080)
   |
   v
PHP-FPM 8.5
   |
   | PDO / SQL
   v
PostgreSQL 17
```

Le frontend React fonctionne **hors Docker** pendant le développement. Docker exécute Nginx, PHP, PostgreSQL et pgAdmin. Le proxy de `frontend/vite.config.js` redirige les requêtes `/api` vers Nginx ; les composants peuvent donc appeler `fetch('/api/healthcheck')` sans URL absolue.

## 5. Installation et démarrage

### Prérequis

- Git.
- Docker Desktop et Docker Compose (ou une installation Docker équivalente).
- Node.js et npm compatibles avec la version de Vite du projet.
- Un éditeur de code, par exemple Visual Studio Code.

**Windows + WSL 2 :** démarrer Docker Desktop et activer l'intégration de la distribution dans **Settings → Resources → WSL Integration**. Exécuter les commandes depuis le même environnement de travail.

### Étape 1 — Cloner le dépôt

```bash
git clone https://github.com/yocswar/lovelace-ceramique.git lovelace-ceramique
cd lovelace-ceramique
```

> Tant que la pull request d'architecture n'est pas fusionnée, les instructions React/PHP ci-dessous correspondent à la branche `chore/project-architecture`, et pas nécessairement à `main`.

### Étape 2 — Configurer l'environnement

```bash
cp .env.example .env
```

Adapter les valeurs si nécessaire. **Ne jamais commiter `.env`** : les identifiants fournis sont réservés au développement local.

### Étape 3 — Démarrer les services Docker

Depuis la racine du dépôt :

```bash
docker compose up -d --build
docker compose ps
```

| Service         | Accès par défaut      | Remarque                                           |
| --------------- | --------------------- | -------------------------------------------------- |
| Nginx / API PHP | http://localhost:8080 | Port publié par Docker                             |
| PHP-FPM         | `php:9000`            | Réseau Docker interne, pas une page web            |
| PostgreSQL      | `postgres:5432`       | Nom d'hôte interne Docker ; port hôte configurable |
| pgAdmin         | http://localhost:5050 | Interface d'administration                         |

Les ports dépendent des variables du fichier `.env`. Vérifier que PostgreSQL est **healthy** dans `docker compose ps`.

### Étape 4 — Vérifier le backend

Ouvrir http://localhost:8080/api/healthcheck ou lancer :

```bash
curl -i http://localhost:8080/api/healthcheck
```

Une réponse `HTTP 200 OK` avec `"status":"ok"` confirme que PHP a pu exécuter `SELECT 1` dans PostgreSQL. **La racine `http://localhost:8080/` n'est plus la page HTML de test initiale.**

### Étape 5 — Générer l'autoloader php

Dans ce projet, Composer est déjà disponible **dans le conteneur PHP** (pas besoin de l’installer sur ton Mac).

Depuis la **racine du projet** (là où se trouve `docker-compose.yml`) :

```bash
docker compose exec php composer --version
```

Tu dois voir un numéro de version (par exemple `Composer version 2.x...`).

> Si la commande échoue : vérifie que les conteneurs tournent (`docker compose ps`).

S'il n'est pas installé :

```bash
docker compose exec php composer install
```

Si tout est installé, régénérer l'autoloader (à faire à chaque modification ou ajout de classe dans les namespaces) :

```bash
docker compose exec php composer dump-autoload
```

### Étape 6 — Accéder à pgAdmin

Ouvrir http://localhost:5050.

Identifiants de développement par défaut (à vérifier dans `.env`) :

- **Email :** `ada@example.com`
- **Mot de passe :** `ada_dev`

Dans **Register → Server**, renseigner :

| Champ                | Valeur de développement |
| -------------------- | ----------------------- |
| Name                 | Lovelace                |
| Host name/address    | `postgres`              |
| Port                 | `5432`                  |
| Maintenance database | `lovelace`              |
| Username             | `lovelace`              |
| Password             | `lovelace_dev`          |

**Important :** dans pgAdmin exécuté sous Docker, l'hôte est **`postgres`**, et non `localhost`. Les identifiants de PostgreSQL doivent correspondre aux valeurs effectives de `.env` et de la base initialisée.

### Arrêter et redémarrer

- **Services Docker :** depuis la racine du dépôt, `docker compose down`.
- **Redémarrage :** `docker compose up -d`, puis `npm run dev` depuis `frontend/`.

Ne pas utiliser `docker compose down -v` pour un arrêt normal : l'option `-v` supprime les volumes Docker et peut entraîner une perte de données PostgreSQL.

### Dépannage rapide

| Problème                       | Vérification                                                         |
| ------------------------------ | -------------------------------------------------------------------- |
| Docker introuvable dans WSL    | Docker Desktop démarré et intégration WSL activée                    |
| Frontend indisponible          | `node -v`, `npm -v`, puis `npm ci` et `npm run dev` dans `frontend/` |
| Erreur de connexion dans React | Tester successivement les deux URL de healthcheck ci-dessous         |
| PostgreSQL indisponible        | `docker compose ps` et `docker compose logs postgres`                |
| Erreur PHP / Nginx             | `docker compose logs php` et `docker compose logs nginx`             |

```bash
curl -i http://localhost:8080/api/healthcheck
curl -i http://localhost:5173/api/healthcheck
```

Si la première URL fonctionne mais pas la seconde, vérifier le serveur Vite et son proxy.

## 6. API — Healthcheck

**Route :** `GET /api/healthcheck`

**Accès direct :** http://localhost:8080/api/healthcheck
**Accès via Vite :** http://localhost:5173/api/healthcheck

Cette route exécute `SELECT 1` via PDO pour vérifier la connexion à PostgreSQL.

**Réponse attendue — HTTP 200 :**

```json
{
  "status": "ok",
  "database": "connected",
  "result": 1,
  "message": "Connexion PostgreSQL OK"
}
```

En cas d'échec du service, l'API renvoie une erreur HTTP `503`. Une route inconnue renvoie `404`.

## 7. Contribution et conventions Git

- Partir d'un `main` à jour : `git switch main && git pull`.
- Créer une branche dédiée : `git switch -c feature/ma-feature`.
- Une branche par fonctionnalité ou correction.
- Ouvrir une **pull request** avant toute fusion vers `main` ; **au moins une approbation** est requise.
- Utiliser [Conventional Commits](https://www.conventionalcommits.org/) : `feat:`, `fix:`, `docs:`, `refactor:`, `chore:`.

### Nommage des branches

| Préfixe     | Usage                              | Exemple                         |
| ----------- | ---------------------------------- | ------------------------------- |
| `feature/`  | Nouvelle fonctionnalité            | `feature/catalogue-pieces`      |
| `fix/`      | Correction de bug                  | `fix/validation-email`          |
| `docs/`     | Documentation                      | `docs/readme-installation`      |
| `refactor/` | Restructuration du code            | `refactor/separation-services`  |
| `chore/`    | Configuration, dépendances, outils | `chore/mise-a-jour-dependances` |

Exemples : « Afficher le catalogue des pièces » → `feature/catalogue-pieces` ; « Corriger la connexion API » → `fix/connexion-postgres` ; « Documenter le choix PostgreSQL » → `docs/adr-choix-bdd`.

## 8. Outils et organisation du travail

**Outils :** Git, Docker Desktop, Visual Studio Code, outils d'IA (ChatGPT, Claude, GitHub Copilot, Qwen), Excalidraw (wireframes), Figma (prototypage).

**Rythme envisagé :**

- Sprints de **1 à 2 semaines**.
- **Sprint planning :** jeudi, une semaine sur deux, après la spécialisation DevOps (vers 16 h 15–16 h 30).
- **Daily :** vers 16 h 15–16 h 30 après les cours, idéalement tous les jours ou un jour sur deux.
- **Sprint review :** mercredi, une semaine sur deux, après la spécialisation DevOps (vers 16 h 15–16 h 30).
- **Rétrospective :** mercredi après la sprint review.

### Équipe

- Goundoba D — Scrum master.
- Esteban — Reviewer.
- Maxence Fournier — Développeur.
- Victor — Développeur.
- Emmanuel — Développeur.
- Tom — Chef de projet.

## 9. ADR — Décisions d'architecture

Les décisions d'architecture du projet sont documentées dans le fichier [ADR.md](docs/ADR.md).
