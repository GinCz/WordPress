<?php
/**
 * Standalone Data Exporter for ACADP Listings & Directorist Companies
 * Author: Vladimir Bulantsev (@GinCz) & AI
 * Target: doska-*.ru network
 */

if ( ! defined( 'ABSPATH' ) ) {
    // If run via WP-CLI eval-file or direct include
    $wp_load = getcwd() . '/wp-load.php';
    if ( file_exists( $wp_load ) ) {
        require_once $wp_load;
    } else {
        exit( "Cannot locate wp-load.php in current working directory.\n" );
    }
}

global $wpdb;

echo "=== STARTING EXPORT FOR: " . home_url() . " ===\n";

$export_data = [
    'domain'      => parse_url( home_url(), PHP_URL_HOST ),
    'exported_at' => date( 'c' ),
    'categories'  => [],
    'listings'    => [],
    'companies'   => [],
];

// 1. Export Categories (ACADP and Directorist)
$taxonomies = [ 'acadp_categories', 'acadp_locations', 'at_biz_dir-category', 'at_biz_dir-location' ];
foreach ( $taxonomies as $tax ) {
    if ( taxonomy_exists( $tax ) ) {
        $terms = get_terms( [
            'taxonomy'   => $tax,
            'hide_empty' => false,
        ] );
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $export_data['categories'][] = [
                    'term_id'  => $term->term_id,
                    'taxonomy' => $term->taxonomy,
                    'name'     => $term->name,
                    'slug'     => $term->slug,
                    'parent'   => $term->parent,
                    'count'    => $term->count,
                ];
            }
        }
    }
}

// 2. Export ACADP Listings
$ad_posts = get_posts( [
    'post_type'      => 'acadp_listings',
    'post_status'    => 'any',
    'posts_per_page' => -1,
] );

echo "Found " . count( $ad_posts ) . " ACADP listings.\n";

foreach ( $ad_posts as $post ) {
    $meta = get_post_meta( $post->ID );
    
    // Resolve attachments / image URLs
    $images = [];
    if ( ! empty( $meta['images'][0] ) ) {
        $img_ids = maybe_unserialize( $meta['images'][0] );
        if ( is_array( $img_ids ) ) {
            foreach ( $img_ids as $img_id ) {
                $url = wp_get_attachment_url( (int) $img_id );
                if ( $url ) {
                    $images[] = [
                        'id'  => (int) $img_id,
                        'url' => $url,
                    ];
                }
            }
        }
    }
    // Also featured image if not in images array
    $thumb_id = get_post_thumbnail_id( $post->ID );
    if ( $thumb_id ) {
        $thumb_url = wp_get_attachment_url( $thumb_id );
        if ( $thumb_url && ! in_array( $thumb_id, array_column( $images, 'id' ) ) ) {
            array_unshift( $images, [
                'id'  => (int) $thumb_id,
                'url' => $thumb_url,
            ] );
        }
    }

    $terms = wp_get_post_terms( $post->ID, [ 'acadp_categories', 'acadp_locations' ], [ 'fields' => 'all' ] );
    $assigned_terms = [];
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $t ) {
            $assigned_terms[] = [
                'taxonomy' => $t->taxonomy,
                'name'     => $t->name,
                'slug'     => $t->slug,
            ];
        }
    }

    $export_data['listings'][] = [
        'id'          => $post->ID,
        'title'       => $post->post_title,
        'content'     => $post->post_content,
        'status'      => $post->post_status,
        'date'        => $post->post_date,
        'author_id'   => $post->post_author,
        'terms'       => $assigned_terms,
        'price'       => $meta['price'][0] ?? '',
        'phone'       => $meta['phone'][0] ?? '',
        'email'       => $meta['email'][0] ?? '',
        'address'     => $meta['address'][0] ?? '',
        'images'      => $images,
        'meta_all'    => $meta,
    ];
}

// 3. Export Directorist Companies
$dir_posts = get_posts( [
    'post_type'      => 'at_biz_dir',
    'post_status'    => 'any',
    'posts_per_page' => -1,
] );

echo "Found " . count( $dir_posts ) . " Directorist companies.\n";

foreach ( $dir_posts as $post ) {
    $meta = get_post_meta( $post->ID );
    
    $logo_url = '';
    if ( ! empty( $meta['_listing_prv_img'][0] ) ) {
        $logo_url = wp_get_attachment_url( (int) $meta['_listing_prv_img'][0] ) ?: '';
    }

    $terms = wp_get_post_terms( $post->ID, [ 'at_biz_dir-category', 'at_biz_dir-location' ], [ 'fields' => 'all' ] );
    $assigned_terms = [];
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $t ) {
            $assigned_terms[] = [
                'taxonomy' => $t->taxonomy,
                'name'     => $t->name,
                'slug'     => $t->slug,
            ];
        }
    }

    $export_data['companies'][] = [
        'id'        => $post->ID,
        'title'     => $post->post_title,
        'content'   => $post->post_content,
        'status'    => $post->post_status,
        'date'      => $post->post_date,
        'author_id' => $post->post_author,
        'terms'     => $assigned_terms,
        'tagline'   => $meta['_tagline'][0] ?? '',
        'address'   => $meta['_address'][0] ?? '',
        'phone'     => $meta['_phone'][0] ?? '',
        'email'     => $meta['_email'][0] ?? '',
        'website'   => $meta['_website'][0] ?? '',
        'logo_url'  => $logo_url,
        'meta_all'  => $meta,
    ];
}

// 4. Save JSON Archive
$out_dir = '/root/backups_doski_opt/data_export';
if ( ! is_dir( $out_dir ) ) {
    mkdir( $out_dir, 0755, true );
}

$domain = parse_url( home_url(), PHP_URL_HOST );
$out_file = "{$out_dir}/{$domain}_data_export_" . date( 'Ymd_His' ) . ".json";
file_put_contents( $out_file, json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );

echo "SUCCESS: Exported " . count( $export_data['listings'] ) . " ads and " . count( $export_data['companies'] ) . " companies to: {$out_file} (" . round( filesize( $out_file ) / 1024 ) . " KB)\n";
