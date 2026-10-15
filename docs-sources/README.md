# Sources des documents des marquoirs

Tout ce qu'il faut pour refaire les trois PDF des marquoirs. Ce dossier n'est pas servi par le site.

| Document | Source | Fichier publié |
|---|---|---|
| Mode d'emploi (joueurs, A4 paysage) | `mode-emploi/howto.html` + `mode-emploi/img/` | `public/assets/docs/Marquoirs_RBCD_mode_emploi.pdf` |
| Guide du directeur sportif (A4 paysage) | `guide-ds/head.html` + `guide-ds/body.html` + `guide-ds/img/` | `app/Docs/Marquoirs_RBCD_guide_DS.pdf` (membres connectés) |
| Stickers du pavé numérique (A4, échelle 1:1) | `stickers/make.py` | `build/Stickers_pave_RBCD.pdf` (à imprimer, pas publié) |

La version web du mode d'emploi est la vue `app/Views/public/pages/marquoirs_howto.php`, avec les images JPEG de `public/assets/images/marquoirs-howto/`. **Quand on modifie le texte, il faut le faire aux deux endroits** : `howto.html` pour le PDF, la vue PHP pour le site.

## Prérequis (Mac du club)

- MAMP démarré (site sur `http://localhost:8888/RBCD/CI4_2026/public/`, MySQL sur le port 8889).
- Google Chrome installé dans `/Applications`.
- Node.js (pour `puppeteer-core`, installé tout seul au premier lancement) et Python 3.

## Refaire les PDF (sans refaire les captures)

```bash
cd docs-sources
./build.sh
```

Le script régénère les deux PDF à leur place dans le projet, ainsi que les JPEG de la page web à partir des PNG. Il produit aussi `build/Stickers_pave_RBCD.pdf` et des aperçus page par page dans `build/`. Contrôlez les aperçus avant de commiter.

## Refaire des captures d'écran

Les identifiants MySQL sont lus dans le `.env` du projet et le code PIN du marquoir dans la base (`lib.cjs`) : rien n'est écrit en dur.

- **Mode d'emploi** :
  - `node mode-emploi/shots3.cjs mode-emploi/img` refait la page du billard, les formulaires amicaux (à 2 et à 3) et toute la partie à 3 (captures 02, 03, 21 à 25). Le script crée des rencontres de démonstration sur le billard 2 puis les efface.
  - `mode-emploi/shots.cjs` refait les captures d'origine (01, 04 à 13). Il attend un billard et une rencontre officielle de démonstration (voir l'en-tête du script). `13_direct_liste_crop.png` est un recadrage manuel de `13_direct_liste.png`.
  - `mode-emploi/upd.cjs` refait la capture de la moyenne proportionnelle (14).
  - `node mode-emploi/shots4.cjs mode-emploi/img` refait les captures des signatures : fin de partie et signature de la feuille de match (26, 27), rencontre d'équipe du jour et clôture par les capitaines (28, 29). Données de démonstration sur les billards 2 et 3, effacées à la fin. Après un premier ajout d'image, créez son JPEG dans `public/assets/images/marquoirs-howto/` (`build.sh` ne met à jour que les JPEG existants).
  - `node mode-emploi/web.cjs <url de la page How To> build` vérifie la page web : images cassées et débordement sur mobile.
- **Guide DS** :
  1. `php guide-ds/render.php`, lancé depuis la racine du projet, crée des rencontres, une rencontre d'équipe et une finale de démonstration, enregistre les pages admin dans `guide-ds/pages/`, puis efface tout.
  2. `node guide-ds/shoot.cjs "$PWD/guide-ds"` fait les captures dans `guide-ds/img/`.

Après de nouvelles captures, relancez `./build.sh`.
