#!/usr/bin/env bash
# Move Nexora user uploads outside Git and link assets/uploads -> /var/www/nexora-uploads
# Run on Ubuntu production as a user with sudo, from the project root.
set -euo pipefail

UPLOAD_STORE="${NEXORA_UPLOAD_STORE:-/var/www/nexora-uploads}"
PROJECT_ROOT="${NEXORA_PROJECT_ROOT:-$(cd "$(dirname "$0")/.." && pwd)}"
LINK_PATH="${PROJECT_ROOT}/assets/uploads"
WEB_USER="${NEXORA_WEB_USER:-www-data}"
BACKUP_DIR="${NEXORA_UPLOAD_BACKUP:-/var/backups/nexora-uploads-$(date +%Y%m%d-%H%M%S)}"

echo "==> Project root: ${PROJECT_ROOT}"
echo "==> Upload store: ${UPLOAD_STORE}"
echo "==> Symlink path: ${LINK_PATH}"

if [[ ! -d "${PROJECT_ROOT}/assets" ]]; then
  echo "ERROR: ${PROJECT_ROOT}/assets not found. Set NEXORA_PROJECT_ROOT." >&2
  exit 1
fi

echo "==> Creating external upload directory"
sudo mkdir -p "${UPLOAD_STORE}"
sudo chown "${WEB_USER}:${WEB_USER}" "${UPLOAD_STORE}"
sudo chmod 775 "${UPLOAD_STORE}"

if [[ -e "${LINK_PATH}" && ! -L "${LINK_PATH}" ]]; then
  echo "==> Backing up existing repo uploads to ${BACKUP_DIR}"
  sudo mkdir -p "${BACKUP_DIR}"
  sudo cp -a "${LINK_PATH}/." "${BACKUP_DIR}/"
  echo "==> Copying existing files into ${UPLOAD_STORE}"
  sudo cp -an "${LINK_PATH}/." "${UPLOAD_STORE}/" || true
  echo "==> Removing repo uploads directory (files are preserved in ${UPLOAD_STORE})"
  sudo rm -rf "${LINK_PATH}"
elif [[ -L "${LINK_PATH}" ]]; then
  echo "==> Symlink already exists:"
  ls -la "${LINK_PATH}"
  CURRENT_TARGET="$(readlink -f "${LINK_PATH}")"
  if [[ "${CURRENT_TARGET}" == "${UPLOAD_STORE}" ]]; then
    echo "==> Symlink already points to ${UPLOAD_STORE}. Nothing to do."
    exit 0
  fi
  echo "ERROR: ${LINK_PATH} points to ${CURRENT_TARGET}, expected ${UPLOAD_STORE}" >&2
  exit 1
fi

echo "==> Creating symlink"
sudo ln -s "${UPLOAD_STORE}" "${LINK_PATH}"
sudo chown -h "${WEB_USER}:${WEB_USER}" "${LINK_PATH}" 2>/dev/null || true

echo "==> Bootstrapping upload subfolders"
sudo -u "${WEB_USER}" mkdir -p \
  "${UPLOAD_STORE}/images" \
  "${UPLOAD_STORE}/pdfs" \
  "${UPLOAD_STORE}/digital-featured" \
  "${UPLOAD_STORE}/digital-gallery" \
  "${UPLOAD_STORE}/printing-samples" \
  "${UPLOAD_STORE}/agro/items"
sudo chmod -R 775 "${UPLOAD_STORE}"

echo "==> Verification"
ls -la "${LINK_PATH}"
echo "Resolved path: $(readlink -f "${LINK_PATH}")"
echo "File count in store: $(find "${UPLOAD_STORE}" -type f | wc -l)"

echo ""
echo "Done. Public URLs remain: /assets/uploads/..."
echo "Git will never track ${LINK_PATH} (see .gitignore)."
