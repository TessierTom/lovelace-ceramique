# BRIEF projet lovelace-ceramique

Nous avons pour l'instant plusieurs propositions pour le nom de l'application qui n'est pas encore confirmé :

- PotterPilot
- ClayFlow
- PotterFlow
- PlanPot
- ZenPot

# Contexte : Application à destination des artisans potiers et céramistes

Notre application Saas (Software As A Service) permet de pouvoir suivre le processus de production d’un atelier céramique de petite taille. Le but est de pouvoir créer des workflows de production (Façonnage —> Séchage —> Engobage/Peinture —> Emaillage —> Cuisson —> Contrôle Qualité) et de pouvoir répondre aux commandes dans les temps.

Les artisans doivent pouvoir savoir à quel étape sont les objets, définir des échéances (temps de séchage : 24h) quand les objets sont prêts pour passer à l’étape suivante, suivre les pertes (casses, fissure,…) et sur quel tâche chaque artisan travaille.

# Cible prioritaire

Les céramistes qui façonnent divers objets au quotidien et qui ont plusieurs processus de fabrications d'objets en même temps.

# Problème central

Chaque matin, Claire, céramiste et fondatrice d'un atelier de poterie, perd beaucoup de temps à se situer sur les différentes étapes des différents processus de création de poteries en cours. Cela peut entraîner des délais dans la fabrication, des retards dans les livraisons et une baisse de la satisfaction client.

# Contraintes non négociables

Les entreprises de céramiques sont généralement petites donc ils auront un budget restreint pour ce projet.
Il faut que l'usage de l'application au quotidien soit simple, pratique et rapide.

# Types profils

- Profil Admin : Peut créer des étapes, Faire passer des objets à une autre étape, supprimer des étapes, paramétrer une échéance, ajouter des membres, assigner des rôles en plus des droits Contributeur
- Profil Contributeur : Créer des tâches, les assigner à un utilisateur, validation des taches puis les déplacer dans chaque étapes de production jusqu’à ce que l’objet soit terminé

# Périmêtre V1

- Système d’authentification organisation
  - Créer, modifier utilisateur (identifiants, mots de passe) (Must have)
  - Gérer les rôles, droits (Should have)
  - Page de connexion (Must have)
- Système d’authentification utilisateur (Should have)
- Création et gestion d’une étape de production (droits admin) (Must have)
- Réalisation d'une interface pour créer des tâches, les lister et les assigner à un artisan (droits basiques) (Must have)
- Délais de fabrication pièce unique ou volume(séchage, cuisson) (Must have)
- Suivi des pertes au cours de la production, à quel étape ? (casses, fissure, craquelage,…) (Must Have)

# Hors V1

- Notification qu'une cuisson est terminée (Wont Have)
- Interface qui stocke petit à petit les objets et qui notifie quand la cuisson peut être lancé (Wont Have)
- Gestion des stocks de matières premières (Wont have)
- Gestion commandes et livraisons ? (Won’t Have)
- Calendrier des commandes priorisation des commandes (Should Have)
