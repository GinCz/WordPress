#!/usr/bin/env python3
import subprocess
import os
import sys

SITES = [
    "doska-cz.ru", "doska-de.ru", "doska-esp.ru", "doska-fr.ru",
    "doska-gr.ru", "doska-hun.ru", "doska-isl.ru", "doska-it.ru",
    "doska-mld.ru", "doska-pl.ru", "doska-ua.ru"
]

def run_cmd(cmd, cwd=None):
    res = subprocess.run(cmd, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, cwd=cwd)
    return res.stdout.strip(), res.stderr.strip(), res.returncode

def main():
    print("=== DEPLOYING WP-BOARD-DIRECTORY V1.20 & STANDARDIZING SEARCH PAGE ACROSS 11 SITES ===")
    
    for site in SITES:
        site_dir = f"/var/www/doski/data/www/{site}"
        if not os.path.isdir(site_dir):
            print(f"[SKIP] {site} not found")
            continue
            
        print(f"\n--- Site: {site} ---")
        
        # Flush transients
        run_cmd("wp eval 'delete_site_transient(\"_vladimir_ai_manifest\"); delete_site_transient(\"update_plugins\");' --allow-root", cwd=site_dir)
        
        # Update plugin
        run_cmd("wp plugin update wp-board-directory --allow-root", cwd=site_dir)
        
        # Verify version
        ver, _, _ = run_cmd("wp plugin get wp-board-directory --field=version --allow-root", cwd=site_dir)
        print(f"Active version: {ver}")
        
        # Find and update page 32 (or page with slug poisk-obyavlenij)
        page_id_out, _, _ = run_cmd("wp post list --post_type=page --name=poisk-obyavlenij --field=ID --allow-root", cwd=site_dir)
        page_id = page_id_out.strip()
        if not page_id:
            page_id = "32"
            
        run_cmd(f"wp post update {page_id} --post_content='[vb_search]' --allow-root", cwd=site_dir)
        print(f"Page {page_id} content updated to [vb_search]")

    print("\n=== DEPLOYMENT COMPLETED ===")

if __name__ == "__main__":
    main()
