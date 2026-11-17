<?php
/**
 * Plugin Name: WP Date & Time Micro (VladiMIR+AI✅)
 * Plugin URI:  https://github.com/GinCz/WordPress_Plugins/tree/main/wp-date-time-micro
 * Description: Ultra-lightweight micro-plugin for rendering date and time shortcodes ([wpdts-date], [wpdts-time], [wp-dt], etc.) using native WordPress timezone-aware wp_date(). Zero overhead replacement for heavy 3rd-party date plugins.
 * Version:     2026-11__1.00
 * Author:      VladiMIR (GinCz) + AI
 * Author URI:  https://github.com/GinCz
 * License:     GPL-2.0-or-later
 * Update URI:  https://vladimir-ai.updates/wp-date-time-micro
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: wp-date-time-micro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Shared auto-update client (guarded fallback)
if ( file_exists( __DIR__ . '/vladimir-ai-updater.php' ) ) {
    require_once __DIR__ . '/vladimir-ai-updater.php';
}

// Shared plugin-list translations
if ( file_exists( __DIR__ . '/vladimir-ai-i18n.php' ) ) {
    require_once __DIR__ . '/vladimir-ai-i18n.php';
}

/**
 * Universal Shortcode Handler for Date & Time
 *
 * Supported tags:
 * [wpdts-date], [wp-dt-date]
 * [wpdts-time], [wp-dt-time]
 * [wpdts-year], [wp-dt-year]
 * [wpdts-month], [wpdts-day], [wpdts-hour], [wpdts-minute], [wpdts-second], [wpdts-weekday], [wpdts-timezone]
 * [wpdts format="..."], [wp-dt format="..."], [wp_dt format="..."]
 *
 * @param array       $atts    Shortcode attributes.
 * @param string|null $content Enclosed content (unused).
 * @param string      $tag     The shortcode tag invoked.
 * @return string Formatted date/time.
 */
function wp_date_time_micro_render( $atts, $content = null, $tag = '' ) {
    $atts = shortcode_atts( [
        'format'    => '',
        'offset'    => '',
        'timestamp' => '',
    ], $atts, $tag );

    // 1. Resolve base timestamp (native WordPress timestamp in site timezone)
    if ( ! empty( $atts['timestamp'] ) && is_numeric( $atts['timestamp'] ) ) {
        $timestamp = (int) $atts['timestamp'];
    } else {
        $timestamp = current_time( 'timestamp' );
    }

    // 2. Apply relative offset if provided (e.g. '+1 day', '-2 hours')
    if ( ! empty( $atts['offset'] ) ) {
        $parsed = strtotime( $atts['offset'], $timestamp );
        if ( false !== $parsed ) {
            $timestamp = $parsed;
        }
    }

    // 3. Resolve output format
    $format = trim( (string) $atts['format'] );

    if ( empty( $format ) ) {
        switch ( $tag ) {
            case 'wpdts-date':
            case 'wp-dt-date':
                $format = get_option( 'date_format', 'd / m / Y' );
                break;

            case 'wpdts-time':
            case 'wp-dt-time':
                $format = get_option( 'time_format', 'H:i' );
                break;

            case 'wpdts-year':
            case 'wp-dt-year':
                $format = 'Y';
                break;

            case 'wpdts-month':
                $format = 'm';
                break;

            case 'wpdts-day':
                $format = 'd';
                break;

            case 'wpdts-hour':
                $format = 'H';
                break;

            case 'wpdts-minute':
                $format = 'i';
                break;

            case 'wpdts-second':
                $format = 's';
                break;

            case 'wpdts-weekday':
                $format = 'l';
                break;

            case 'wpdts-timezone':
                $format = 'T';
                break;

            case 'wpdts':
            case 'wp-dt':
            case 'wp_dt':
            default:
                $dateFormat = get_option( 'date_format', 'd / m / Y' );
                $timeFormat = get_option( 'time_format', 'H:i' );
                $format     = $dateFormat . ' ' . $timeFormat;
                break;
        }
    }

    // 4. Return localized, timezone-aware formatted date/time string
    return esc_html( wp_date( $format, $timestamp ) );
}

/**
 * Register all shortcodes on init
 */
add_action( 'init', function() {
    $shortcodes = [
        'wpdts',
        'wp-dt',
        'wp_dt',
        'wpdts-date',
        'wp-dt-date',
        'wpdts-time',
        'wp-dt-time',
        'wpdts-year',
        'wp-dt-year',
        'wpdts-month',
        'wpdts-day',
        'wpdts-hour',
        'wpdts-minute',
        'wpdts-second',
        'wpdts-weekday',
        'wpdts-timezone',
    ];

    foreach ( $shortcodes as $shortcode ) {
        add_shortcode( $shortcode, 'wp_date_time_micro_render' );
    }
} );
