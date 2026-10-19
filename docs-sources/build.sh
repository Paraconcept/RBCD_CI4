#!/bin/bash
# Régénère les trois PDF à partir des sources (les captures ne sont pas refaites : voir README.md).
set -e
cd "$(dirname "$0")"
[ -d node_modules ] || npm install --silent
ROOT=..
mkdir -p build

node pdf.cjs mode-emploi/howto.html "$ROOT/public/assets/docs/Marquoirs_RBCD_mode_emploi.pdf" build/apercus-mode-emploi

cat guide-ds/head.html guide-ds/body.html > guide-ds/guide.html
node pdf.cjs guide-ds/guide.html "$ROOT/app/Docs/Marquoirs_RBCD_guide_DS.pdf" build/apercus-guide-ds

# Caisse du bar : guide du comité (How to admin) et mode d'emploi du barman (How to caisse), servis aux membres connectés
node pdf.cjs caisse/admin.html "$ROOT/app/Docs/Caisse_RBCD_guide_admin.pdf" build/apercus-caisse-admin
node pdf.cjs caisse/barman.html "$ROOT/app/Docs/Caisse_RBCD_mode_emploi_barman.pdf" build/apercus-caisse-barman

python3 stickers/make.py stickers/stickers.html
node pdf.cjs stickers/stickers.html build/Stickers_pave_RBCD.pdf build/apercu-stickers

# Images de la page web « Marquoirs : How To » (JPEG 1600 px)
for f in mode-emploi/img/[0-9]*.png; do
  [ -f "$ROOT/public/assets/images/marquoirs-howto/$(basename "${f%.png}").jpg" ] || continue
  sips -Z 1600 -s format jpeg -s formatOptions 82 "$f" --out "$ROOT/public/assets/images/marquoirs-howto/$(basename "${f%.png}").jpg" >/dev/null
done
echo "OK — stickers : build/Stickers_pave_RBCD.pdf"
