<?php
/**
 * Plugin Name: WP Weather & Holidays (VladiMIR+AI✅)
 * Plugin URI:  https://github.com/GinCz/WordPress/tree/main/plugins/wp-weather-holidays
 * Description: Compact multi-city weather widget for 11 European capitals with Open-Meteo caching, daily Czech Svátek, Name Days (День Ангела), and European/Russian holidays.
 * Version:     2026-11__1.02
 * Author:      VladiMIR (GinCz) + AI
 * Author URI:  https://github.com/GinCz
 * License:     GPL-2.0-or-later
 * Update URI:  https://vladimir-ai.updates/wp-weather-holidays
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: wp-weather-holidays
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

define( 'VB_WH_VERSION', '2026-11__1.02' );
define( 'VB_WH_PATH', plugin_dir_path( __FILE__ ) );
define( 'VB_WH_URL', plugin_dir_url( __FILE__ ) );

// Load Submodules
require_once VB_WH_PATH . 'includes/class-weather.php';
require_once VB_WH_PATH . 'includes/class-holidays.php';
require_once VB_WH_PATH . 'includes/class-country-boards.php';

// Enqueue CSS
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'vb-weather-holidays', VB_WH_URL . 'assets/css/weather-holidays.css', [], VB_WH_VERSION );
} );

// Register Shortcodes
add_shortcode( 'vb_weather', [ 'VB_Weather', 'render' ] );
add_shortcode( 'vb_holidays', [ 'VB_Holidays', 'render' ] );
add_shortcode( 'vb_country_boards', [ 'VB_Country_Boards', 'render' ] );
add_shortcode( 'vb_svatek', [ 'VB_Holidays', 'render' ] );

// Combined Shortcode
add_shortcode( 'vb_weather_holidays', function( $atts ) {
    return VB_Weather::render( $atts ) . VB_Holidays::render( $atts );
} );
