# Sources des documents des marquoirs et de la caisse du bar

Tout ce qu'il faut pour refaire les PDF des marquoirs et de la caisse du bar. Ce dossier n'est pas servi par le site.

| Document | Source | Fichier publié |
|---|---|---|
| Mode d'emploi (joueurs, A4 paysage) | `mode-emploi/howto.html` + `mode-emploi/img/` | `public/assets/docs/Marquoirs_RBCD_mode_emploi.pdf` |
| Guide du directeur sportif (A4 paysage) | `guide-ds/head.html` + `guide-ds/body.html` + `guide-ds/img/` | `app/Docs/Marquoirs_RBCD_guide_DS.pdf` (membres connectés) |
| Caisse du bar — guide du comité (A4 paysage) | `caisse/admin.html` + `caisse/style.css` + `caisse/img/` | `app/Docs/Caisse_RBCD_guide_admin.pdf` (Admin › Caisse du bar › How to admin) |
| Caisse du bar — mode d'emploi du barman (A4 paysage, à imprimer) | `caisse/barman.html` + `caisse/style.css` + `caisse/img/` | `app/Docs/Caisse_RBCD_mode_emploi_barman.pdf` (Admin › Caisse du bar › How to caisse) |
| Caisse du bar — pas à pas (A4 paysage, grands caractères, une action par page, à imprimer) | `caisse/pas-a-pas.html` + `caisse/style.css` + `caisse/img-pas/` | `app/Docs/Caisse_RBCD_pas_a_pas.pdf` (Admin › Caisse du bar › How to pas à pas) |
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

Les identifiants MySQL sont lus dans le `.env` du projet et la clé d'accès au marquoir est créée en base à chaque lancement, comme appareil « Captures documentation » (`lib.cjs`) : rien n'est écrit en dur.

- **Mode d'emploi** :
  - `node mode-emploi/shots3.cjs mode-emploi/img` refait la page du billard, les formulaires amicaux (à 2 et à 3) et toute la partie à 3 (captures 02, 03, 21 à 25). Le script crée des rencontres de démonstration sur le billard 2 puis les efface.
  - `mode-emploi/shots.cjs` refait les captures d'origine (01, 04 à 13). Il attend un billard et une rencontre officielle de démonstration (voir l'en-tête du script). `13_direct_liste_crop.png` est un recadrage manuel de `13_direct_liste.png`.
  - `mode-emploi/upd.cjs` refait la capture de la moyenne proportionnelle (14).
  - `node mode-emploi/shots5.cjs mode-emploi/img` refait la capture du formulaire par sets (15), avec des points par set différents pour chaque joueur. Rien n'est enregistré.
  - `node mode-emploi/shots6.cjs mode-emploi/img` refait la capture de fin de set (17) et l'écran « Pause » (19) : parties amicales sur le billard 2, jouées au clavier puis effacées.
  - `node mode-emploi/shots4.cjs mode-emploi/img` refait les captures des signatures : fin de partie et signature de la feuille de match (26, 27), rencontre d'équipe du jour et clôture par les capitaines (28, 29). Données de démonstration sur les billards 2 et 3, effacées à la fin. Après un premier ajout d'image, créez son JPEG dans `public/assets/images/marquoirs-howto/` (`build.sh` ne met à jour que les JPEG existants).
  - `node mode-emploi/web.cjs <url de la page How To> build` vérifie la page web : images cassées et débordement sur mobile.
- **Guide DS** :
  1. `php guide-ds/render.php`, lancé depuis la racine du projet, crée des rencontres, une rencontre d'équipe et une finale de démonstration, enregistre les pages admin dans `guide-ds/pages/`, puis efface tout.
  2. `node guide-ds/shoot.cjs "$PWD/guide-ds"` fait les captures dans `guide-ds/img/`.

Après de nouvelles captures, relancez `./build.sh`.
- **Caisse du bar** : `node caisse/shots.cjs caisse/img` refait toutes les captures (écrans de la caisse au format tablette, récapitulatif, pages admin), sauf l'écran rouge de coupure Internet, refait par `node caisse/offline.cjs caisse/img`. Le script refuse de tourner si un service est ouvert dans la base locale ; il crée un service de démonstration avec des clients fictifs et deux appels de billards, remplace les vrais noms (barman, membres) par des noms fictifs, puis efface tout. La feuille de style `caisse/style.css` reprend celle du guide DS.
- **Caisse du bar, pas à pas** : `./caisse/pas-a-pas-shots.sh` refait les captures de `caisse/img-pas/` et `caisse/img-pas/targets.js` (position des boutons à toucher, d'où les cadres rouges numérotés du PDF). Il travaille sur une **copie** de la base (`<base>_docs`) : il y clôture les services restés ouverts, fait pointer le `.env` vers la copie le temps des captures, puis rétablit le `.env` et supprime la copie, même en cas d'erreur. La vraie base n'est jamais modifiée. L'écran rouge de coupure Internet est repris de `caisse/img/c10_hors_ligne.png`.
