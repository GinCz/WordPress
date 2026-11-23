<?php
/**
 * Plugin Name: WP Login & Admin Branding (VladiMIR+AI✅)
 * Plugin URI:  https://github.com/GinCz/WordPress/tree/main/plugins/wp-login-branding
 * Description: Ultra-modern turnkey customizer for WordPress authentication pages. Replaces standard WP logo with dynamic site brand or custom upload, adds gradient presets, custom background images, glassmorphism cards, entrance animations, ambient effects, and admin branding.
 * Version:     2026-11__1.04
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

// Shared plugin-list translations (8 languages)
if ( file_exists( __DIR__ . '/vladimir-ai-i18n.php' ) ) {
    require_once __DIR__ . '/vladimir-ai-i18n.php';
}

define( 'VB_LOGIN_BRANDING_VERSION', '2026-11__1.04' );

final class VB_Login_Branding {

    public static function init() {
        // Login page hooks
        add_action( 'login_head', [ __CLASS__, 'render_login_styles' ] );
        add_filter( 'login_headerurl', [ __CLASS__, 'filter_login_headerurl' ] );
        add_filter( 'login_headertext', [ __CLASS__, 'filter_login_headertext' ] );
        add_filter( 'login_message', [ __CLASS__, 'render_login_header_text' ] );
        add_action( 'login_footer', [ __CLASS__, 'render_login_footer' ] );

        // Admin menu & settings
        add_action( 'admin_menu', [ __CLASS__, 'register_admin_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ __CLASS__, 'add_plugin_action_links' ] );

        // Admin head branding
        add_action( 'admin_head', [ __CLASS__, 'render_admin_brand_styles' ] );

        // Guarantee background auto-updates for this plugin via WP core
        add_filter( 'auto_update_plugin', [ __CLASS__, 'filter_auto_update_plugin' ], 20, 2 );
    }

    /**
     * Default configuration options.
     */
    public static function get_options() {
        $defaults = [
            // 1. Logo
            'logo_mode'          => 'default', // 'default' (site logo from theme/customizer), 'custom' (uploaded image), 'text' (site name), 'none' (hidden)
            'custom_logo_url'    => '',
            'logo_width'         => 400,
            'logo_height'        => 150,
            'logo_link_url'      => '',
            'show_site_title'    => '1',
            'custom_site_title'  => '',
            'show_site_subtitle' => '1',
            'custom_subtitle'    => '',

            // 2. Background
            'bg_type'            => 'preset',  // 'preset', 'custom_gradient', 'image'
            'bg_preset'          => 'deep_ocean', // cosmic_purple | deep_ocean | dark_graphite | cyber_emerald | sunset_aurora | modern_light
            'custom_gradient'    => 'radial-gradient(circle at 50% 50%, #1e112a 0%, #0d0614 100%)',
            'bg_image_url'       => '',
            'bg_image_size'      => 'cover',
            'bg_overlay_color'   => '#000000',
            'bg_overlay_opacity' => 40,

            // 3. Effects & Style
            'card_animation'     => 'fade_slide_up', // fade_slide_up | zoom_in | pulse_glow | none
            'ambient_fx'         => 'ambient_orb',   // ambient_orb | floating_particles | none
            'card_style'         => 'glass_light',   // glass_light | glass_dark | solid_white
            'accent_color'       => '#7c3aed',

            // 4. Navigation & Admin Bar
            'show_nav_links'     => '1',
            'show_footer_copy'   => '1',
            'admin_bar_brand'    => '1',
        ];

        $saved = get_option( 'vb_login_branding_options', [] );
        return wp_parse_args( is_array( $saved ) ? $saved : [], $defaults );
    }

    /**
     * Curated premium gradient presets.
     */
    public static function get_presets() {
        return [
            'cosmic_purple' => [
                'name'   => 'Космический ультрафиолет (VladiMIR+AI)',
                'desc'   => 'Фирменный стиль: глубокий аметист, неоновый фиолет и мягкое свечение',
                'css'    => 'background-color: #0d0614 !important; background-image: radial-gradient(at 0% 0%, rgba(91, 33, 182, 0.5) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(124, 58, 237, 0.45) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(76, 29, 149, 0.5) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(63, 0, 119, 0.55) 0px, transparent 50%), radial-gradient(at 50% 50%, rgba(139, 92, 246, 0.25) 0px, transparent 65%) !important;',
                'thumb'  => 'linear-gradient(135deg, #0d0614 0%, #4c1d95 50%, #7c3aed 100%)',
                'accent' => '#7c3aed',
            ],
            'deep_ocean' => [
                'name'   => 'Глубокий океан (Deep Ocean)',
                'desc'   => 'Полночный сапфир, темный индиго и неоновый циан',
                'css'    => 'background-color: #030712 !important; background-image: radial-gradient(at 0% 0%, rgba(14, 116, 144, 0.5) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(2, 132, 199, 0.45) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(30, 58, 138, 0.5) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(15, 23, 42, 0.6) 0px, transparent 50%), radial-gradient(at 50% 50%, rgba(56, 189, 248, 0.2) 0px, transparent 65%) !important;',
                'thumb'  => 'linear-gradient(135deg, #030712 0%, #1e3a8a 50%, #0284c7 100%)',
                'accent' => '#0284c7',
            ],
            'dark_graphite' => [
                'name'   => 'Темный графит (Carbon Obsidian)',
                'desc'   => 'Премиальный матовый черный, серый уголь и сдержанный минимализм',
                'css'    => 'background-color: #09090b !important; background-image: radial-gradient(at 50% 0%, rgba(39, 39, 42, 0.6) 0px, transparent 60%), radial-gradient(at 100% 100%, rgba(24, 24, 27, 0.8) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(18, 18, 20, 0.9) 0px, transparent 50%) !important;',
                'thumb'  => 'linear-gradient(135deg, #09090b 0%, #18181b 50%, #3f3f46 100%)',
                'accent' => '#71717a',
            ],
            'cyber_emerald' => [
                'name'   => 'Изумрудный киберпанк (Cyber Emerald)',
                'desc'   => 'Глубокий малахит, темная хвоя и мятное неоновое свечение',
                'css'    => 'background-color: #022c22 !important; background-image: radial-gradient(at 0% 0%, rgba(5, 150, 105, 0.5) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.45) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(6, 78, 59, 0.6) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(2, 44, 34, 0.7) 0px, transparent 50%), radial-gradient(at 50% 50%, rgba(52, 211, 153, 0.2) 0px, transparent 65%) !important;',
                'thumb'  => 'linear-gradient(135deg, #022c22 0%, #065f46 50%, #10b981 100%)',
                'accent' => '#10b981',
            ],
            'sunset_aurora' => [
                'name'   => 'Закатная аврора (Sunset Aurora)',
                'desc'   => 'Пурпурный рубин, закатная малина и теплый янтарный акцент',
                'css'    => 'background-color: #1a0b1e !important; background-image: radial-gradient(at 0% 0%, rgba(225, 29, 72, 0.45) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(217, 70, 239, 0.45) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(136, 19, 55, 0.5) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(88, 28, 135, 0.55) 0px, transparent 50%), radial-gradient(at 50% 50%, rgba(244, 63, 94, 0.2) 0px, transparent 65%) !important;',
                'thumb'  => 'linear-gradient(135deg, #1a0b1e 0%, #881337 50%, #e11d48 100%)',
                'accent' => '#e11d48',
            ],
            'modern_light' => [
                'name'   => 'Современный светлый (Frost Glass)',
                'desc'   => 'Мягкий светлый жемчуг, пастельный лавандовый и чистый минимализм',
                'css'    => 'background-color: #f1f5f9 !important; background-image: radial-gradient(at 0% 0%, rgba(199, 210, 254, 0.6) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(224, 231, 255, 0.7) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(216, 180, 254, 0.5) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(243, 232, 255, 0.6) 0px, transparent 50%) !important;',
                'thumb'  => 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #c7d2fe 100%)',
                'accent' => '#6366f1',
            ],
        ];
    }

    /**
     * Automatically detect default site logo from theme mods, customizer, or site options.
     */
    public static function detect_default_site_logo() {
        $logo_url = '';
        $source   = '';

        // 1. Check Flatsome and modern themes: theme_mod 'site_logo'
        $site_logo = get_theme_mod( 'site_logo' );
        if ( ! empty( $site_logo ) ) {
            if ( is_numeric( $site_logo ) ) {
                $logo_url = wp_get_attachment_image_url( (int) $site_logo, 'full' );
                if ( $logo_url ) {
                    $source = 'Тема Flatsome (theme_mod: site_logo #' . (int) $site_logo . ')';
                }
            } elseif ( is_string( $site_logo ) ) {
                $logo_url = $site_logo;
                $source   = 'Тема Flatsome (theme_mod: site_logo URL)';
            }
        }

        // 2. Fallback: WordPress Core custom_logo (Customizer)
        if ( empty( $logo_url ) ) {
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            if ( $custom_logo_id ) {
                $logo_url = wp_get_attachment_image_url( (int) $custom_logo_id, 'full' );
                if ( $logo_url ) {
                    $source = 'WordPress Customizer (custom_logo #' . (int) $custom_logo_id . ')';
                }
            }
        }

        // 3. Fallback: Block themes / WP options site_logo
        if ( empty( $logo_url ) ) {
            $opt_logo = get_option( 'site_logo' );
            if ( $opt_logo ) {
                if ( is_numeric( $opt_logo ) ) {
                    $logo_url = wp_get_attachment_image_url( (int) $opt_logo, 'full' );
                } elseif ( is_string( $opt_logo ) ) {
                    $logo_url = $opt_logo;
                }
                if ( $logo_url ) {
                    $source = 'Настройки сайта (option: site_logo)';
                }
            }
        }

        // 4. Fallback: Check site icon (Favicon / App Icon)
        if ( empty( $logo_url ) ) {
            $site_icon_id = get_option( 'site_icon' );
            if ( $site_icon_id ) {
                $logo_url = wp_get_attachment_image_url( (int) $site_icon_id, 'full' );
                if ( $logo_url ) {
                    $source = 'Иконка сайта (option: site_icon #' . (int) $site_icon_id . ')';
                }
            }
        }

        return [
            'url'    => $logo_url,
            'source' => $source,
        ];
    }

    /**
     * Resolve site logo or brand info based on settings.
     */
    public static function get_brand_logo_info() {
        $options   = self::get_options();
        $logo_mode = $options['logo_mode'];
        $logo_url  = '';
        $is_text   = false;
        $is_hidden = false;

        if ( 'none' === $logo_mode ) {
            $is_hidden = true;
        } elseif ( 'text' === $logo_mode ) {
            $is_text = true;
        } elseif ( 'custom' === $logo_mode && ! empty( $options['custom_logo_url'] ) ) {
            $logo_url = $options['custom_logo_url'];
        } else {
            // 'default' mode: auto-detect from site
            $detected = self::detect_default_site_logo();
            $logo_url = $detected['url'];
            if ( empty( $logo_url ) ) {
                $is_text = true;
            }
        }

        return [
            'logo_url'  => $logo_url,
            'is_text'   => $is_text,
            'is_hidden' => $is_hidden,
        ];
    }

    /**
     * Point logo link to site homepage or custom link.
     */
    public static function filter_login_headerurl( $url ) {
        $options = self::get_options();
        if ( ! empty( $options['logo_link_url'] ) ) {
            return esc_url( $options['logo_link_url'] );
        }
        return home_url( '/' );
    }

    /**
     * Logo accessible text.
     */
    public static function filter_login_headertext( $text ) {
        $options = self::get_options();
        if ( ! empty( $options['custom_site_title'] ) ) {
            return esc_attr( $options['custom_site_title'] );
        }
        return get_bloginfo( 'name' );
    }

    /**
     * Render site title and administration subtitle under the logo.
     */
    public static function render_login_header_text( $message ) {
        $options = self::get_options();

        $show_title    = ! empty( $options['show_site_title'] ) && '0' !== $options['show_site_title'];
        $show_subtitle = ! empty( $options['show_site_subtitle'] ) && '0' !== $options['show_site_subtitle'];

        if ( ! $show_title && ! $show_subtitle ) {
            return $message;
        }

        $site_name = ! empty( $options['custom_site_title'] ) ? $options['custom_site_title'] : get_bloginfo( 'name' );
        $action    = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'login';

        $subtitle = ! empty( $options['custom_subtitle'] ) ? $options['custom_subtitle'] : 'Вход в администрирование сайта';
        if ( 'register' === $action ) {
            $subtitle = 'Регистрация на сайте';
        } elseif ( 'lostpassword' === $action || 'retrievepassword' === $action ) {
            $subtitle = 'Восстановление пароля';
        }

        $header_html = '<div class="vb-login-header-text">';
        if ( $show_title ) {
            $header_html .= '<div class="vb-login-site-title" title="' . esc_attr( $site_name ) . '">' . esc_html( $site_name ) . '</div>';
        }
        if ( $show_subtitle ) {
            $header_html .= '<div class="vb-login-site-subtitle">' . esc_html( $subtitle ) . '</div>';
        }
        $header_html .= '</div>';

        return $header_html . $message;
    }

    /**
     * Guarantee background auto-updates for this plugin via WP core auto-updater.
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
        $options          = self::get_options();
        $show_nav_links   = ! empty( $options['show_nav_links'] ) && '0' !== $options['show_nav_links'];
        $show_footer_copy = ! empty( $options['show_footer_copy'] ) && '0' !== $options['show_footer_copy'];

        if ( $show_nav_links ) {
            $can_register = (bool) get_option( 'users_can_register' );
            $register_url = wp_registration_url();
            $lost_url     = wp_lostpassword_url();
            $home_url     = home_url( '/' );
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
            <?php
        }

        if ( $show_footer_copy ) {
            $year     = date( 'Y' );
            $name     = ! empty( $options['custom_site_title'] ) ? $options['custom_site_title'] : get_bloginfo( 'name' );
            $home_url = home_url( '/' );
            ?>
            <div class="vb-login-footer">
                <div>&copy; <?php echo esc_html( $year ); ?> <?php echo esc_html( $name ); ?> &bull; Все права защищены</div>
                <div style="margin-top: 3px; opacity: 0.75;">Designed by <a href="<?php echo esc_url( $home_url ); ?>" target="_blank">VladiMir + AI</a></div>
            </div>
            <?php
        }
    }

    /**
     * Render state-of-the-art CSS styles for login/registration pages.
     */
    public static function render_login_styles() {
        $options     = self::get_options();
        $brand       = self::get_brand_logo_info();
        $logo_url    = $brand['logo_url'];
        $is_text     = $brand['is_text'];
        $is_hidden   = $brand['is_hidden'];
        $logo_width  = (int) $options['logo_width'];
        $logo_height = (int) $options['logo_height'];
        $accent      = ! empty( $options['accent_color'] ) ? $options['accent_color'] : '#7c3aed';

        // Background calculation
        $bg_css = '';
        $bg_type = $options['bg_type'];
        if ( 'preset' === $bg_type ) {
            $presets = self::get_presets();
            $preset_key = isset( $presets[ $options['bg_preset'] ] ) ? $options['bg_preset'] : 'cosmic_purple';
            $bg_css = $presets[ $preset_key ]['css'];
        } elseif ( 'custom_gradient' === $bg_type ) {
            $gradient = ! empty( $options['custom_gradient'] ) ? $options['custom_gradient'] : 'radial-gradient(circle at 50% 50%, #1e112a 0%, #0d0614 100%)';
            $bg_css = 'background-color: #0d0614 !important; background-image: ' . esc_attr( $gradient ) . ' !important; background-attachment: fixed !important; background-size: cover !important;';
        } elseif ( 'image' === $bg_type ) {
            $img_url = $options['bg_image_url'];
            $overlay_color = ! empty( $options['bg_overlay_color'] ) ? $options['bg_overlay_color'] : '#000000';
            $opacity = isset( $options['bg_overlay_opacity'] ) ? ( (int) $options['bg_overlay_opacity'] ) / 100 : 0.4;
            $rgba_overlay = self::hex_to_rgba( $overlay_color, $opacity );
            $size = ! empty( $options['bg_image_size'] ) ? $options['bg_image_size'] : 'cover';

            if ( ! empty( $img_url ) ) {
                $bg_css = 'background-color: #0d0614 !important; background-image: linear-gradient(' . esc_attr( $rgba_overlay ) . ', ' . esc_attr( $rgba_overlay ) . '), url(\'' . esc_url( $img_url ) . '\') !important; background-size: ' . esc_attr( $size ) . ' !important; background-position: center center !important; background-repeat: no-repeat !important; background-attachment: fixed !important;';
            } else {
                $bg_css = 'background-color: #0d0614 !important;';
            }
        }

        // Card animation
        $card_animation_css = '';
        $anim = $options['card_animation'];
        if ( 'fade_slide_up' === $anim ) {
            $card_animation_css = 'animation: vbCardSlideUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;';
        } elseif ( 'zoom_in' === $anim ) {
            $card_animation_css = 'animation: vbCardZoomIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;';
        } elseif ( 'pulse_glow' === $anim ) {
            $card_animation_css = 'animation: vbCardPulseGlow 0.85s ease-out forwards !important;';
        }

        // Card style
        $card_style = $options['card_style'];
        $card_style_css = '';
        if ( 'glass_dark' === $card_style ) {
            $card_style_css = '
            .login form {
                background: rgba(15, 12, 24, 0.88) !important;
                backdrop-filter: blur(24px) !important;
                -webkit-backdrop-filter: blur(24px) !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                border-radius: 16px !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(124, 58, 237, 0.25) !important;
                padding: 22px 24px 20px !important;
                margin-top: 0 !important;
            }
            .login label {
                color: #e2e8f0 !important;
            }
            .login form .input, .login input[type="text"], .login input[type="password"], .login input[type="email"] {
                background: rgba(30, 27, 46, 0.75) !important;
                border: 1.5px solid rgba(139, 92, 246, 0.35) !important;
                color: #ffffff !important;
            }
            .login .forgetmenot label {
                color: #cbd5e1 !important;
            }';
        } elseif ( 'solid_white' === $card_style ) {
            $card_style_css = '
            .login form {
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 14px !important;
                box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.4) !important;
                padding: 22px 24px 20px !important;
                margin-top: 0 !important;
            }';
        } else {
            // 'glass_light' (default)
            $card_style_css = '
            .login form {
                background: rgba(255, 255, 255, 0.96) !important;
                backdrop-filter: blur(20px) !important;
                -webkit-backdrop-filter: blur(20px) !important;
                border: 1px solid rgba(255, 255, 255, 0.7) !important;
                border-radius: 16px !important;
                box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.2) !important;
                padding: 22px 24px 20px !important;
                margin-top: 0 !important;
            }';
        }

        // Ambient FX
        $ambient_fx = $options['ambient_fx'];
        $ambient_css = '';
        if ( 'ambient_orb' === $ambient_fx ) {
            $ambient_css = '
            @keyframes vbGlowPulse {
                0%, 100% { transform: scale(1); opacity: 0.8; }
                50% { transform: scale(1.05); opacity: 1; }
            }
            body.login::before {
                content: "";
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: radial-gradient(circle at 50% 20%, ' . esc_attr( self::hex_to_rgba( $accent, 0.20 ) ) . ' 0%, transparent 60%);
                animation: vbGlowPulse 7s ease-in-out infinite alternate;
                pointer-events: none;
                z-index: 0;
            }';
        } elseif ( 'floating_particles' === $ambient_fx ) {
            $ambient_css = '
            @keyframes vbParticlesMove {
                0% { transform: scale(1) translateY(0px) rotate(0deg); }
                50% { transform: scale(1.1) translateY(-18px) rotate(2deg); }
                100% { transform: scale(1.04) translateY(14px) rotate(-2deg); }
            }
            body.login::before {
                content: "";
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: 
                    radial-gradient(circle at 20% 35%, ' . esc_attr( self::hex_to_rgba( $accent, 0.25 ) ) . ' 0%, transparent 25%),
                    radial-gradient(circle at 75% 65%, ' . esc_attr( self::hex_to_rgba( $accent, 0.18 ) ) . ' 0%, transparent 30%),
                    radial-gradient(circle at 50% 85%, rgba(192, 132, 252, 0.15) 0%, transparent 35%);
                animation: vbParticlesMove 18s ease-in-out infinite alternate;
                pointer-events: none;
                z-index: 0;
            }';
        }
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
            <?php echo $bg_css; ?>
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

        <?php echo $ambient_css; ?>

        /* Keyframe animations */
        @keyframes vbCardSlideUp {
            0% { opacity: 0; transform: translateY(24px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes vbCardZoomIn {
            0% { opacity: 0; transform: scale(0.92); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes vbCardPulseGlow {
            0% { opacity: 0; transform: translateY(14px); box-shadow: 0 0 0 rgba(124, 58, 237, 0); }
            50% { box-shadow: 0 0 35px <?php echo esc_attr( self::hex_to_rgba( $accent, 0.45 ) ); ?>; }
            100% { opacity: 1; transform: translateY(0); box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.5); }
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
            <?php echo $card_animation_css; ?>
        }

        /* Logo Branding */
        .login h1 {
            margin: 0 0 8px 0 !important;
            text-align: center !important;
            <?php if ( $is_hidden ) : ?>
            display: none !important;
            <?php endif; ?>
        }

        <?php if ( ! $is_hidden ) : ?>
            <?php if ( ! empty( $logo_url ) && ! $is_text ) : ?>
            .login h1 a {
                background-image: url('<?php echo esc_url( $logo_url ); ?>') !important;
                background-size: contain !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
                width: 100% !important;
                max-width: <?php echo (int) $logo_width; ?>px !important;
                height: <?php echo (int) $logo_height; ?>px !important;
                margin: 0 auto !important;
                display: block !important;
                transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease !important;
                filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.35)) !important;
            }
            .login h1 a:hover {
                transform: scale(1.04) translateY(-1px) !important;
                filter: drop-shadow(0 10px 20px <?php echo esc_attr( self::hex_to_rgba( $accent, 0.50 ) ); ?>) !important;
            }
            <?php else : ?>
            .login h1 a {
                background-image: none !important;
                text-indent: 0 !important;
                overflow: visible !important;
                width: auto !important;
                height: auto !important;
                display: inline-block !important;
                font-size: 24px !important;
                font-weight: 800 !important;
                color: #ffffff !important;
                letter-spacing: -0.5px !important;
                text-decoration: none !important;
                line-height: 1.25 !important;
                text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7) !important;
                transition: transform 0.2s ease !important;
            }
            .login h1 a:hover {
                transform: scale(1.03) !important;
                color: <?php echo esc_attr( $accent ); ?> !important;
            }
            <?php endif; ?>
        <?php endif; ?>

        /* Header Title & Subtitle under Logo */
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

        /* Form Card Styling */
        <?php echo $card_style_css; ?>

        /* Form Labels */
        .login label {
            font-size: 12.5px !important;
            font-weight: 600 !important;
            margin-bottom: 5px !important;
            display: block !important;
        }

        /* Form Inputs */
        .login form .input, 
        .login input[type="text"], 
        .login input[type="password"],
        .login input[type="email"] {
            border-radius: 9px !important;
            padding: 9px 12px !important;
            font-size: 14.5px !important;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.03) !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box !important;
            margin-bottom: 6px !important;
        }
        .login form .input:focus, 
        .login input[type="text"]:focus, 
        .login input[type="password"]:focus,
        .login input[type="email"]:focus {
            border-color: <?php echo esc_attr( $accent ); ?> !important;
            box-shadow: 0 0 0 3px <?php echo esc_attr( self::hex_to_rgba( $accent, 0.22 ) ); ?>, inset 0 1px 2px rgba(0,0,0,0.03) !important;
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
            background-color: <?php echo esc_attr( $accent ); ?> !important;
            border-color: <?php echo esc_attr( $accent ); ?> !important;
        }

        /* Submit Button */
        .login .button-primary {
            width: 100% !important;
            height: auto !important;
            background: linear-gradient(135deg, <?php echo esc_attr( $accent ); ?> 0%, <?php echo esc_attr( self::adjust_brightness( $accent, -35 ) ); ?> 100%) !important;
            border: none !important;
            border-radius: 9px !important;
            color: #ffffff !important;
            font-size: 14.5px !important;
            font-weight: 700 !important;
            padding: 10px 20px !important;
            line-height: 1.3 !important;
            text-shadow: none !important;
            box-shadow: 0 4px 14px <?php echo esc_attr( self::hex_to_rgba( $accent, 0.35 ) ); ?> !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            margin-top: 8px !important;
            float: none !important;
            display: block !important;
        }
        .login .button-primary:hover, 
        .login .button-primary:focus {
            background: linear-gradient(135deg, <?php echo esc_attr( self::adjust_brightness( $accent, -10 ) ); ?> 0%, <?php echo esc_attr( self::adjust_brightness( $accent, -45 ) ); ?> 100%) !important;
            box-shadow: 0 6px 18px <?php echo esc_attr( self::hex_to_rgba( $accent, 0.50 ) ); ?> !important;
            transform: translateY(-1px) !important;
            color: #ffffff !important;
        }
        .login .button-primary:active {
            transform: translateY(0) !important;
            box-shadow: 0 2px 8px <?php echo esc_attr( self::hex_to_rgba( $accent, 0.30 ) ); ?> !important;
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

        /* Suppress informational notice */
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
        $options = self::get_options();
        if ( empty( $options['admin_bar_brand'] ) || '0' === $options['admin_bar_brand'] ) {
            return;
        }
        if ( ! is_admin_bar_showing() ) return;
        $accent = ! empty( $options['accent_color'] ) ? $options['accent_color'] : '#7c3aed';
        ?>
        <style id="vb-admin-brand-css">
        #wpadminbar {
            background: #140d1f !important;
        }
        #wpadminbar .ab-item, #wpadminbar .ab-icon:before {
            color: #e2e8f0 !important;
        }
        #wpadminbar li:hover > .ab-item, #wpadminbar li.hover > .ab-item {
            background: <?php echo esc_attr( self::adjust_brightness( $accent, -40 ) ); ?> !important;
            color: #ffffff !important;
        }
        </style>
        <?php
    }

    /**
     * Register Admin Settings Menu.
     */
    public static function register_admin_menu() {
        add_options_page(
            'WP Login & Admin Branding (VladiMIR+AI✅)',
            'Брендинг входа',
            'manage_options',
            'wp-login-branding',
            [ __CLASS__, 'render_settings_page' ]
        );
    }

    /**
     * Add Settings link on Plugins page.
     */
    public static function add_plugin_action_links( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=wp-login-branding' ) ) . '">Настройки</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Enqueue media uploader and color picker for settings page.
     */
    public static function enqueue_admin_assets( $hook_suffix ) {
        if ( 'settings_page_wp-login-branding' !== $hook_suffix ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }

    /**
     * Register and sanitize settings.
     */
    public static function register_settings() {
        register_setting( 'vb_login_branding_group', 'vb_login_branding_options', [
            'sanitize_callback' => [ __CLASS__, 'sanitize_options' ],
        ] );
    }

    /**
     * Sanitize settings fields.
     */
    public static function sanitize_options( $input ) {
        $output = self::get_options();

        if ( isset( $input['logo_mode'] ) && in_array( $input['logo_mode'], [ 'default', 'custom', 'text', 'none' ], true ) ) {
            $output['logo_mode'] = $input['logo_mode'];
        }
        if ( isset( $input['custom_logo_url'] ) ) {
            $output['custom_logo_url'] = esc_url_raw( trim( $input['custom_logo_url'] ) );
        }
        if ( isset( $input['logo_width'] ) ) {
            $w = absint( $input['logo_width'] );
            $output['logo_width'] = ( $w >= 40 && $w <= 600 ) ? $w : 400;
        }
        if ( isset( $input['logo_height'] ) ) {
            $h = absint( $input['logo_height'] );
            $output['logo_height'] = ( $h >= 20 && $h <= 300 ) ? $h : 150;
        }
        if ( isset( $input['logo_link_url'] ) ) {
            $output['logo_link_url'] = esc_url_raw( trim( $input['logo_link_url'] ) );
        }

        $output['show_site_title'] = ! empty( $input['show_site_title'] ) ? '1' : '0';
        if ( isset( $input['custom_site_title'] ) ) {
            $output['custom_site_title'] = sanitize_text_field( $input['custom_site_title'] );
        }

        $output['show_site_subtitle'] = ! empty( $input['show_site_subtitle'] ) ? '1' : '0';
        if ( isset( $input['custom_subtitle'] ) ) {
            $output['custom_subtitle'] = sanitize_text_field( $input['custom_subtitle'] );
        }

        if ( isset( $input['bg_type'] ) && in_array( $input['bg_type'], [ 'preset', 'custom_gradient', 'image' ], true ) ) {
            $output['bg_type'] = $input['bg_type'];
        }
        if ( isset( $input['bg_preset'] ) ) {
            $presets = self::get_presets();
            if ( isset( $presets[ $input['bg_preset'] ] ) ) {
                $output['bg_preset'] = $input['bg_preset'];
            }
        }
        if ( isset( $input['custom_gradient'] ) ) {
            $output['custom_gradient'] = wp_strip_all_tags( trim( $input['custom_gradient'] ) );
        }
        if ( isset( $input['bg_image_url'] ) ) {
            $output['bg_image_url'] = esc_url_raw( trim( $input['bg_image_url'] ) );
        }
        if ( isset( $input['bg_image_size'] ) && in_array( $input['bg_image_size'], [ 'cover', 'contain', 'auto' ], true ) ) {
            $output['bg_image_size'] = $input['bg_image_size'];
        }
        if ( isset( $input['bg_overlay_color'] ) ) {
            $color = sanitize_hex_color( $input['bg_overlay_color'] );
            $output['bg_overlay_color'] = $color ? $color : '#000000';
        }
        if ( isset( $input['bg_overlay_opacity'] ) ) {
            $op = absint( $input['bg_overlay_opacity'] );
            $output['bg_overlay_opacity'] = min( 90, max( 0, $op ) );
        }

        if ( isset( $input['card_animation'] ) && in_array( $input['card_animation'], [ 'fade_slide_up', 'zoom_in', 'pulse_glow', 'none' ], true ) ) {
            $output['card_animation'] = $input['card_animation'];
        }
        if ( isset( $input['ambient_fx'] ) && in_array( $input['ambient_fx'], [ 'ambient_orb', 'floating_particles', 'none' ], true ) ) {
            $output['ambient_fx'] = $input['ambient_fx'];
        }
        if ( isset( $input['card_style'] ) && in_array( $input['card_style'], [ 'glass_light', 'glass_dark', 'solid_white' ], true ) ) {
            $output['card_style'] = $input['card_style'];
        }
        if ( isset( $input['accent_color'] ) ) {
            $acc = sanitize_hex_color( $input['accent_color'] );
            $output['accent_color'] = $acc ? $acc : '#7c3aed';
        }

        $output['show_nav_links']   = ! empty( $input['show_nav_links'] ) ? '1' : '0';
        $output['show_footer_copy'] = ! empty( $input['show_footer_copy'] ) ? '1' : '0';
        $output['admin_bar_brand']  = ! empty( $input['admin_bar_brand'] ) ? '1' : '0';

        return $output;
    }

    /**
     * Render modern turnkey settings page in WP Admin.
     */
    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $options   = self::get_options();
        $presets   = self::get_presets();
        $detected  = self::detect_default_site_logo();
        $login_url = wp_login_url();
        ?>
        <div class="wrap vb-settings-wrap">
            <div class="vb-admin-header">
                <div class="vb-header-left">
                    <h1 class="vb-admin-title">
                        <span class="vb-title-glow">WP Login & Admin Branding</span>
                        <span class="vb-version-tag">v<?php echo esc_html( VB_LOGIN_BRANDING_VERSION ); ?></span>
                    </h1>
                    <p class="vb-admin-desc">Ультрасовременная настройка страницы входа, логотипа, градиентов и эффектов.</p>
                </div>
                <div class="vb-header-right">
                    <a href="<?php echo esc_url( $login_url ); ?>" target="_blank" class="button vb-btn-preview">
                        <span class="dashicons dashicons-external" style="margin-top:4px; font-size:16px;"></span> Предпросмотр входа
                    </a>
                </div>
            </div>

            <?php if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) : ?>
                <div class="notice notice-success is-dismissible vb-notice">
                    <p><strong>✅ Настройки успешно сохранены и применены ко всем страницам авторизации!</strong></p>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php" id="vb-login-settings-form">
                <?php settings_fields( 'vb_login_branding_group' ); ?>

                <!-- SECTION 1: LOGO -->
                <div class="vb-card">
                    <div class="vb-card-header">
                        <h2>🎨 1. Логотип страницы входа</h2>
                        <span class="vb-badge">Идентификация бренда</span>
                    </div>
                    <div class="vb-card-body">
                        <!-- Detected Site Logo Status Box -->
                        <div class="vb-detected-box">
                            <div class="vb-detected-info">
                                <div class="vb-detected-title">Статус логотипа по умолчанию на сайте:</div>
                                <?php if ( ! empty( $detected['url'] ) ) : ?>
                                    <div class="vb-detected-success">
                                        <span class="dashicons dashicons-yes-alt" style="color:#10b981;"></span>
                                        <strong>Обнаружен логотип сайта:</strong> <code><?php echo esc_html( $detected['source'] ); ?></code>
                                    </div>
                                    <div class="vb-detected-url"><?php echo esc_html( $detected['url'] ); ?></div>
                                <?php else : ?>
                                    <div class="vb-detected-warning">
                                        <span class="dashicons dashicons-info" style="color:#f59e0b;"></span>
                                        Логотип сайта в теме не загружен. Плагин автоматически отобразит название сайта в стильном текстовом бейдже.
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ( ! empty( $detected['url'] ) ) : ?>
                                <div class="vb-detected-preview">
                                    <img src="<?php echo esc_url( $detected['url'] ); ?>" alt="Default Site Logo" style="max-height: 54px; max-width: 180px; object-fit: contain;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Logo Mode Selection -->
                        <div class="vb-field-row">
                            <label class="vb-label">Режим отображения логотипа:</label>
                            <div class="vb-radio-group">
                                <label class="vb-radio-option">
                                    <input type="radio" name="vb_login_branding_options[logo_mode]" value="default" <?php checked( $options['logo_mode'], 'default' ); ?>>
                                    <div class="vb-radio-content">
                                        <strong>Отображать логотип по умолчанию с сайта (Рекомендуется)</strong>
                                        <span>Автоматически берет логотип из темы Flatsome, Customizer или настроек сайта.</span>
                                    </div>
                                </label>

                                <label class="vb-radio-option">
                                    <input type="radio" name="vb_login_branding_options[logo_mode]" value="custom" <?php checked( $options['logo_mode'], 'custom' ); ?>>
                                    <div class="vb-radio-content">
                                        <strong>Загрузить свой отдельный логотип для входа</strong>
                                        <span>Использовать индивидуальную картинку специально для экрана авторизации.</span>
                                    </div>
                                </label>

                                <label class="vb-radio-option">
                                    <input type="radio" name="vb_login_branding_options[logo_mode]" value="text" <?php checked( $options['logo_mode'], 'text' ); ?>>
                                    <div class="vb-radio-content">
                                        <strong>Текстовое название сайта</strong>
                                        <span>Отображать стильный неоновый текст с названием сайта вместо картинки.</span>
                                    </div>
                                </label>

                                <label class="vb-radio-option">
                                    <input type="radio" name="vb_login_branding_options[logo_mode]" value="none" <?php checked( $options['logo_mode'], 'none' ); ?>>
                                    <div class="vb-radio-content">
                                        <strong>Скрыть логотип</strong>
                                        <span>Убрать блок логотипа полностью, оставив только карточку формы.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Custom Logo Upload Box (Conditional) -->
                        <div id="vb-custom-logo-section" class="vb-conditional-box" style="<?php echo ( 'custom' === $options['logo_mode'] ) ? '' : 'display:none;'; ?>">
                            <label class="vb-label" for="vb_custom_logo_url">URL пользовательского логотипа:</label>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <input type="text" id="vb_custom_logo_url" name="vb_login_branding_options[custom_logo_url]" value="<?php echo esc_attr( $options['custom_logo_url'] ); ?>" class="regular-text" style="flex:1;">
                                <button type="button" class="button vb-upload-media-btn" data-target="#vb_custom_logo_url" data-preview="#vb_custom_logo_preview">Выбрать из медиабиблиотеки</button>
                            </div>
                            <div id="vb-custom-logo-preview-wrap" style="margin-top:10px; <?php echo empty( $options['custom_logo_url'] ) ? 'display:none;' : ''; ?>">
                                <img id="vb_custom_logo_preview" src="<?php echo esc_url( $options['custom_logo_url'] ); ?>" style="max-height:60px; background:#140d1f; padding:8px 14px; border-radius:8px; border:1px solid #33264c;">
                            </div>
                        </div>

                        <!-- Logo Dimensions -->
                        <div class="vb-grid-2">
                            <div>
                                <label class="vb-label" for="vb_logo_width">Максимальная ширина логотипа (px):</label>
                                <input type="number" id="vb_logo_width" name="vb_login_branding_options[logo_width]" value="<?php echo esc_attr( $options['logo_width'] ); ?>" min="40" max="600" class="small-text">
                                <span class="vb-help">По умолчанию: 400px.</span>
                            </div>
                            <div>
                                <label class="vb-label" for="vb_logo_height">Максимальная высота логотипа (px):</label>
                                <input type="number" id="vb_logo_height" name="vb_login_branding_options[logo_height]" value="<?php echo esc_attr( $options['logo_height'] ); ?>" min="20" max="300" class="small-text">
                                <span class="vb-help">По умолчанию: 150px.</span>
                            </div>
                        </div>

                        <!-- Logo Link URL -->
                        <div style="margin-top:16px;">
                            <label class="vb-label" for="vb_logo_link_url">Ссылка при клике на логотип:</label>
                            <input type="text" id="vb_logo_link_url" name="vb_login_branding_options[logo_link_url]" value="<?php echo esc_attr( $options['logo_link_url'] ); ?>" placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>" class="regular-text" style="width:100%; max-width:480px;">
                            <span class="vb-help">Оставьте пустым, чтобы открывать главную страницу сайта.</span>
                        </div>

                        <!-- Site Title and Subtitle Options -->
                        <div class="vb-divider"></div>
                        <div class="vb-field-row">
                            <label class="vb-checkbox-label">
                                <input type="checkbox" name="vb_login_branding_options[show_site_title]" value="1" <?php checked( $options['show_site_title'], '1' ); ?>>
                                <strong>Отображать название сайта под логотипом</strong>
                            </label>
                            <div style="margin-top:6px; margin-left:24px;">
                                <input type="text" name="vb_login_branding_options[custom_site_title]" value="<?php echo esc_attr( $options['custom_site_title'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="regular-text">
                                <span class="vb-help">Если не заполнено, берется название из общих настроек WordPress.</span>
                            </div>
                        </div>

                        <div class="vb-field-row" style="margin-top:14px;">
                            <label class="vb-checkbox-label">
                                <input type="checkbox" name="vb_login_branding_options[show_site_subtitle]" value="1" <?php checked( $options['show_site_subtitle'], '1' ); ?>>
                                <strong>Отображать подзаголовок («Вход в администрирование сайта»)</strong>
                            </label>
                            <div style="margin-top:6px; margin-left:24px;">
                                <input type="text" name="vb_login_branding_options[custom_subtitle]" value="<?php echo esc_attr( $options['custom_subtitle'] ); ?>" placeholder="Вход в администрирование сайта" class="regular-text">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: BACKGROUND -->
                <div class="vb-card">
                    <div class="vb-card-header">
                        <h2>🌌 2. Фон страницы авторизации</h2>
                        <span class="vb-badge">Атмосфера и цветовые градиенты</span>
                    </div>
                    <div class="vb-card-body">
                        <!-- BG Type -->
                        <div class="vb-field-row">
                            <label class="vb-label">Тип оформления фона:</label>
                            <div class="vb-tabs-nav">
                                <label class="vb-tab-btn <?php echo ( 'preset' === $options['bg_type'] ) ? 'active' : ''; ?>">
                                    <input type="radio" name="vb_login_branding_options[bg_type]" value="preset" <?php checked( $options['bg_type'], 'preset' ); ?>>
                                    Готовые градиентные пресеты
                                </label>
                                <label class="vb-tab-btn <?php echo ( 'custom_gradient' === $options['bg_type'] ) ? 'active' : ''; ?>">
                                    <input type="radio" name="vb_login_branding_options[bg_type]" value="custom_gradient" <?php checked( $options['bg_type'], 'custom_gradient' ); ?>>
                                    Пользовательский CSS-градиент
                                </label>
                                <label class="vb-tab-btn <?php echo ( 'image' === $options['bg_type'] ) ? 'active' : ''; ?>">
                                    <input type="radio" name="vb_login_branding_options[bg_type]" value="image" <?php checked( $options['bg_type'], 'image' ); ?>>
                                    Фоновое изображение
                                </label>
                            </div>
                        </div>

                        <!-- TAB 1: PRESETS -->
                        <div id="vb-bg-pane-preset" class="vb-tab-pane" style="<?php echo ( 'preset' === $options['bg_type'] ) ? '' : 'display:none;'; ?>">
                            <div class="vb-presets-grid">
                                <?php foreach ( $presets as $key => $preset ) : ?>
                                    <label class="vb-preset-card <?php echo ( $options['bg_preset'] === $key ) ? 'selected' : ''; ?>">
                                        <input type="radio" name="vb_login_branding_options[bg_preset]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $options['bg_preset'], $key ); ?>>
                                        <div class="vb-preset-preview" style="background: <?php echo esc_attr( $preset['thumb'] ); ?>;"></div>
                                        <div class="vb-preset-info">
                                            <div class="vb-preset-name"><?php echo esc_html( $preset['name'] ); ?></div>
                                            <div class="vb-preset-desc"><?php echo esc_html( $preset['desc'] ); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- TAB 2: CUSTOM GRADIENT -->
                        <div id="vb-bg-pane-custom_gradient" class="vb-tab-pane" style="<?php echo ( 'custom_gradient' === $options['bg_type'] ) ? '' : 'display:none;'; ?>">
                            <label class="vb-label" for="vb_custom_gradient">CSS код градиента (linear-gradient / radial-gradient):</label>
                            <textarea id="vb_custom_gradient" name="vb_login_branding_options[custom_gradient]" rows="3" class="large-text code" style="font-family:monospace;"><?php echo esc_textarea( $options['custom_gradient'] ); ?></textarea>
                            <span class="vb-help">Пример: <code>radial-gradient(circle at 50% 50%, #1e112a 0%, #0d0614 100%)</code> или <code>linear-gradient(135deg, #111827 0%, #312e81 100%)</code>.</span>
                        </div>

                        <!-- TAB 3: IMAGE -->
                        <div id="vb-bg-pane-image" class="vb-tab-pane" style="<?php echo ( 'image' === $options['bg_type'] ) ? '' : 'display:none;'; ?>">
                            <label class="vb-label" for="vb_bg_image_url">Фоновое изображение:</label>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <input type="text" id="vb_bg_image_url" name="vb_login_branding_options[bg_image_url]" value="<?php echo esc_attr( $options['bg_image_url'] ); ?>" class="regular-text" style="flex:1;">
                                <button type="button" class="button vb-upload-media-btn" data-target="#vb_bg_image_url" data-preview="#vb_bg_image_preview">Выбрать фон из медиа</button>
                            </div>

                            <div id="vb-bg-image-preview-wrap" style="margin-top:10px; <?php echo empty( $options['bg_image_url'] ) ? 'display:none;' : ''; ?>">
                                <img id="vb_bg_image_preview" src="<?php echo esc_url( $options['bg_image_url'] ); ?>" style="max-height:120px; border-radius:8px; border:1px solid #33264c;">
                            </div>

                            <div class="vb-grid-3" style="margin-top:16px;">
                                <div>
                                    <label class="vb-label" for="vb_bg_image_size">Масштабирование фона:</label>
                                    <select id="vb_bg_image_size" name="vb_login_branding_options[bg_image_size]">
                                        <option value="cover" <?php selected( $options['bg_image_size'], 'cover' ); ?>>Cover (Заполнить весь экран)</option>
                                        <option value="contain" <?php selected( $options['bg_image_size'], 'contain' ); ?>>Contain (Вписать целиком)</option>
                                        <option value="auto" <?php selected( $options['bg_image_size'], 'auto' ); ?>>Auto (Оригинальный размер)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="vb-label" for="vb_bg_overlay_color">Цвет затемнения поверх фото:</label>
                                    <input type="text" id="vb_bg_overlay_color" name="vb_login_branding_options[bg_overlay_color]" value="<?php echo esc_attr( $options['bg_overlay_color'] ); ?>" class="vb-color-picker">
                                </div>
                                <div>
                                    <label class="vb-label" for="vb_bg_overlay_opacity">Плотность затемнения (%):</label>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <input type="range" id="vb_bg_overlay_opacity_range" min="0" max="90" step="5" value="<?php echo esc_attr( $options['bg_overlay_opacity'] ); ?>" style="width:140px;">
                                        <input type="number" id="vb_bg_overlay_opacity" name="vb_login_branding_options[bg_overlay_opacity]" value="<?php echo esc_attr( $options['bg_overlay_opacity'] ); ?>" min="0" max="90" class="small-text"> %
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: EFFECTS & CARD STYLE -->
                <div class="vb-card">
                    <div class="vb-card-header">
                        <h2>✨ 3. Визуальные эффекты и стиль формы</h2>
                        <span class="vb-badge">Анимации и глассморфизм</span>
                    </div>
                    <div class="vb-card-body">
                        <div class="vb-grid-2">
                            <!-- Card Animation -->
                            <div>
                                <label class="vb-label" for="vb_card_animation">Эффект появления формы при входе:</label>
                                <select id="vb_card_animation" name="vb_login_branding_options[card_animation]" class="regular-text" style="width:100%;">
                                    <option value="fade_slide_up" <?php selected( $options['card_animation'], 'fade_slide_up' ); ?>>Плавный подъем снизу (Fade In & Slide Up) — Рекомендуется</option>
                                    <option value="zoom_in" <?php selected( $options['card_animation'], 'zoom_in' ); ?>>Элегантное мягкое увеличение (Scale Zoom)</option>
                                    <option value="pulse_glow" <?php selected( $options['card_animation'], 'pulse_glow' ); ?>>Неоновая пульсация контура при загрузке</option>
                                    <option value="none" <?php selected( $options['card_animation'], 'none' ); ?>>Без анимации (Статичный вид)</option>
                                </select>
                                <span class="vb-help">Плавная аппаратная CSS-анимация при загрузке экрана.</span>
                            </div>

                            <!-- Ambient FX -->
                            <div>
                                <label class="vb-label" for="vb_ambient_fx">Фоновый динамический эффект:</label>
                                <select id="vb_ambient_fx" name="vb_login_branding_options[ambient_fx]" class="regular-text" style="width:100%;">
                                    <option value="ambient_orb" <?php selected( $options['ambient_fx'], 'ambient_orb' ); ?>>Пульсирующее «дышащее» свечение (Ambient Glow)</option>
                                    <option value="floating_particles" <?php selected( $options['ambient_fx'], 'floating_particles' ); ?>>Парящие световые сферы (Floating Bokeh)</option>
                                    <option value="none" <?php selected( $options['ambient_fx'], 'none' ); ?>>Без эффектов (Чистый фон)</option>
                                </select>
                                <span class="vb-help">Мягкая атмосферная динамика без нагрузки на процессор.</span>
                            </div>
                        </div>

                        <div class="vb-grid-2" style="margin-top:16px;">
                            <!-- Card Style -->
                            <div>
                                <label class="vb-label" for="vb_card_style">Стиль карточки входа (Glassmorphism):</label>
                                <select id="vb_card_style" name="vb_login_branding_options[card_style]" class="regular-text" style="width:100%;">
                                    <option value="glass_light" <?php selected( $options['card_style'], 'glass_light' ); ?>>Матовое стекло Light Frost (Светлый глассморфизм 20px blur) — По умолчанию</option>
                                    <option value="glass_dark" <?php selected( $options['card_style'], 'glass_dark' ); ?>>Матовое стекло Dark Obsidian (Темный глассморфизм)</option>
                                    <option value="solid_white" <?php selected( $options['card_style'], 'solid_white' ); ?>>Классическая белая плотная карточка (Solid White)</option>
                                </select>
                            </div>

                            <!-- Accent Color -->
                            <div>
                                <label class="vb-label" for="vb_accent_color">Акцентный цвет (кнопки, фокус полей, свечение):</label>
                                <div>
                                    <input type="text" id="vb_accent_color" name="vb_login_branding_options[accent_color]" value="<?php echo esc_attr( $options['accent_color'] ); ?>" class="vb-color-picker">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: NAVIGATION & ADMIN BAR -->
                <div class="vb-card">
                    <div class="vb-card-header">
                        <h2>🔗 4. Навигация и панель управления</h2>
                        <span class="vb-badge">Ссылки и админ-бар</span>
                    </div>
                    <div class="vb-card-body">
                        <div class="vb-field-row">
                            <label class="vb-checkbox-label">
                                <input type="checkbox" name="vb_login_branding_options[show_nav_links]" value="1" <?php checked( $options['show_nav_links'], '1' ); ?>>
                                <strong>Отображать навигационную строку под карточкой</strong>
                                <span class="vb-help" style="display:block; margin-left:24px;">Ссылки «Забыли пароль?», «Регистрация», «← На главную» в виде единой строки.</span>
                            </label>
                        </div>

                        <div class="vb-field-row" style="margin-top:12px;">
                            <label class="vb-checkbox-label">
                                <input type="checkbox" name="vb_login_branding_options[show_footer_copy]" value="1" <?php checked( $options['show_footer_copy'], '1' ); ?>>
                                <strong>Отображать копирайт и подпись в подвале</strong>
                                <span class="vb-help" style="display:block; margin-left:24px;">Брендовая строка с годом, именем сайта и авторством «Designed by VladiMir + AI».</span>
                            </label>
                        </div>

                        <div class="vb-field-row" style="margin-top:12px;">
                            <label class="vb-checkbox-label">
                                <input type="checkbox" name="vb_login_branding_options[admin_bar_brand]" value="1" <?php checked( $options['admin_bar_brand'], '1' ); ?>>
                                <strong>Применить фирменный темный стиль к верхней панели (Admin Bar)</strong>
                                <span class="vb-help" style="display:block; margin-left:24px;">Аккуратный темно-фиолетовый акцент в шапке сайта для авторизованных пользователей.</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT BAR -->
                <div class="vb-submit-bar">
                    <?php submit_button( 'Сохранить настройки', 'primary vb-btn-save', 'submit', false ); ?>
                    <a href="<?php echo esc_url( $login_url ); ?>" target="_blank" class="button button-secondary vb-btn-preview-sub">
                        Открыть страницу входа &rarr;
                    </a>
                </div>
            </form>
        </div>

        <!-- Inline Admin Styles & Scripts -->
        <style>
        .vb-settings-wrap {
            max-width: 960px;
            margin: 20px 20px 40px 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .vb-admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #140d1f 0%, #2e1065 100%);
            border-radius: 14px;
            padding: 22px 28px;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
            margin-bottom: 24px;
        }
        .vb-admin-title {
            color: #ffffff !important;
            font-size: 24px !important;
            font-weight: 700 !important;
            margin: 0 !important;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .vb-version-tag {
            background: rgba(124, 58, 237, 0.4);
            border: 1px solid rgba(168, 85, 247, 0.4);
            color: #e9d5ff;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
        }
        .vb-admin-desc {
            margin: 6px 0 0 0;
            color: #c4b5fd;
            font-size: 14px;
        }
        .vb-btn-preview {
            background: #7c3aed !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 8px 16px !important;
            font-weight: 600 !important;
            font-size: 13.5px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4) !important;
            transition: all 0.2s ease !important;
        }
        .vb-btn-preview:hover {
            background: #6d28d9 !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
        }
        .vb-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.04);
            margin-bottom: 22px;
            overflow: hidden;
        }
        .vb-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .vb-card-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }
        .vb-badge {
            background: #e0e7ff;
            color: #4338ca;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
        }
        .vb-card-body {
            padding: 20px 22px;
        }
        .vb-detected-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
        .vb-detected-title {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
        }
        .vb-detected-success {
            font-size: 13px;
            color: #065f46;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .vb-detected-url {
            font-size: 11.5px;
            color: #64748b;
            font-family: monospace;
            margin-top: 3px;
        }
        .vb-detected-preview {
            background: #140d1f;
            border-radius: 8px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #33264c;
        }
        .vb-field-row {
            margin-bottom: 16px;
        }
        .vb-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .vb-help {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            display: block;
        }
        .vb-radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .vb-radio-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .vb-radio-option:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }
        .vb-radio-option input[type="radio"] {
            margin-top: 3px;
        }
        .vb-radio-content strong {
            display: block;
            font-size: 13.5px;
            color: #0f172a;
        }
        .vb-radio-content span {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }
        .vb-conditional-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 12px;
            margin-bottom: 16px;
        }
        .vb-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .vb-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }
        .vb-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 20px 0;
        }
        .vb-tabs-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .vb-tab-btn {
            background: #f1f5f9;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .vb-tab-btn.active, .vb-tab-btn:hover {
            background: #ede9fe;
            border-color: #7c3aed;
            color: #5b21b6;
        }
        .vb-presets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 14px;
        }
        .vb-preset-card {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            background: #ffffff;
            display: block;
        }
        .vb-preset-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
        }
        .vb-preset-card.selected {
            border-color: #7c3aed;
            box-shadow: 0 0 0 2px #7c3aed;
        }
        .vb-preset-card input[type="radio"] {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .vb-preset-preview {
            height: 60px;
            width: 100%;
        }
        .vb-preset-info {
            padding: 10px 12px;
        }
        .vb-preset-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }
        .vb-preset-desc {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.3;
        }
        .vb-submit-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.04);
        }
        .vb-btn-save {
            background: #7c3aed !important;
            border-color: #6d28d9 !important;
            font-size: 14px !important;
            padding: 6px 22px !important;
            font-weight: 700 !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 10px rgba(124, 58, 237, 0.3) !important;
        }
        .vb-btn-save:hover {
            background: #6d28d9 !important;
        }
        .vb-btn-preview-sub {
            border-radius: 8px !important;
            font-weight: 600 !important;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Initialize Color Pickers
            $('.vb-color-picker').wpColorPicker();

            // Logo Mode Toggle
            $('input[name="vb_login_branding_options[logo_mode]"]').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#vb-custom-logo-section').slideDown(200);
                } else {
                    $('#vb-custom-logo-section').slideUp(200);
                }
            });

            // Background Type Tab Toggle
            $('.vb-tab-btn input[type="radio"]').on('change', function() {
                var selected = $(this).val();
                $('.vb-tab-btn').removeClass('active');
                $(this).closest('.vb-tab-btn').addClass('active');

                $('.vb-tab-pane').hide();
                $('#vb-bg-pane-' + selected).fadeIn(200);
            });

            // Preset Selection Visual
            $('.vb-preset-card input[type="radio"]').on('change', function() {
                $('.vb-preset-card').removeClass('selected');
                $(this).closest('.vb-preset-card').addClass('selected');
            });

            // Opacity slider sync
            $('#vb_bg_overlay_opacity_range').on('input', function() {
                $('#vb_bg_overlay_opacity').val($(this).val());
            });
            $('#vb_bg_overlay_opacity').on('input', function() {
                $('#vb_bg_overlay_opacity_range').val($(this).val());
            });

            // Media Uploader
            $('.vb-upload-media-btn').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var targetInput = $(btn.data('target'));
                var previewImg = $(btn.data('preview'));

                var customUploader = wp.media({
                    title: 'Выберите или загрузите изображение',
                    button: { text: 'Использовать это изображение' },
                    multiple: false
                }).on('select', function() {
                    var attachment = customUploader.state().get('selection').first().toJSON();
                    targetInput.val(attachment.url);
                    if (previewImg.length) {
                        previewImg.attr('src', attachment.url);
                        previewImg.parent().show();
                    }
                }).open();
            });
        });
        </script>
        <?php
    }

    /**
     * Helper: Convert HEX color to RGBA.
     */
    private static function hex_to_rgba( $hex, $alpha = 1.0 ) {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $r = hexdec( str_repeat( substr( $hex, 0, 1 ), 2 ) );
            $g = hexdec( str_repeat( substr( $hex, 1, 1 ), 2 ) );
            $b = hexdec( str_repeat( substr( $hex, 2, 1 ), 2 ) );
        } elseif ( strlen( $hex ) === 6 ) {
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );
        } else {
            return 'rgba(0, 0, 0, ' . (float) $alpha . ')';
        }
        return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . (float) $alpha . ')';
    }

    /**
     * Helper: Lighten or darken a HEX color.
     */
    private static function adjust_brightness( $hex, $percent ) {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if ( strlen( $hex ) !== 6 ) {
            return '#7c3aed';
        }

        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );

        $r = max( 0, min( 255, (int) ( $r + ( 255 * ( $percent / 100 ) ) ) ) );
        $g = max( 0, min( 255, (int) ( $g + ( 255 * ( $percent / 100 ) ) ) ) );
        $b = max( 0, min( 255, (int) ( $b + ( 255 * ( $percent / 100 ) ) ) ) );

        return sprintf( '#%02x%02x%02x', $r, $g, $b );
    }
}

add_action( 'init', [ 'VB_Login_Branding', 'init' ] );
