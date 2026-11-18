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

        // Admin Table Columns & Filters
        add_filter( 'manage_vb_listing_posts_columns', [ __CLASS__, 'set_custom_columns' ] );
        add_action( 'manage_vb_listing_posts_custom_column', [ __CLASS__, 'render_custom_columns' ], 10, 2 );
        add_filter( 'manage_edit-vb_listing_sortable_columns', [ __CLASS__, 'set_sortable_columns' ] );
        add_action( 'restrict_manage_posts', [ __CLASS__, 'render_admin_filters' ] );
        add_action( 'admin_head', [ __CLASS__, 'admin_column_styles' ] );

        // Pre get posts for frontend city filter and admin sorting/filtering
        add_action( 'pre_get_posts', [ __CLASS__, 'handle_pre_get_posts' ] );

        // Enhanced admin search across custom fields (email, phone, address)
        add_filter( 'posts_search', [ __CLASS__, 'enhance_admin_search' ], 10, 2 );
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
            'rewrite'            => [ 'slug' => 'inzerat', 'with_front' => false ],
            'capability_type'    => 'post',
            'has_archive'        => 'inzeraty',
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-grid-view',
            'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ],
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
            'rewrite'               => [ 'slug' => 'kategorie', 'with_front' => false, 'hierarchical' => true ],
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
            'rewrite'           => [ 'slug' => 'mesto', 'with_front' => false, 'hierarchical' => true ],
            'show_in_rest'      => true,
        ] );
    }

    /**
     * Define custom admin columns for vb_listing
     */
    public static function set_custom_columns( $columns ) {
        $new_columns = [];
        $new_columns['cb']       = $columns['cb'];
        $new_columns['vb_thumb'] = 'Фото';
        $new_columns['title']    = 'Заголовок';
        $new_columns['vb_type']  = 'Тип';
        $new_columns['taxonomy-vb_category'] = 'Рубрика';
        $new_columns['taxonomy-vb_location'] = 'Город';
        $new_columns['vb_phone'] = 'Телефон';
        $new_columns['vb_email'] = 'Email';
        $new_columns['vb_price'] = 'Цена';
        $new_columns['vb_expiry']= 'Срок';
        $new_columns['date']     = $columns['date'];

        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public static function render_custom_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'vb_thumb':
                $thumb = get_the_post_thumbnail_url( $post_id, 'thumbnail' ) ?: '/wp-content/plugins/wp-board-directory/assets/no-photo.svg';
                echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">';
                echo '<img src="' . esc_url( $thumb ) . '" class="vb-admin-col-thumb" alt="" />';
                echo '</a>';
                break;

            case 'vb_type':
                $type = get_post_meta( $post_id, '_vb_type', true );
                if ( 'company' === $type ) {
                    echo '<span class="vb-badge-company">🏢 Фирма</span>';
                } else {
                    echo '<span class="vb-badge-ad">📢 Объявление</span>';
                }
                break;

            case 'vb_phone':
                $phone = get_post_meta( $post_id, '_vb_phone', true ) ?: get_post_meta( $post_id, '_phone', true ) ?: get_post_meta( $post_id, 'phone', true );
                if ( $phone ) {
                    echo '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ) . '" style="text-decoration:none; font-weight:600; color:#0284c7;">📞 ' . esc_html( $phone ) . '</a>';
                } else {
                    echo '<span style="color:#94a3b8;">—</span>';
                }
                break;

            case 'vb_email':
                $email = get_post_meta( $post_id, '_vb_email', true ) ?: get_post_meta( $post_id, '_email', true ) ?: get_post_meta( $post_id, 'email', true );
                if ( ! $email ) {
                    $author_id = get_post_field( 'post_author', $post_id );
                    $author = get_userdata( $author_id );
                    $email = $author ? $author->user_email : '';
                }
                if ( $email ) {
                    echo '<a href="mailto:' . esc_attr( $email ) . '" style="text-decoration:none; color:#475569;">✉️ ' . esc_html( $email ) . '</a>';
                } else {
                    echo '<span style="color:#94a3b8;">—</span>';
                }
                break;

            case 'vb_price':
                $price = get_post_meta( $post_id, '_vb_price', true ) ?: get_post_meta( $post_id, 'price', true );
                if ( $price > 0 ) {
                    echo '<strong>' . number_format( (float) $price, 0, '.', ' ' ) . ' Kč</strong>';
                } else {
                    echo '<span style="color:#64748b;">—</span>';
                }
                break;

            case 'vb_expiry':
                $expiry = get_post_meta( $post_id, 'expiry_date', true ) ?: get_post_meta( $post_id, '_expiry_date', true ) ?: get_post_meta( $post_id, '_vb_expiry_date', true );
                if ( $expiry ) {
                    $exp_ts = strtotime( $expiry );
                    $is_expired = $exp_ts && $exp_ts < time();
                    $color = $is_expired ? '#dc2626' : '#059669';
                    echo '<span style="color:' . $color . '; font-size:12px; font-weight:600;">' . esc_html( date( 'd.m.Y', $exp_ts ) ) . '</span>';
                } else {
                    echo '<span style="color:#94a3b8;">—</span>';
                }
                break;
        }
    }

    /**
     * Make custom columns sortable
     */
    public static function set_sortable_columns( $columns ) {
        $columns['vb_phone'] = 'vb_phone';
        $columns['vb_email'] = 'vb_email';
        $columns['vb_price'] = 'vb_price';
        $columns['vb_type']  = 'vb_type';
        return $columns;
    }

    /**
     * Render category, location, and type filters on admin list table
     */
    public static function render_admin_filters( $post_type ) {
        if ( 'vb_listing' !== $post_type ) {
            return;
        }

        // 1. Filter by Category
        $selected_cat = sanitize_text_field( $_GET['vb_category_filter'] ?? '' );
        wp_dropdown_categories( [
            'show_option_all' => '— Все рубрики —',
            'taxonomy'        => 'vb_category',
            'name'            => 'vb_category_filter',
            'orderby'         => 'name',
            'selected'        => $selected_cat,
            'hierarchical'    => true,
            'depth'           => 3,
            'show_count'      => true,
            'hide_empty'      => false,
            'value_field'     => 'slug',
        ] );

        // 2. Filter by Location
        $selected_loc = sanitize_text_field( $_GET['vb_location_filter'] ?? '' );
        wp_dropdown_categories( [
            'show_option_all' => '— Все города и регионы —',
            'taxonomy'        => 'vb_location',
            'name'            => 'vb_location_filter',
            'orderby'         => 'name',
            'selected'        => $selected_loc,
            'hierarchical'    => true,
            'depth'           => 3,
            'show_count'      => true,
            'hide_empty'      => false,
            'value_field'     => 'slug',
        ] );

        // 3. Filter by Type (Ad vs Company)
        $selected_type = sanitize_key( $_GET['vb_type_filter'] ?? '' );
        ?>
        <select name="vb_type_filter">
            <option value="">— Все типы —</option>
            <option value="classified" <?php selected( $selected_type, 'classified' ); ?>>📢 Только объявления</option>
            <option value="company" <?php selected( $selected_type, 'company' ); ?>>🏢 Только компании</option>
        </select>
        <?php
    }

    /**
     * Pre get posts: handle admin filtering & sorting, and frontend category city filter
     */
    public static function handle_pre_get_posts( $query ) {
        // ADMIN QUERY
        if ( is_admin() && $query->is_main_query() && 'vb_listing' === $query->get( 'post_type' ) ) {
            // Sort by Phone
            if ( 'vb_phone' === $query->get( 'orderby' ) ) {
                $query->set( 'meta_key', '_vb_phone' );
                $query->set( 'orderby', 'meta_value' );
            }
            // Sort by Email
            if ( 'vb_email' === $query->get( 'orderby' ) ) {
                $query->set( 'meta_key', '_vb_email' );
                $query->set( 'orderby', 'meta_value' );
            }
            // Sort by Price
            if ( 'vb_price' === $query->get( 'orderby' ) ) {
                $query->set( 'meta_key', '_vb_price' );
                $query->set( 'orderby', 'meta_value_num' );
            }
            // Sort by Type
            if ( 'vb_type' === $query->get( 'orderby' ) ) {
                $query->set( 'meta_key', '_vb_type' );
                $query->set( 'orderby', 'meta_value' );
            }

            // Filter by Category
            if ( ! empty( $_GET['vb_category_filter'] ) ) {
                $cat_slug = sanitize_text_field( $_GET['vb_category_filter'] );
                $tax_query = (array) $query->get( 'tax_query' );
                $tax_query[] = [
                    'taxonomy' => 'vb_category',
                    'field'    => 'slug',
                    'terms'    => $cat_slug,
                ];
                $query->set( 'tax_query', $tax_query );
            }

            // Filter by Location
            if ( ! empty( $_GET['vb_location_filter'] ) ) {
                $loc_slug = sanitize_text_field( $_GET['vb_location_filter'] );
                $tax_query = (array) $query->get( 'tax_query' );
                $tax_query[] = [
                    'taxonomy' => 'vb_location',
                    'field'    => 'slug',
                    'terms'    => $loc_slug,
                ];
                $query->set( 'tax_query', $tax_query );
            }

            // Filter by Type
            if ( ! empty( $_GET['vb_type_filter'] ) ) {
                $type_val = sanitize_key( $_GET['vb_type_filter'] );
                $meta_query = (array) $query->get( 'meta_query' );
                $meta_query[] = [
                    'key'     => '_vb_type',
                    'value'   => $type_val,
                    'compare' => '=',
                ];
                $query->set( 'meta_query', $meta_query );
            }
        }

        // FRONTEND QUERY: Category Archive City & Keyword Filter
        if ( ! is_admin() && $query->is_main_query() && ( is_tax( 'vb_category' ) || is_post_type_archive( 'vb_listing' ) ) ) {
            $city_slug = sanitize_text_field( $_GET['city'] ?? $_GET['mesto'] ?? '' );
            if ( ! empty( $city_slug ) ) {
                $tax_query = (array) $query->get( 'tax_query' );
                $tax_query[] = [
                    'taxonomy' => 'vb_location',
                    'field'    => 'slug',
                    'terms'    => $city_slug,
                ];
                $query->set( 'tax_query', $tax_query );
            }

            // Keyword search in category
            $kw = sanitize_text_field( $_GET['s_q'] ?? '' );
            if ( ! empty( $kw ) ) {
                $query->set( 's', $kw );
            }

            // Sort order
            $order_by = sanitize_key( $_GET['sort'] ?? '' );
            if ( 'price_asc' === $order_by ) {
                $query->set( 'meta_key', '_vb_price' );
                $query->set( 'orderby', 'meta_value_num' );
                $query->set( 'order', 'ASC' );
            } elseif ( 'price_desc' === $order_by ) {
                $query->set( 'meta_key', '_vb_price' );
                $query->set( 'orderby', 'meta_value_num' );
                $query->set( 'order', 'DESC' );
            } elseif ( 'oldest' === $order_by ) {
                $query->set( 'orderby', 'date' );
                $query->set( 'order', 'ASC' );
            }
        }
    }

    /**
     * Enhanced search across custom fields (_vb_email, _vb_phone, _vb_address) and author email
     */
    public static function enhance_admin_search( $search, $query ) {
        global $wpdb;

        if ( ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) && 'vb_listing' === $query->get( 'post_type' ) && $query->is_search() ) {
            $search_term = trim( (string) $query->get( 's' ) );
            if ( ! empty( $search_term ) ) {
                $term_like = '%' . $wpdb->esc_like( $search_term ) . '%';

                // Find matching post IDs from postmeta
                $meta_ids = $wpdb->get_col( $wpdb->prepare(
                    "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_vb_email', '_vb_phone', '_vb_address', 'email', 'phone', '_email', '_phone') AND meta_value LIKE %s",
                    $term_like
                ) );

                // Find matching post IDs from author users
                $user_ids = $wpdb->get_col( $wpdb->prepare(
                    "SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->users} u ON p.post_author = u.ID WHERE p.post_type = 'vb_listing' AND (u.user_email LIKE %s OR u.display_name LIKE %s)",
                    $term_like,
                    $term_like
                ) );

                $matched_ids = array_unique( array_merge( (array) $meta_ids, (array) $user_ids ) );
                if ( ! empty( $matched_ids ) ) {
                    $id_list = implode( ',', array_map( 'absint', $matched_ids ) );
                    $search = " AND (({$wpdb->posts}.post_title LIKE '{$term_like}') OR ({$wpdb->posts}.post_content LIKE '{$term_like}') OR ({$wpdb->posts}.ID IN ({$id_list}))) ";
                }
            }
        }

        return $search;
    }

    /**
     * Custom styling for admin columns
     */
    public static function admin_column_styles() {
        global $post_type;
        if ( 'vb_listing' !== $post_type ) {
            return;
        }
        ?>
        <style>
            .column-vb_thumb { width: 65px; text-align: center; }
            .vb-admin-col-thumb { width: 52px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; vertical-align: middle; }
            .column-vb_type { width: 110px; }
            .vb-badge-company { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
            .vb-badge-ad { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
            .column-vb_phone { width: 130px; }
            .column-vb_email { width: 150px; }
            .column-vb_price { width: 95px; }
            .column-vb_expiry { width: 85px; }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('posts-filter');
            if (!form) return;
            form.addEventListener('submit', function() {
                // Disable useless/default GET parameters so they do not clutter the URL
                var checks = [
                    { name: 'filter_action', condition: function() { return true; } },
                    { name: 'action', condition: function(v) { return v === '-1' || !v; } },
                    { name: 'action2', condition: function(v) { return v === '-1' || !v; } },
                    { name: 'm', condition: function(v) { return v === '0' || !v; } },
                    { name: 's', condition: function(v) { return !v.trim(); } },
                    { name: 'vb_category_filter', condition: function(v) { return v === '0' || !v; } },
                    { name: 'vb_location_filter', condition: function(v) { return v === '0' || !v; } },
                    { name: 'vb_type_filter', condition: function(v) { return !v; } }
                ];
                checks.forEach(function(item) {
                    var els = form.querySelectorAll('[name="' + item.name + '"]');
                    els.forEach(function(el) {
                        if (item.condition(el.value)) {
                            el.disabled = true;
                        }
                    });
                });
            });
        });
        </script>
        <?php
    }
}
