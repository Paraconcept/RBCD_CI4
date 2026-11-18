#!/bin/bash
# Refait les captures du guide « La caisse pas à pas » sur une COPIE de la base locale,
# pour ne jamais toucher aux services ou tickets de la vraie base (même s'il y a un service ouvert).
# Pendant quelques minutes, le site local pointe vers la copie ; le .env est rétabli à la fin, même en cas d'erreur.
set -e
cd "$(dirname "$0")/.."
ROOT=..
ENV="$ROOT/.env"
MYSQL=/Applications/MAMP/Library/bin/mysql
DUMP=/Applications/MAMP/Library/bin/mysqldump
val() { grep -E "^\s*database\.default\.$1\s*=" "$ENV" | head -1 | sed -E "s/^[^=]*=\s*'?([^'#]*)'?\s*$/\1/" | xargs; }
DB=$(val database); USER=$(val username); PASS=$(val password); PORT=$(val port)
COPY="${DB}_docs"
CONN=(-u"$USER" -p"$PASS" -h127.0.0.1 -P"$PORT")

cp "$ENV" "$ENV.docs-backup"
restore() {
  mv "$ENV.docs-backup" "$ENV"
  "$MYSQL" "${CONN[@]}" -e "drop database if exists \`$COPY\`" 2>/dev/null
  echo "Base d'origine rétablie ($DB), copie $COPY supprimée."
}
trap restore EXIT

"$MYSQL" "${CONN[@]}" -e "drop database if exists \`$COPY\`; create database \`$COPY\` character set utf8mb4 collate utf8mb4_general_ci" 2>/dev/null
"$DUMP" "${CONN[@]}" --single-transaction --routines "$DB" 2>/dev/null | "$MYSQL" "${CONN[@]}" "$COPY" 2>/dev/null
# Dans la copie seulement : les services restés ouverts sont clôturés
"$MYSQL" "${CONN[@]}" "$COPY" -e "update bar_services set status='closed', closed_at=now() where status='open'" 2>/dev/null
sed -i "" -E "s/^([[:space:]]*database\.default\.database[[:space:]]*=).*/\1 $COPY/" "$ENV"
grep -q "database.default.database = $COPY" "$ENV" || { echo "Impossible de pointer le .env vers la copie"; exit 1; }

node caisse/pas-a-pas-shots.cjs

# Captures réduites à 1600 px : assez pour l’impression A4, et un PDF léger
for f in caisse/img-pas/*.jpg; do sips -Z 1600 -s formatOptions 82 "$f" >/dev/null; done
