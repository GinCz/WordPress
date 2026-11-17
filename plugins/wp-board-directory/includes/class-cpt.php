<?php
/**
 * Custom Post Types & Taxonomies Registration
 *
 * @package WP_Board_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VB_CPT {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_post_types' ], 0 );
        add_action( 'init', [ __CLASS__, 'register_taxonomies' ], 0 );
    }

    public static function register_post_types() {
        $labels = [
            'name'                  => 'Доска и Каталог',
            'singular_name'         => 'Объявление / Фирма',
            'menu_name'             => 'Доска и Фирмы',
            'name_admin_bar'        => 'Объявление / Фирма',
            'add_new'               => 'Добавить',
            'add_new_item'          => 'Добавить новое объявление / фирму',
            'new_item'              => 'Новая запись',
            'edit_item'             => 'Редактировать запись',
            'view_item'             => 'Посмотреть запись',
            'all_items'             => 'Все объявления и фирмы',
            'search_items'          => 'Искать записи',
            'not_found'             => 'Не найдено',
            'not_found_in_trash'    => 'В корзине не найдено',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => [ 'slug' => 'item', 'with_front' => false ],
            'capability_type'    => 'post',
            'has_archive'        => 'all-items',
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-grid-view',
            'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ],
            'show_in_rest'       => true,
        ];

        register_post_type( 'vb_listing', $args );
    }

    public static function register_taxonomies() {
        // 1. Categories
        $cat_labels = [
            'name'              => 'Рубрики и категории',
            'singular_name'     => 'Категория',
            'search_items'      => 'Искать категории',
            'all_items'         => 'Все категории',
            'parent_item'       => 'Родительская категория',
            'parent_item_colon' => 'Родительская категория:',
            'edit_item'         => 'Редактировать категорию',
            'update_item'       => 'Обновить категорию',
            'add_new_item'      => 'Добавить новую категорию',
            'new_item_name'     => 'Название новой категории',
            'menu_name'         => 'Рубрики',
        ];

        register_taxonomy( 'vb_category', [ 'vb_listing' ], [
            'hierarchical'          => true,
            'labels'                => $cat_labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => true,
            'rewrite'               => [ 'slug' => 'board-cat', 'with_front' => false ],
            'show_in_rest'          => true,
            'update_count_callback' => '_update_post_term_count',
        ] );

        // 2. Locations
        $loc_labels = [
            'name'              => 'Города и регионы',
            'singular_name'     => 'Город / Регион',
            'search_items'      => 'Искать локации',
            'all_items'         => 'Все локации',
            'parent_item'       => 'Родительский регион',
            'parent_item_colon' => 'Родительский регион:',
            'edit_item'         => 'Редактировать локацию',
            'update_item'       => 'Обновить локацию',
            'add_new_item'      => 'Добавить локацию',
            'new_item_name'     => 'Название новой локации',
            'menu_name'         => 'Города / Локации',
        ];

        register_taxonomy( 'vb_location', [ 'vb_listing' ], [
            'hierarchical'      => true,
            'labels'            => $loc_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'location', 'with_front' => false ],
            'show_in_rest'      => true,
        ] );
    }
}
