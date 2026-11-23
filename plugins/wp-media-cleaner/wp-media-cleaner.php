<?php
/**
 * Plugin Name: WP Media Cleaner (VladiMIR+AI✅)
 * Plugin URI:  https://github.com/GinCz/WordPress/tree/main/plugins/wp-media-cleaner
 * Description: Universal high-performance media cleaner for WordPress & WooCommerce. Safely scans, detects, and purges orphan images, unused WooCommerce product galleries, variation photos, and auto-deletes media on item removal.
 * Version:     2026-11__1.04
 * Author:      VladiMIR (GinCz) + AI
 * Author URI:  https://github.com/GinCz
 * License:     GPL-2.0-or-later
 * Update URI:  https://vladimir-ai.updates/wp-media-cleaner
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: wp-media-cleaner
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Shared auto-update client (guarded)
if ( file_exists( __DIR__ . '/vladimir-ai-updater.php' ) ) {
    require_once __DIR__ . '/vladimir-ai-updater.php';
}

// Shared plugin-list translations (8 languages)
if ( file_exists( __DIR__ . '/vladimir-ai-i18n.php' ) ) {
    require_once __DIR__ . '/vladimir-ai-i18n.php';
}

define( 'VB_MEDIA_CLEANER_VERSION', '2026-11__1.04' );
define( 'VB_MEDIA_CLEANER_PATH', plugin_dir_path( __FILE__ ) );
define( 'VB_MEDIA_CLEANER_URL', plugin_dir_url( __FILE__ ) );

final class WP_Media_Cleaner_App {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // 1. Auto-purge attached media when a post, page, WooCommerce product, or CPT is permanently deleted
        add_action( 'before_delete_post', [ $this, 'handle_before_delete_post' ] );

        // 2. Admin Menus & Assets
        if ( is_admin() ) {
            add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'register_plugin_action_links' ] );
            add_action( 'admin_post_vb_media_cleaner_purge', [ $this, 'handle_purge_post' ] );
            add_action( 'admin_post_vb_media_cleaner_save_settings', [ $this, 'handle_save_settings' ] );
        }

        // 3. WP-CLI
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'media-cleaner', [ $this, 'cli_command' ] );
        }
    }

    /**
     * Settings getter
     */
    public static function get_setting( $key, $default = '' ) {
        $options = get_option( 'wp_media_cleaner_settings', [
            'auto_purge' => 1, // Auto purge on post/product deletion
        ] );
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }

    /**
     * Action links on Plugins page
     */
    public function register_plugin_action_links( $links ) {
        $cleaner_link = '<a href="' . esc_url( admin_url( 'upload.php?page=wp-media-cleaner' ) ) . '"><strong>' . esc_html__( 'Очистка картинок', 'wp-media-cleaner' ) . '</strong></a>';
        array_unshift( $links, $cleaner_link );
        return $links;
    }

    /**
     * Admin menu registration under Media and Tools
     */
    public function register_admin_menu() {
        $page_title = 'Очистка картинок (Media Cleaner)';
        $menu_title = '🧹 Очистка картинок';

        // Add under Media (upload.php)
        add_media_page(
            $page_title,
            $menu_title,
            'manage_options',
            'wp-media-cleaner',
            [ $this, 'render_admin_page' ]
        );

        // Add under Tools (tools.php) as secondary entry point
        add_management_page(
            $page_title,
            $menu_title,
            'manage_options',
            'wp-media-cleaner',
            [ $this, 'render_admin_page' ]
        );
    }

    /**
     * Auto-purge media on post/product/variation deletion
     */
    public function handle_before_delete_post( $post_id ) {
        if ( ! self::get_setting( 'auto_purge', 1 ) ) {
            return;
        }

        $post = get_post( $post_id );
        if ( ! $post || 'attachment' === $post->post_type ) {
            return;
        }

        $post_type = $post->post_type;
        $attachment_ids_to_check = [];

        // 1. Featured image (_thumbnail_id)
        $thumb_id = get_post_thumbnail_id( $post_id );
        if ( $thumb_id ) {
            $attachment_ids_to_check[] = (int) $thumb_id;
        }

        // 2. WooCommerce Product Galleries (_product_image_gallery)
        $wc_gallery = get_post_meta( $post_id, '_product_image_gallery', true );
        if ( ! empty( $wc_gallery ) ) {
            $gallery_ids = is_array( $wc_gallery ) ? $wc_gallery : explode( ',', $wc_gallery );
            foreach ( $gallery_ids as $gid ) {
                $gid = (int) trim( $gid );
                if ( $gid > 0 ) {
                    $attachment_ids_to_check[] = $gid;
                }
            }
        }

        // 3. WooCommerce Product Variations (if parent product is being deleted)
        if ( 'product' === $post_type ) {
            $variations = get_posts( [
                'post_type'      => 'product_variation',
                'post_parent'    => $post_id,
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'post_status'    => 'any',
            ] );
            if ( ! empty( $variations ) ) {
                foreach ( $variations as $var_id ) {
                    $var_thumb = get_post_thumbnail_id( $var_id );
                    if ( $var_thumb ) {
                        $attachment_ids_to_check[] = (int) $var_thumb;
                    }
                }
            }
        }

        // 4. Classifieds, Directory & Custom Galleries (_vb_images, images, _gallery_images, etc.)
        $custom_gallery_keys = [ '_vb_images', 'images', '_gallery_images', '_listing_images', '_atbdp_gallery', '_atbdp_header_image' ];
        foreach ( $custom_gallery_keys as $key ) {
            $val = get_post_meta( $post_id, $key, true );
            if ( ! empty( $val ) ) {
                $arr = maybe_unserialize( $val );
                if ( is_string( $arr ) ) {
                    $arr = explode( ',', $arr );
                }
                if ( is_array( $arr ) ) {
                    foreach ( $arr as $aid ) {
                        $aid = (int) trim( $aid );
                        if ( $aid > 0 ) {
                            $attachment_ids_to_check[] = $aid;
                        }
                    }
                } elseif ( is_numeric( $arr ) && $arr > 0 ) {
                    $attachment_ids_to_check[] = (int) $arr;
                }
            }
        }

        // 5. Direct children attachments (post_parent = $post_id)
        $children = get_posts( [
            'post_type'      => 'attachment',
            'post_parent'    => $post_id,
            'posts_per_page' => -1,
            'post_status'    => 'inherit',
            'fields'         => 'ids',
        ] );
        if ( ! empty( $children ) ) {
            foreach ( $children as $cid ) {
                $attachment_ids_to_check[] = (int) $cid;
            }
        }

        $attachment_ids_to_check = array_unique( array_filter( $attachment_ids_to_check ) );

        // Safely delete attachments that are NOT used by any other post
        foreach ( $attachment_ids_to_check as $att_id ) {
            if ( self::is_attachment_safe_to_delete( $att_id, $post_id ) ) {
                wp_delete_attachment( $att_id, true );
            }
        }
    }

    /**
     * Check if an attachment is NOT used by any other active post or WooCommerce product
     */
    public static function is_attachment_safe_to_delete( $att_id, $excluding_post_id = 0 ) {
        global $wpdb;

        $att_id = (int) $att_id;
        if ( $att_id <= 0 ) {
            return false;
        }

        // 0. Protected media safeguard (logos, favicons, brand, documents, options, etc.)
        if ( self::is_protected_media( $att_id ) ) {
            return false;
        }

        // 1. Check if referenced as _thumbnail_id or _company_logo in any other post
        $other_thumb = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_thumbnail_id', '_company_logo') AND meta_value = %s AND post_id != %d LIMIT 1",
            $att_id,
            $excluding_post_id
        ) );
        if ( $other_thumb ) {
            return false;
        }

        // 2. Check if referenced in WooCommerce product galleries of another product
        $wc_galleries = $wpdb->get_results( $wpdb->prepare(
            "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_product_image_gallery' AND post_id != %d AND meta_value != ''",
            $excluding_post_id
        ) );
        foreach ( $wc_galleries as $g ) {
            $ids = array_map( 'intval', explode( ',', $g->meta_value ) );
            if ( in_array( $att_id, $ids, true ) ) {
                return false;
            }
        }

        // 3. Check if referenced in Classifieds / Directory galleries (_vb_images, images, etc.)
        $gallery_meta = $wpdb->get_col( $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_vb_images', 'images', '_gallery_images', '_listing_images', '_atbdp_gallery', '_atbdp_header_image') AND post_id != %d AND meta_value != ''",
            $excluding_post_id
        ) );
        foreach ( $gallery_meta as $val ) {
            if ( strpos( (string) $val, (string) $att_id ) === false ) {
                continue;
            }
            $decoded = json_decode( $val, true );
            if ( is_array( $decoded ) && in_array( $att_id, array_map( 'intval', $decoded ), true ) ) {
                return false;
            }
            $arr = maybe_unserialize( $val );
            if ( is_array( $arr ) && in_array( $att_id, array_map( 'intval', $arr ), true ) ) {
                return false;
            }
            if ( is_string( $arr ) && in_array( $att_id, array_map( 'intval', explode( ',', $arr ) ), true ) ) {
                return false;
            }
            if ( is_numeric( $arr ) && (int) $arr === $att_id ) {
                return false;
            }
        }

        // 4. Check if site icon, custom logo, or Flatsome logo
        $theme_logo_ids = array_filter( [
            (int) get_option( 'site_icon' ),
            (int) get_theme_mod( 'custom_logo' ),
            (int) get_theme_mod( 'site_logo' ),
            (int) get_theme_mod( 'site_logo_dark' ),
            (int) get_theme_mod( 'site_logo_sticky' ),
        ] );
        if ( in_array( $att_id, $theme_logo_ids, true ) ) {
            return false;
        }

        return true;
    }

    /**
     * Protected media: non-images (CSV/PDF/docs), logos, site icon (and the original
     * of a cropped icon), manual list (option vb_cleaner_protected_ids), anything
     * referenced by file name in options (theme mods, widgets) or content.
     */
    public static function is_protected_media( $id ) {
        global $wpdb;
        $id = (int) $id;
        $mime = (string) get_post_mime_type( $id );
        if ( 0 !== strpos( $mime, 'image/' ) ) {
            return true;
        }
        if ( in_array( $id, array_map( 'intval', (array) get_option( 'vb_cleaner_protected_ids', [] ) ), true ) ) {
            return true;
        }
        $logo_ids = array_filter( [ (int) get_option( 'site_icon' ), (int) get_theme_mod( 'custom_logo' ), (int) get_theme_mod( 'site_logo' ), (int) get_theme_mod( 'site_logo_dark' ), (int) get_theme_mod( 'site_logo_sticky' ) ] );
        if ( in_array( $id, $logo_ids, true ) ) {
            return true;
        }

        $rel = get_post_meta( $id, '_wp_attached_file', true );
        if ( ! $rel ) {
            return false;
        }
        $info = pathinfo( $rel );
        if ( preg_match( '/(logo|favicon|site[-_]?icon|brand)/i', $info['filename'] ) ) {
            return true;
        }
        $rel = get_post_meta( $id, '_wp_attached_file', true );
        if ( ! $rel ) {
            return false;
        }
        $info = pathinfo( $rel );
        $base = preg_replace( '/-scaled$/', '', $info['filename'] );
        if ( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s LIMIT 1", '%' . $wpdb->esc_like( 'cropped-' . $base . '.' ) . '%' ) ) ) {
            return true;
        }
        $like = '%' . $wpdb->esc_like( $info['dirname'] . '/' . $base ) . '%';
        if ( $wpdb->get_var( $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name NOT LIKE %s AND option_name NOT LIKE %s AND option_value LIKE %s LIMIT 1", '%transient%', 'vb_cleaner_protected_ids', $like ) ) ) {
            return true;
        }
        return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type NOT IN ('attachment','revision') AND post_status != 'trash' AND post_content LIKE %s LIMIT 1", $like ) );
    }

    /**
     * Comprehensive scan to identify all orphan media (WordPress + WooCommerce + Themes)
     */
    public static function scan_orphans() {
        global $wpdb;

        // 1. All Featured Images (_thumbnail_id)
        $thumb_ids = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value > 0" );

        // 2. WooCommerce Product Galleries (_product_image_gallery)
        $wc_gallery_meta = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_product_image_gallery' AND meta_value != ''" );
        $wc_gallery_ids = [];
        foreach ( $wc_gallery_meta as $val ) {
            $parts = explode( ',', $val );
            foreach ( $parts as $p ) {
                $p = (int) trim( $p );
                if ( $p > 0 ) {
                    $wc_gallery_ids[] = $p;
                }
            }
        }

        // 3. WooCommerce Product Categories / Term thumbnails (thumbnail_id in termmeta)
        $term_thumb_ids = [];
        $termmeta_table = $wpdb->prefix . 'termmeta';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$termmeta_table}'" ) === $termmeta_table ) {
            $term_thumb_ids = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$termmeta_table} WHERE meta_key = 'thumbnail_id' AND meta_value > 0" );
        }
        // Legacy woocommerce_termmeta table check
        $wc_termmeta_table = $wpdb->prefix . 'woocommerce_termmeta';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wc_termmeta_table}'" ) === $wc_termmeta_table ) {
            $wc_legacy_terms = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wc_termmeta_table} WHERE meta_key = 'thumbnail_id' AND meta_value > 0" );
            $term_thumb_ids = array_merge( $term_thumb_ids, $wc_legacy_terms );
        }

        // 4. Classifieds, Directory & Custom Galleries (_vb_images, _company_logo, images, etc.)
        $custom_gallery_meta = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_vb_images', '_company_logo', 'images', '_gallery_images', '_listing_images', '_atbdp_gallery', '_atbdp_header_image') AND meta_value != ''" );
        $custom_gallery_ids = [];
        foreach ( $custom_gallery_meta as $val ) {
            if ( is_string( $val ) ) {
                $decoded = json_decode( $val, true );
                if ( is_array( $decoded ) ) {
                    foreach ( $decoded as $id ) {
                        if ( is_numeric( $id ) && (int) $id > 0 ) {
                            $custom_gallery_ids[] = (int) $id;
                        }
                    }
                    continue;
                }
            }
            $arr = maybe_unserialize( $val );
            if ( is_string( $arr ) && strpos( $arr, ',' ) !== false ) {
                $arr = explode( ',', $arr );
            }
            if ( is_array( $arr ) ) {
                foreach ( $arr as $id ) {
                    if ( is_numeric( $id ) && (int) $id > 0 ) {
                        $custom_gallery_ids[] = (int) $id;
                    }
                }
            } elseif ( is_numeric( $arr ) && (int) $arr > 0 ) {
                $custom_gallery_ids[] = (int) $arr;
            }
        }

        // 4b. Explicit check for all directory companies (_company_logo)
        $company_logos = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_company_logo' AND meta_value > 0" );
        foreach ( $company_logos as $cl ) {
            if ( is_numeric( $cl ) && (int) $cl > 0 ) {
                $custom_gallery_ids[] = (int) $cl;
            }
        }

        // 5. Attachments with an active parent post (not in trash)
        $parent_ids = $wpdb->get_col( "SELECT a.ID FROM {$wpdb->posts} a JOIN {$wpdb->posts} p ON a.post_parent = p.ID WHERE a.post_type = 'attachment' AND a.post_parent > 0 AND p.post_type != 'attachment' AND p.post_status NOT IN ('trash', 'auto-draft')" );

        // 6. Theme elements: Site Icon, Custom Logo, Flatsome Logos (site_logo, site_logo_dark, site_logo_sticky) & Theme Mods
        $theme_mod_ids = array_filter( [
            (int) get_option( 'site_icon' ),
            (int) get_theme_mod( 'custom_logo' ),
            (int) get_theme_mod( 'site_logo' ),
            (int) get_theme_mod( 'site_logo_dark' ),
            (int) get_theme_mod( 'site_logo_sticky' ),
        ] );

        // Protect any attachment IDs referenced in any theme_mods_* options
        $theme_mod_options = $wpdb->get_col( "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE 'theme_mods_%'" );
        foreach ( $theme_mod_options as $tmo ) {
            $mods = maybe_unserialize( $tmo );
            if ( is_array( $mods ) ) {
                foreach ( $mods as $k => $v ) {
                    if ( is_numeric( $v ) && (int) $v > 0 ) {
                        if ( preg_match( '/(logo|icon|header|image|bg|banner|img)/i', (string) $k ) ) {
                            $theme_mod_ids[] = (int) $v;
                        }
                    }
                }
            }
        }
        $special_ids = array_unique( array_filter( $theme_mod_ids ) );

        // 7. Scan post_content for inline image IDs (Gutenberg, Flatsome, Shortcodes, UX Blocks)
        $content_ids = [];
        $posts_with_images = $wpdb->get_col( "SELECT post_content FROM {$wpdb->posts} WHERE post_type NOT IN ('attachment', 'revision') AND post_status NOT IN ('trash', 'auto-draft') AND (post_content LIKE '%wp-image-%' OR post_content LIKE '%\"id\":%' OR post_content LIKE '%[ux_%' OR post_content LIKE '%id=\"%' OR post_content LIKE '%bg=\"%' OR post_content LIKE '%img=\"%')" );
        foreach ( $posts_with_images as $cnt ) {
            // Match wp-image-123
            if ( preg_match_all( '/\bwp-image-(\d+)\b/', $cnt, $m ) ) {
                foreach ( $m[1] as $cid ) {
                    $content_ids[] = (int) $cid;
                }
            }
            // Match Gutenberg {"id":123}
            if ( preg_match_all( '/"id":\s*(\d+)/', $cnt, $m ) ) {
                foreach ( $m[1] as $cid ) {
                    $content_ids[] = (int) $cid;
                }
            }
            // Match Flatsome and standard shortcode attributes: id="123", bg="123", img="123", image="123"
            if ( preg_match_all( '/\b(?:id|bg|img|image)=[\'"](\d+)[\'"]/i', $cnt, $m ) ) {
                foreach ( $m[1] as $cid ) {
                    $content_ids[] = (int) $cid;
                }
            }
        }

        // Combine all in-use IDs
        $used_ids = array_unique( array_merge(
            $thumb_ids,
            $wc_gallery_ids,
            $term_thumb_ids,
            $custom_gallery_ids,
            $parent_ids,
            $special_ids,
            $content_ids
        ) );
        $used_ids = array_map( 'absint', $used_ids );
        $used_lookup = array_flip( $used_ids );

        // Fetch all attachments
        $all_attachments = $wpdb->get_results( "SELECT ID, post_title, post_date, post_mime_type, guid FROM {$wpdb->posts} WHERE post_type = 'attachment'", ARRAY_A );

        $orphans = [];
        $total_orphan_bytes = 0;

        foreach ( $all_attachments as $att ) {
            $id = (int) $att['ID'];
            if ( ! isset( $used_lookup[ $id ] ) ) {
                if ( self::is_protected_media( $id ) ) {
                    continue;
                }
                $file = get_attached_file( $id );
                $file_size = 0;
                if ( $file && file_exists( $file ) ) {
                    $file_size = filesize( $file );

                    // Include generated image sizes (thumbnails, medium, large, etc.)
                    $meta = wp_get_attachment_metadata( $id );
                    if ( ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
                        $dirname = dirname( $file );
                        foreach ( $meta['sizes'] as $size_info ) {
                            if ( ! empty( $size_info['file'] ) ) {
                                $subfile = $dirname . '/' . $size_info['file'];
                                if ( file_exists( $subfile ) ) {
                                    $file_size += filesize( $subfile );
                                }
                            }
                        }
                    }
                }

                $total_orphan_bytes += $file_size;

                $orphans[] = [
                    'id'        => $id,
                    'title'     => $att['post_title'] ?: basename( $file ?: $att['guid'] ),
                    'mime'      => $att['post_mime_type'],
                    'date'      => $att['post_date'],
                    'file'      => $file,
                    'size'      => $file_size,
                    'url'       => wp_get_attachment_thumb_url( $id ) ?: $att['guid'],
                ];
            }
        }

        return [
            'total_attachments'  => count( $all_attachments ),
            'used_count'         => max( 0, count( $all_attachments ) - count( $orphans ) ),
            'orphan_count'       => count( $orphans ),
            'orphan_bytes'       => $total_orphan_bytes,
            'orphan_mb'          => round( $total_orphan_bytes / ( 1024 * 1024 ), 2 ),
            'orphans'            => $orphans,
        ];
    }

    /**
     * Delete orphan attachments permanently
     */
    public static function purge_orphans( $ids = [] ) {
        if ( empty( $ids ) ) {
            $scan = self::scan_orphans();
            $ids = wp_list_pluck( $scan['orphans'], 'id' );
        }

        $count = 0;
        $freed_bytes = 0;

        foreach ( $ids as $id ) {
            $id = absint( $id );
            if ( ! $id || self::is_protected_media( $id ) ) {
                continue;
            }

            $file = get_attached_file( $id );
            if ( $file && file_exists( $file ) ) {
                $freed_bytes += filesize( $file );

                // Calculate sub-sizes
                $meta = wp_get_attachment_metadata( $id );
                if ( ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
                    $dirname = dirname( $file );
                    foreach ( $meta['sizes'] as $s ) {
                        if ( ! empty( $s['file'] ) ) {
                            $sub = $dirname . '/' . $s['file'];
                            if ( file_exists( $sub ) ) {
                                $freed_bytes += filesize( $sub );
                            }
                        }
                    }
                }
            }

            // Permanently delete attachment from DB and unlinks all files
            wp_delete_attachment( $id, true );
            $count++;
        }

        return [
            'count'       => $count,
            'freed_bytes' => $freed_bytes,
            'freed_mb'    => round( $freed_bytes / ( 1024 * 1024 ), 2 ),
        ];
    }

    /**
     * Handle Admin POST purge
     */
    public function handle_purge_post() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }

        check_admin_referer( 'vb_media_cleaner_purge_action', 'vb_purge_nonce' );

        $item_ids = array_map( 'absint', (array) ( $_POST['item_ids'] ?? [] ) );
        $item_ids = array_filter( $item_ids );

        $result = self::purge_orphans( $item_ids );

        $redirect = add_query_arg( [
            'page'    => 'wp-media-cleaner',
            'deleted' => $result['count'],
            'freed'   => $result['freed_mb'],
        ], admin_url( 'upload.php' ) );

        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Handle Admin Settings save
     */
    public function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }

        check_admin_referer( 'vb_media_cleaner_settings_action', 'vb_settings_nonce' );

        $auto_purge = isset( $_POST['auto_purge'] ) ? 1 : 0;
        update_option( 'wp_media_cleaner_settings', [
            'auto_purge' => $auto_purge,
        ] );

        $redirect = add_query_arg( [
            'page'  => 'wp-media-cleaner',
            'saved' => 1,
        ], admin_url( 'upload.php' ) );

        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Render Admin GUI Page
     */
    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $scan = self::scan_orphans();
        $auto_purge = self::get_setting( 'auto_purge', 1 );

        $deleted_n = isset( $_GET['deleted'] ) ? absint( $_GET['deleted'] ) : 0;
        $freed_mb  = isset( $_GET['freed'] ) ? floatval( $_GET['freed'] ) : 0;
        $is_saved  = ! empty( $_GET['saved'] );

        $wc_active = class_exists( 'WooCommerce' );
        ?>
        <div class="wrap" style="max-width: 1200px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; margin-bottom:20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
                <div>
                    <h1 style="display:inline-block; margin:0; font-size:24px; font-weight:700; color:#0f172a;">
                        🧹 Очистка картинок <span style="font-size:14px; font-weight:600; color:#10b981; background:#ecfdf5; border:1px solid #a7f3d0; padding:2px 8px; border-radius:12px; margin-left:6px;">VladiMIR+AI✅</span>
                    </h1>
                    <p style="margin:4px 0 0 0; color:#64748b; font-size:13px;">
                        Универсальный инструмент очистки неиспользуемых медиафайлов для <strong>WordPress</strong> и <strong>WooCommerce</strong>.
                        <?php if ( $wc_active ) : ?>
                            <span style="color:#7c3aed; font-weight:600;">🛒 WooCommerce активен (сканируются галереи товаров, вариации и категории).</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <a href="<?php echo esc_url( admin_url( 'upload.php?page=wp-media-cleaner' ) ); ?>" class="button button-secondary" style="font-weight:600;">
                        🔄 Сканировать заново
                    </a>
                </div>
            </div>

            <?php if ( $deleted_n > 0 ) : ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#10b981; padding:12px 16px; font-size:14px;">
                    ✅ <strong>Успешно очищено!</strong> Удалено <strong><?php echo $deleted_n; ?></strong> неиспользуемых файлов. Освобождено <strong><?php echo $freed_mb; ?> МБ</strong> дискового пространства сервера.
                </div>
            <?php endif; ?>

            <?php if ( $is_saved ) : ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#10b981;">
                    <p>💾 Настройки успешно сохранены.</p>
                </div>
            <?php endif; ?>

            <!-- STATS CARDS -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:15px; margin-bottom:25px;">
                <div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Всего медиафайлов</div>
                    <div style="font-size:26px; font-weight:700; color:#1e293b; margin:6px 0;"><?php echo number_format_i18n( $scan['total_attachments'] ); ?></div>
                    <small style="color:#64748b;">Зарегистрировано в WordPress</small>
                </div>

                <div style="background:#fff; border:1px solid #a7f3d0; border-radius:10px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05); background:#f0fdf4;">
                    <div style="font-size:12px; color:#047857; font-weight:600; text-transform:uppercase;">Используется на сайте</div>
                    <div style="font-size:26px; font-weight:700; color:#065f46; margin:6px 0;"><?php echo number_format_i18n( $scan['used_count'] ); ?></div>
                    <small style="color:#047857;">Защищено от удаления</small>
                </div>

                <div style="background:#fff; border:1px solid <?php echo $scan['orphan_count'] > 0 ? '#fca5a5' : '#cbd5e1'; ?>; border-radius:10px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05); <?php echo $scan['orphan_count'] > 0 ? 'background:#fef2f2;' : ''; ?>">
                    <div style="font-size:12px; color:<?php echo $scan['orphan_count'] > 0 ? '#b91c1c' : '#64748b'; ?>; font-weight:600; text-transform:uppercase;">Неиспользуемый мусор</div>
                    <div style="font-size:26px; font-weight:700; color:<?php echo $scan['orphan_count'] > 0 ? '#dc2626' : '#1e293b'; ?>; margin:6px 0;">
                        <?php echo number_format_i18n( $scan['orphan_count'] ); ?>
                    </div>
                    <small style="color:<?php echo $scan['orphan_count'] > 0 ? '#991b1b' : '#64748b'; ?>;">
                        Занимает: <strong><?php echo $scan['orphan_mb']; ?> МБ</strong> на диске
                    </small>
                </div>

                <div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Автоудаление фото</div>
                    <div style="font-size:15px; font-weight:700; color:<?php echo $auto_purge ? '#047857' : '#b91c1c'; ?>; margin:8px 0;">
                        <?php echo $auto_purge ? '⚡ Активно (Включено)' : '⛔ Отключено'; ?>
                    </div>
                    <small style="color:#64748b;">При удалении постов и товаров</small>
                </div>
            </div>

            <!-- SETTINGS COLLAPSIBLE -->
            <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:14px 18px; margin-bottom:20px;">
                <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <?php wp_nonce_field( 'vb_media_cleaner_settings_action', 'vb_settings_nonce' ); ?>
                    <input type="hidden" name="action" value="vb_media_cleaner_save_settings">
                    <label style="font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="auto_purge" value="1" <?php checked( $auto_purge, 1 ); ?> onchange="this.form.submit();">
                        <span>Автоматически и безвозвратно удалять прикрепленные картинки и галереи при удалении товаров WooCommerce, объявлений и записей</span>
                    </label>
                    <button type="submit" class="button button-secondary" style="font-size:12px;">💾 Сохранить опцию</button>
                </form>
            </div>

            <!-- ORPHAN LIST & PURGE ACTION -->
            <?php if ( $scan['orphan_count'] === 0 ) : ?>
                <div style="background:#f0fdf4; border:1px solid #a7f3d0; border-radius:10px; padding:35px 20px; text-align:center;">
                    <div style="font-size:42px; margin-bottom:10px;">🎉</div>
                    <h3 style="color:#065f46; font-size:18px; margin:0 0 8px 0;">Медиабиблиотека полностью чиста!</h3>
                    <p style="color:#047857; margin:0;">Все загруженные изображения используются на страницах, в товарах WooCommerce или в объявлениях.</p>
                </div>
            <?php else : ?>
                <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Вы действительно хотите удалить выбранные картинки? Все связанные файлы будут безвозвратно удалены с диска сервера.');">
                    <?php wp_nonce_field( 'vb_media_cleaner_purge_action', 'vb_purge_nonce' ); ?>
                    <input type="hidden" name="action" value="vb_media_cleaner_purge">

                    <div style="display:flex; justify-content:space-between; align-items:center; background:#fff; padding:12px 18px; border:1px solid #cbd5e1; border-radius:8px; margin-bottom:15px;">
                        <div>
                            <label style="font-weight:700; cursor:pointer; font-size:14px;">
                                <input type="checkbox" id="vb-select-all-orphans" onclick="document.querySelectorAll('.vb-orphan-chk').forEach(c => c.checked = this.checked);">
                                Выбрать все неиспользуемые (<?php echo $scan['orphan_count']; ?> шт.)
                            </label>
                        </div>
                        <div>
                            <button type="submit" class="button button-primary button-large" style="background:#dc2626; border-color:#b91c1c; font-weight:700;">
                                🗑️ Удалить все выбранные (<?php echo $scan['orphan_count']; ?> шт. / <?php echo $scan['orphan_mb']; ?> МБ)
                            </button>
                        </div>
                    </div>

                    <table class="wp-list-table widefat fixed striped" style="border-radius:8px; overflow:hidden;">
                        <thead>
                            <tr>
                                <th style="width:40px;"><input type="checkbox" onclick="document.getElementById('vb-select-all-orphans').click();"></th>
                                <th style="width:75px;">Превью</th>
                                <th>Имя файла / Название</th>
                                <th style="width:110px;">Размер</th>
                                <th style="width:140px;">Дата загрузки</th>
                                <th style="width:100px;">Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( array_slice( $scan['orphans'], 0, 150 ) as $item ) : ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $item['id'] ); ?>" class="vb-orphan-chk" checked>
                                    </td>
                                    <td>
                                        <img src="<?php echo esc_url( $item['url'] ); ?>" style="width:55px; height:45px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;" loading="lazy">
                                    </td>
                                    <td>
                                        <strong style="color:#0f172a;"><?php echo esc_html( $item['title'] ); ?></strong>
                                        <div style="font-size:11px; color:#64748b; word-break:break-all;"><?php echo esc_html( $item['file'] ); ?></div>
                                    </td>
                                    <td style="font-weight:600; color:#334155;">
                                        <?php echo round( $item['size'] / 1024, 1 ); ?> КБ
                                    </td>
                                    <td style="color:#64748b;">
                                        <?php echo esc_html( date_i18n( 'd.m.Y H:i', strtotime( $item['date'] ) ) ); ?>
                                    </td>
                                    <td>
                                        <button type="button" class="button button-small" style="color:#dc2626; border-color:#fca5a5;" onclick="if(confirm('Удалить эту картинку с сервера?')){document.querySelectorAll('.vb-orphan-chk').forEach(c => c.checked=false); document.getElementById('single-chk-<?php echo $item['id']; ?>').checked=true; this.closest('form').submit();}">Удалить</button>
                                        <input type="checkbox" id="single-chk-<?php echo $item['id']; ?>" name="item_ids[]" value="<?php echo esc_attr( $item['id'] ); ?>" style="display:none;">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ( $scan['orphan_count'] > 150 ) : ?>
                        <p style="margin-top:12px; color:#64748b; font-style:italic;">
                            Показано первые 150 файлов из <?php echo $scan['orphan_count']; ?>. При нажатии «Удалить» будут очищены все отмеченные на странице.
                        </p>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * WP-CLI Command: wp media-cleaner scan | wp media-cleaner clean
     */
    public function cli_command( $args, $assoc_args ) {
        $subcommand = isset( $args[0] ) ? $args[0] : 'scan';

        if ( 'scan' === $subcommand ) {
            WP_CLI::line( "Scanning media library for orphan files..." );
            $scan = self::scan_orphans();
            WP_CLI::line( "----------------------------------------" );
            WP_CLI::line( "Total media:    " . $scan['total_attachments'] );
            WP_CLI::line( "Active in use:  " . $scan['used_count'] );
            WP_CLI::line( "Orphan garbage: " . $scan['orphan_count'] );
            WP_CLI::line( "Orphan size:    " . $scan['orphan_mb'] . " MB" );
            WP_CLI::line( "----------------------------------------" );

            if ( $scan['orphan_count'] > 0 ) {
                WP_CLI::success( "Found {$scan['orphan_count']} orphan images ({$scan['orphan_mb']} MB). Run 'wp media-cleaner clean' to purge." );
            } else {
                WP_CLI::success( "Media library is completely clean!" );
            }
            return;
        }

        if ( 'clean' === $subcommand ) {
            $is_dry_run = isset( $assoc_args['dry-run'] );
            $scan = self::scan_orphans();

            if ( $scan['orphan_count'] === 0 ) {
                WP_CLI::success( "No orphan files found. Nothing to delete." );
                return;
            }

            if ( $is_dry_run ) {
                WP_CLI::success( "[DRY-RUN] Found {$scan['orphan_count']} orphan images ({$scan['orphan_mb']} MB). No files were deleted." );
                return;
            }

            if ( ! isset( $assoc_args['yes'] ) ) {
                WP_CLI::confirm( "Are you sure you want to permanently delete {$scan['orphan_count']} files ({$scan['orphan_mb']} MB)?" );
            }

            WP_CLI::line( "Purging {$scan['orphan_count']} orphan files..." );
            $res = self::purge_orphans();
            WP_CLI::success( "Deleted {$res['count']} orphan files! Freed {$res['freed_mb']} MB disk space." );
            return;
        }

        WP_CLI::error( "Unknown subcommand. Usage: wp media-cleaner [scan|clean] [--dry-run] [--yes]" );
    }
}

// Bootstrap
WP_Media_Cleaner_App::instance();
