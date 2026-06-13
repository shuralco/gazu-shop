#!/usr/bin/env bash
# Безпечний ручний hotfix blade/view на проді GAZU — БЕЗ холодного кешу.
#
# НІКОЛИ не роби `docker cp ... && php artisan view:clear` голим: view:clear
# без наступного view:cache лишає storefront холодним (перший хіт кожного типу
# сторінки рекомпілює ~394 blade ≈ 500ms). Цей скрипт робить правильну
# послідовність: cp → view:cache (атомарно clear+compile) → responsecache:clear
# → cache:warm (re-warm). Альтернатива — повний `Coolify deploy` (image-based
# контейнер; docker cp і так зникає на наступному деплої).
#
# Використання:
#   scripts/blade-hotfix.sh <локальний-файл> <шлях-у-контейнері>
#   напр.: scripts/blade-hotfix.sh resources/views/gazu/partials/footer.blade.php \
#                                  /var/www/html/resources/views/gazu/partials/footer.blade.php
set -euo pipefail

SRC="${1:?локальний файл}"
DEST="${2:?шлях у контейнері}"
HOST="${GAZU_HOST:-root@23.88.115.55}"
NAME_FILTER="${GAZU_CONTAINER:-bgkgc8ww0co8w4wo0kw0osck}"

SSH() { ssh -o StrictHostKeyChecking=no "$HOST" "$@"; }
CID="$(SSH "docker ps --filter name=${NAME_FILTER} -q | head -1")"
[ -n "$CID" ] || { echo "Контейнер не знайдено"; exit 1; }

echo "[hotfix] cp $SRC → ${CID}:${DEST}"
scp -o StrictHostKeyChecking=no "$SRC" "${HOST}:/tmp/_hotfix_$$" >/dev/null
SSH "docker cp /tmp/_hotfix_$$ ${CID}:${DEST} && rm -f /tmp/_hotfix_$$"

echo "[hotfix] view:cache (compile, НЕ голий view:clear)"
SSH "docker exec ${CID} php artisan view:cache"
echo "[hotfix] responsecache:clear"
SSH "docker exec ${CID} php artisan responsecache:clear || true"
echo "[hotfix] cache:warm (re-warm, у фоні)"
SSH "docker exec -d ${CID} php artisan cache:warm --products"

echo "[hotfix] done — storefront лишається теплим."
