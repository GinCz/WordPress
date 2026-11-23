<?php
/**
 * Plugin Name: WP Login & Admin Branding (VladiMIR+AI✅)
 * Plugin URI:  https://github.com/GinCz/WordPress/tree/main/plugins/wp-login-branding
 * Description: Ultra-modern turnkey customizer for WordPress authentication pages. Replaces standard WP logo with dynamic site brand and country flag, adds sleek glassmorphism card styling, responsive ambient glow background, custom favicon, social login styling, and custom brand footer.
 * Version:     2026-11__1.02
 * Author:      VladiMIR (GinCz) + AI
 * Author URI:  https://github.com/GinCz
 * License:     GPL-2.0-or-later
 * Update URI:  https://vladimir-ai.updates/wp-login-branding
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: wp-login-branding
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Shared auto-update client (guarded)
if ( file_exists( __DIR__ . '/vladimir-ai-updater.php' ) ) {
    require_once __DIR__ . '/vladimir-ai-updater.php';
}

// Shared plugin-list translations (7 languages)
if ( file_exists( __DIR__ . '/vladimir-ai-i18n.php' ) ) {
    require_once __DIR__ . '/vladimir-ai-i18n.php';
}

define( 'VB_LOGIN_BRANDING_VERSION', '2026-11__1.02' );

final class VB_Login_Branding {

    public static function init() {
        // Login page hooks
        add_action( 'login_head', [ __CLASS__, 'render_login_styles' ] );
        add_filter( 'login_headerurl', [ __CLASS__, 'filter_login_headerurl' ] );
        add_filter( 'login_headertext', [ __CLASS__, 'filter_login_headertext' ] );
        add_filter( 'login_message', [ __CLASS__, 'render_login_header_text' ] );
        add_action( 'login_footer', [ __CLASS__, 'render_login_footer' ] );

        // Admin head branding
        add_action( 'admin_head', [ __CLASS__, 'render_admin_brand_styles' ] );

        // Guarantee background auto-updates for this plugin via WP core
        add_filter( 'auto_update_plugin', [ __CLASS__, 'filter_auto_update_plugin' ], 20, 2 );
    }

    /**
     * Resolve site logo or country flag image.
     */
    public static function get_brand_logo_info() {
        $logo_url = '';

        // 1. Check theme_mod site_logo (Flatsome and modern themes)
        $logo_id = get_theme_mod( 'site_logo' );
        if ( ! empty( $logo_id ) ) {
            if ( is_numeric( $logo_id ) ) {
                $logo_url = wp_get_attachment_image_url( (int) $logo_id, 'full' );
            } elseif ( is_string( $logo_id ) ) {
                $logo_url = $logo_id;
            }
        }

        // 2. Fallback: custom_logo
        if ( empty( $logo_url ) ) {
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            if ( $custom_logo_id ) {
                $logo_url = wp_get_attachment_image_url( (int) $custom_logo_id, 'full' );
            }
        }

        // 3. Fallback: Check site icon
        if ( empty( $logo_url ) ) {
            $site_icon_id = get_option( 'site_icon' );
            if ( $site_icon_id ) {
                $logo_url = wp_get_attachment_image_url( (int) $site_icon_id, 'full' );
            }
        }

        // Determine country flag and info based on host / domain
        $host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( $_SERVER['HTTP_HOST'] ) : '';
        $country_info = [
            'flag'    => '🌍',
            'country' => 'Портал объявлений',
        ];

        if ( strpos( $host, '-de' ) !== false || strpos( $host, '.de' ) !== false ) {
            $country_info = [ 'flag' => '🇩🇪', 'country' => 'Германия' ];
        } elseif ( strpos( $host, '.cz' ) !== false || strpos( $host, '-cz' ) !== false ) {
            $country_info = [ 'flag' => '🇨🇿', 'country' => 'Чехия' ];
        } elseif ( strpos( $host, '-at' ) !== false || strpos( $host, '.at' ) !== false ) {
            $country_info = [ 'flag' => '🇦🇹', 'country' => 'Австрия' ];
        } elseif ( strpos( $host, '-pl' ) !== false || strpos( $host, '.pl' ) !== false ) {
            $country_info = [ 'flag' => '🇵🇱', 'country' => 'Польша' ];
        } elseif ( strpos( $host, '-es' ) !== false || strpos( $host, '.es' ) !== false ) {
            $country_info = [ 'flag' => '🇪🇸', 'country' => 'Испания' ];
        } elseif ( strpos( $host, '-it' ) !== false || strpos( $host, '.it' ) !== false ) {
            $country_info = [ 'flag' => '🇮🇹', 'country' => 'Италия' ];
        } elseif ( strpos( $host, '-fr' ) !== false || strpos( $host, '.fr' ) !== false ) {
            $country_info = [ 'flag' => '🇫🇷', 'country' => 'Франция' ];
        } elseif ( strpos( $host, '-ch' ) !== false || strpos( $host, '.ch' ) !== false ) {
            $country_info = [ 'flag' => '🇨🇭', 'country' => 'Швейцария' ];
        } elseif ( strpos( $host, '-uk' ) !== false || strpos( $host, '.uk' ) !== false || strpos( $host, '.co.uk' ) !== false ) {
            $country_info = [ 'flag' => '🇬🇧', 'country' => 'Великобритания' ];
        } elseif ( strpos( $host, '-us' ) !== false || strpos( $host, '.us' ) !== false ) {
            $country_info = [ 'flag' => '🇺🇸', 'country' => 'США' ];
        } elseif ( strpos( $host, '-ae' ) !== false || strpos( $host, '.ae' ) !== false ) {
            $country_info = [ 'flag' => '🇦🇪', 'country' => 'ОАЭ' ];
        }

        return [
            'logo_url'     => $logo_url,
            'flag'         => $country_info['flag'],
            'country_name' => $country_info['country'],
        ];
    }

    /**
     * Point logo link to site homepage instead of wordpress.org.
     */
    public static function filter_login_headerurl( $url ) {
        return home_url( '/' );
    }

    /**
     * Logo accessible text.
     */
    public static function filter_login_headertext( $text ) {
        return get_bloginfo( 'name' );
    }

    /**
     * Render site title and administration subtitle in two clean single lines directly under the flag.
     *
     * @param string $message Existing login messages.
     * @return string
     */
    public static function render_login_header_text( $message ) {
        $site_name = get_bloginfo( 'name' );
        $action    = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'login';

        $subtitle = 'Вход в администрирование сайта';
        if ( 'register' === $action ) {
            $subtitle = 'Регистрация на сайте';
        } elseif ( 'lostpassword' === $action || 'retrievepassword' === $action ) {
            $subtitle = 'Восстановление пароля';
        }

        $header_html = '<div class="vb-login-header-text">'
            . '<div class="vb-login-site-title" title="' . esc_attr( $site_name ) . '">' . esc_html( $site_name ) . '</div>'
            . '<div class="vb-login-site-subtitle">' . esc_html( $subtitle ) . '</div>'
            . '</div>';

        return $header_html . $message;
    }

    /**
     * Guarantee background auto-updates for this plugin via WP core auto-updater.
     *
     * @param bool|null $update Current auto-update verdict.
     * @param object    $item   Plugin item being considered for auto-update.
     * @return bool
     */
    public static function filter_auto_update_plugin( $update, $item ) {
        if ( ! empty( $item->slug ) && 'wp-login-branding' === $item->slug ) {
            return true;
        }
        return $update;
    }

    /**
     * Render sleek single-row navigation and brand footer below the login card.
     */
    public static function render_login_footer() {
        $can_register = (bool) get_option( 'users_can_register' );
        $register_url = wp_registration_url();
        $lost_url     = wp_lostpassword_url();
        $home_url     = home_url( '/' );
        $year         = date( 'Y' );
        $name         = get_bloginfo( 'name' );
        ?>
        <div class="vb-login-nav-row">
            <?php if ( $can_register ) : ?>
                <a href="<?php echo esc_url( $register_url ); ?>">Регистрация</a>
                <span class="vb-dot">&bull;</span>
            <?php endif; ?>
            <a href="<?php echo esc_url( $lost_url ); ?>">Забыли пароль?</a>
            <span class="vb-dot">&bull;</span>
            <a href="<?php echo esc_url( $home_url ); ?>">&larr; На главную</a>
        </div>
        <div class="vb-login-footer">
            <div>&copy; <?php echo esc_html( $year ); ?> <?php echo esc_html( $name ); ?> &bull; Все права защищены</div>
            <div style="margin-top: 3px; opacity: 0.75;">Designed by <a href="<?php echo esc_url( $home_url ); ?>" target="_blank">VladiMir + AI</a></div>
        </div>
        <?php
    }

    /**
     * Render state-of-the-art CSS styles for login/registration pages.
     */
    public static function render_login_styles() {
        $brand = self::get_brand_logo_info();
        $logo_url = $brand['logo_url'];
        ?>
        <style id="vb-login-branding-css">
        /* Reset & Canvas */
        html, body.login {
            height: auto !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body.login {
            background-color: #0d0614 !important;
            background-image: 
                radial-gradient(at 0% 0%, rgba(91, 33, 182, 0.5) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(124, 58, 237, 0.45) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(76, 29, 149, 0.5) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(63, 0, 119, 0.55) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(139, 92, 246, 0.25) 0px, transparent 65%) !important;
            background-attachment: fixed !important;
            background-size: cover !important;
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important;
            align-items: center !important;
            padding: 24px 14px 16px !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
        }

        /* Ambient glowing animated orb */
        @keyframes vbGlowPulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.04); opacity: 1; }
        }
        body.login::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 20%, rgba(168, 85, 247, 0.16) 0%, transparent 60%);
            animation: vbGlowPulse 7s ease-in-out infinite alternate;
            pointer-events: none;
            z-index: 0;
        }

        /* Main Container */
        #login {
            position: relative !important;
            z-index: 1 !important;
            padding: 6px 14px 10px !important;
            width: 100% !important;
            max-width: 410px !important;
            box-sizing: border-box !important;
            margin: 0 auto !important;
        }

        /* Logo Branding */
        .login h1 {
            margin: 0 0 8px 0 !important;
            text-align: center !important;
        }

        <?php if ( ! empty( $logo_url ) ) : ?>
        .login h1 a {
            background-image: url('<?php echo esc_url( $logo_url ); ?>') !important;
            background-size: contain !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            width: 100% !important;
            max-width: 220px !important;
            height: 56px !important;
            margin: 0 auto !important;
            display: block !important;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease !important;
            filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.35)) !important;
        }
        .login h1 a:hover {
            transform: scale(1.04) translateY(-1px) !important;
            filter: drop-shadow(0 10px 20px rgba(124, 58, 237, 0.5)) !important;
        }
        <?php else : ?>
        .login h1 a {
            display: none !important;
        }
        <?php endif; ?>

        /* Header Title & Subtitle under Flag */
        .vb-login-header-text {
            text-align: center !important;
            margin: 0 auto 12px auto !important;
            padding: 0 4px !important;
            max-width: 390px !important;
            box-sizing: border-box !important;
        }
        .vb-login-site-title {
            color: #ffffff !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            line-height: 1.3 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            letter-spacing: 0.25px !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6) !important;
        }
        .vb-login-site-subtitle {
            color: #c4b5fd !important;
            font-size: 12.5px !important;
            font-weight: 500 !important;
            line-height: 1.25 !important;
            margin-top: 2px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            letter-spacing: 0.2px !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5) !important;
        }

        /* Suppress obsolete badges */
        .vb-login-badge-wrap,
        .vb-login-page-badge {
            display: none !important;
        }

        /* Form Card */
        .login form {
            background: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 16px !important;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.2) !important;
            padding: 22px 24px 20px !important;
            margin-top: 0 !important;
        }

        /* Form Labels */
        .login label {
            font-size: 12.5px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            margin-bottom: 5px !important;
            display: block !important;
        }

        /* Form Inputs */
        .login form .input, 
        .login input[type="text"], 
        .login input[type="password"],
        .login input[type="email"] {
            background: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 9px !important;
            padding: 9px 12px !important;
            font-size: 14.5px !important;
            color: #0f172a !important;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.03) !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box !important;
            margin-bottom: 6px !important;
        }
        .login form .input:focus, 
        .login input[type="text"]:focus, 
        .login input[type="password"]:focus,
        .login input[type="email"]:focus {
            background: #ffffff !important;
            border-color: #7c3aed !important;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.18), inset 0 1px 2px rgba(0,0,0,0.03) !important;
            outline: none !important;
        }

        /* Checkbox & Forgetmenot */
        .login .forgetmenot {
            margin-bottom: 12px !important;
            float: none !important;
        }
        .login .forgetmenot label {
            font-size: 12.5px !important;
            font-weight: 500 !important;
            color: #64748b !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 7px !important;
            cursor: pointer !important;
        }
        .login input[type="checkbox"] {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 4px !important;
            width: 15px !important;
            height: 15px !important;
            cursor: pointer !important;
        }
        .login input[type="checkbox"]:checked {
            background-color: #7c3aed !important;
            border-color: #7c3aed !important;
        }

        /* Submit Button */
        .login .button-primary {
            width: 100% !important;
            height: auto !important;
            background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%) !important;
            border: none !important;
            border-radius: 9px !important;
            color: #ffffff !important;
            font-size: 14.5px !important;
            font-weight: 700 !important;
            padding: 10px 20px !important;
            line-height: 1.3 !important;
            text-shadow: none !important;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35) !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            margin-top: 8px !important;
            float: none !important;
            display: block !important;
        }
        .login .button-primary:hover, 
        .login .button-primary:focus {
            background: linear-gradient(135deg, #6d28d9 0%, #3b0764 100%) !important;
            box-shadow: 0 6px 18px rgba(124, 58, 237, 0.5) !important;
            transform: translateY(-1px) !important;
            color: #ffffff !important;
        }
        .login .button-primary:active {
            transform: translateY(0) !important;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3) !important;
        }

        /* Alerts & Messages */
        #login_error {
            border-radius: 10px !important;
            border-left: 4px solid #ef4444 !important;
            background: #ffffff !important;
            box-shadow: 0 8px 20px -5px rgba(0, 0, 0, 0.3) !important;
            padding: 12px 16px !important;
            margin-bottom: 14px !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
            color: #1e293b !important;
        }

        /* Suppress informational notice "Вы вышли из системы." */
        #login-message,
        .login .message, 
        .login p.message,
        .login .notice:not(.notice-error) {
            display: none !important;
        }

        /* Hide default standard WP #nav and #backtoblog */
        #nav, #backtoblog {
            display: none !important;
        }

        /* Sleek single-row navigation */
        .vb-login-nav-row {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 10px !important;
            margin: 14px 0 6px 0 !important;
            font-size: 13px !important;
            white-space: nowrap !important;
            flex-wrap: wrap !important;
        }
        .vb-login-nav-row a {
            color: #e2e8f0 !important;
            text-decoration: none !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
            padding: 3px 6px !important;
            border-radius: 6px !important;
        }
        .vb-login-nav-row a:hover {
            color: #ffffff !important;
            text-decoration: underline !important;
            background: rgba(255, 255, 255, 0.12) !important;
        }
        .vb-login-nav-row .vb-dot {
            color: rgba(255, 255, 255, 0.45) !important;
            font-size: 14px !important;
            user-select: none !important;
        }

        /* Hide language switcher for clean visual presentation */
        .language-switcher {
            display: none !important;
        }

        /* Social Login icons integration */
        .mo_openid_login_icons {
            margin-top: 14px !important;
            padding-top: 14px !important;
            border-top: 1px solid #e2e8f0 !important;
            text-align: center !important;
        }
        .mo_openid_login_icons p {
            font-size: 12.5px !important;
            color: #64748b !important;
            font-weight: 600 !important;
            margin-bottom: 10px !important;
        }

        /* Footer Copyright */
        .vb-login-footer {
            text-align: center;
            margin-top: 14px;
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.55);
            letter-spacing: 0.3px;
        }
        .vb-login-footer a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
        }
        .vb-login-footer a:hover {
            text-decoration: underline;
        }
            text-decoration: none;
            font-weight: 600;
        }
        .vb-login-footer a:hover {
            color: #ffffff;
            text-decoration: underline;
        }
        </style>
        <?php
    }

    /**
     * Admin styling: subtle accent on admin bar.
     */
    public static function render_admin_brand_styles() {
        if ( ! is_admin_bar_showing() ) return;
        ?>
        <style id="vb-admin-brand-css">
        #wpadminbar {
            background: #1e112a !important;
        }
        #wpadminbar .ab-item, #wpadminbar .ab-icon:before {
            color: #e2e8f0 !important;
        }
        #wpadminbar li:hover > .ab-item, #wpadminbar li.hover > .ab-item {
            background: #2e1065 !important;
            color: #ffffff !important;
        }
        </style>
        <?php
    }
}

add_action( 'init', [ 'VB_Login_Branding', 'init' ] );
