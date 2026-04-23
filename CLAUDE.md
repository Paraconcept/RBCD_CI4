# CLAUDE.md — RBC Disonais / CI4_2026

## Projet
Site web du **RBC Disonais** — club de billard carambole situé à **Dison, Belgique**.

## Stack technique
- **Framework** : CodeIgniter 4 (v4.7.2)
- **PHP** : via MAMP (macOS)
- **Base de données** : MySQL (MAMP), port 8889, DB = `rbcd_ci4_2026`
- **URL locale** : `http://localhost:8888/RBCD/CI4_2026/public/`
- **Environnement** : development (`.env`)

## Architecture CI4
- `app/` — code applicatif (Controllers, Models, Views, Config, Filters, etc.)
- `public/` — point d'entrée web (`index.php`), assets publics
- `writable/` — cache, logs, sessions (non versionné)
- `vendor/` — dépendances Composer (non versionné)

## Interface admin
- **Template** : **AdminLTE** (mode clair uniquement)
- AdminLTE installé dans `public/adminlte/` (assets CSS/JS)
- Les vues admin héritent du layout `app/Views/admin/layouts/main.php`
- Préfixe de route : `/admin`
- Filtre d'authentification sur tout le groupe `/admin`

## Interface publique
- En grande partie affichage de données issues de la DB
- Développée après l'admin

## Conventions de code
- **Langue** : français pour les libellés UI, anglais pour le code (variables, méthodes, tables)
- Controllers en PascalCase, méthodes en camelCase
- Models : un model par table, héritage `Model` CI4
- Vues : dossiers par section (`admin/`, `public/`)
- Pas de commentaires évidents dans le code ; commenter uniquement le "pourquoi non-évident"

## Base de données
- Préfixe de table : aucun
- Charset : `utf8mb4`, collation `utf8mb4_general_ci`
- Migrations CI4 dans `app/Database/Migrations/`
- Seeds dans `app/Database/Seeds/`

## Authentification admin
- Système maison (pas de Shield au départ, sauf décision contraire)
- Session CI4 native
- Table `users` avec rôles (admin, etc.)

## Notes importantes
- MAMP pro macOS : MySQL port **8889**, Apache port **8888**
- Le fichier `.env` n'est pas versionné (contient les credentials)
- `writable/` et `vendor/` exclus du versionning
- Toujours tester via `http://localhost:8888/RBCD/CI4_2026/public/`
