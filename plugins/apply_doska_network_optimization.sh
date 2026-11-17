#!/usr/bin/env bash
# ==============================================================================
# Script: apply_doska_network_optimization.sh
# Purpose: Mass optimization for all doska-*.ru sites on Server DE-222
# Author: Vladimir Bulantsev (@GinCz) & AI
# ==============================================================================

set -euo pipefail

TARGET_DOMAINS=(
    "doska-de.ru"
    "doska-esp.ru"
    "doska-fr.ru"
    "doska-gr.ru"
    "doska-hun.ru"
    "doska-isl.ru"
    "doska-it.ru"
    "doska-mld.ru"
    "doska-pl.ru"
    "doska-ua.ru"
)

MICRO_PLUGIN_SRC="/AI/GitHub/WordPress/plugins/wp-date-time-micro"
BACKUP_DIR="/root/backups_doski_opt"
mkdir -p "$BACKUP_DIR"

echo "=== STARTING MASS OPTIMIZATION ON DOSKA NETWORK ==="

for DOMAIN in "${TARGET_DOMAINS[@]}"; do
    SITE_PATH="/var/www/doski/data/www/${DOMAIN}"
    echo "----------------------------------------------------"
    echo ">>> Processing: ${DOMAIN} (${SITE_PATH})"

    if [ ! -d "${SITE_PATH}" ]; then
        echo "WARN: Directory ${SITE_PATH} not found. Skipping."
        continue
    fi

    # 1. DB Backup
    echo "1. Creating DB backup..."
    wp db export "${BACKUP_DIR}/${DOMAIN}_$(date +%Y%m%d_%H%M%S).sql" --path="${SITE_PATH}" --allow-root

    # 2. Deploy wp-date-time-micro
    echo "2. Deploying wp-date-time-micro..."
    DEST_PLUGIN="${SITE_PATH}/wp-content/plugins/wp-date-time-micro"
    mkdir -p "${DEST_PLUGIN}"
    cp -r "${MICRO_PLUGIN_SRC}/"* "${DEST_PLUGIN}/"
    chown -R doski:doski "${DEST_PLUGIN}"

    # 3. Activate wp-date-time-micro
    wp plugin activate wp-date-time-micro --path="${SITE_PATH}" --allow-root

    # 4. Remove wp-date-and-time-shortcode
    wp plugin deactivate wp-date-and-time-shortcode --path="${SITE_PATH}" --allow-root 2>/dev/null || true
    wp plugin delete wp-date-and-time-shortcode --path="${SITE_PATH}" --allow-root 2>/dev/null || true

    # 5. Remove wpforms-lite
    wp plugin deactivate wpforms-lite --path="${SITE_PATH}" --allow-root 2>/dev/null || true
    wp plugin delete wpforms-lite --path="${SITE_PATH}" --allow-root 2>/dev/null || true

    # 6. Drop orphaned DB tables
    echo "3. Cleaning orphaned DB tables..."
    wp db query "DROP TABLE IF EXISTS wp_wc_comments_subscription, wp_wc_feedback_forms, wp_wc_follow_users, wp_wc_phrases, wp_wc_users_rated, wp_wc_users_voted; DROP TABLE IF EXISTS wp_wpforms_analytics_forms, wp_wpforms_analytics_snapshots, wp_wpforms_logs, wp_wpforms_payment_meta, wp_wpforms_payments, wp_wpforms_product_events_queue, wp_wpforms_tasks_meta; DROP TABLE IF EXISTS wp_seopress_content_analysis; DROP TABLE IF EXISTS wp_hcaptcha_events; TRUNCATE TABLE wp_test_email_logs;" --path="${SITE_PATH}" --allow-root 2>/dev/null || true

    # 7. Delete expired transients
    echo "4. Deleting expired transients..."
    wp transient delete --expired --path="${SITE_PATH}" --allow-root 2>/dev/null || true

    # 8. Clean uploads folders
    echo "5. Cleaning orphaned upload directories..."
    rm -rf "${SITE_PATH}/wp-content/uploads/wpdiscuz"
    rm -rf "${SITE_PATH}/wp-content/uploads/wpforms"

    # 9. Verify
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://${DOMAIN}/")
    echo ">>> Finished ${DOMAIN} | HTTP Code: ${HTTP_CODE}"
done

echo "===================================================="
echo "=== ALL DOSKA SITES OPTIMIZATION COMPLETE! ==="
