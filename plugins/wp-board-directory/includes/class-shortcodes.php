<?php
/**
 * Shortcodes & Frontend Views
 *
 * @package WP_Board_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VB_Shortcodes {

    public static function init() {
        add_shortcode( 'vb_categories', [ __CLASS__, 'render_categories' ] );
        add_shortcode( 'vb_listings', [ __CLASS__, 'render_listings' ] );
        add_shortcode( 'vb_companies', [ __CLASS__, 'render_companies' ] );
        add_shortcode( 'vb_dashboard', [ __CLASS__, 'render_dashboard' ] );

        // Backward compatibility: alias old dashboard shortcodes to our unified dashboard
        add_shortcode( 'acadp_user_dashboard', [ __CLASS__, 'render_dashboard' ] );
        add_shortcode( 'directorist_user_dashboard', [ __CLASS__, 'render_dashboard' ] );

        // Handle profile save POST
        add_action( 'init', [ __CLASS__, 'handle_profile_save' ] );

        // 301 Redirect for legacy /all-listings/ to /company/
        add_action( 'template_redirect', [ __CLASS__, 'handle_legacy_redirects' ] );

        // Hook into ACADP categories template to balance rows & dividers
        add_filter( 'acadp_get_template', [ __CLASS__, 'filter_acadp_template' ], 10, 3 );
        add_filter( 'acadp_load_template', [ __CLASS__, 'filter_acadp_load_template' ], 10, 2 );
    }

    /**
     * Handle user profile save from Unified Dashboard
     */
    public static function handle_profile_save() {
        if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || empty( $_POST['vb_action'] ) || 'save_profile' !== $_POST['vb_action'] ) {
            return;
        }

        if ( ! is_user_logged_in() || ! check_admin_referer( 'vb_save_profile_action', 'vb_profile_nonce' ) ) {
            return;
        }

        $uid = get_current_user_id();

        $display_name = sanitize_text_field( $_POST['display_name'] ?? '' );
        $first_name   = sanitize_text_field( $_POST['first_name'] ?? '' );
        $phone        = sanitize_text_field( $_POST['phone'] ?? '' );
        $city         = sanitize_text_field( $_POST['city'] ?? '' );
        $company      = sanitize_text_field( $_POST['company'] ?? '' );
        $bio          = sanitize_textarea_field( $_POST['description'] ?? '' );

        if ( ! empty( $display_name ) ) {
            wp_update_user( [
                'ID'           => $uid,
                'display_name' => $display_name,
                'first_name'   => $first_name ?: $display_name,
                'description'  => $bio,
            ] );
        }

        // Phone meta (update across all known keys for full compatibility)
        update_user_meta( $uid, 'phone', $phone );
        update_user_meta( $uid, 'acadp_phone', $phone );
        update_user_meta( $uid, 'atbdp_phone', $phone );
        update_user_meta( $uid, 'billing_phone', $phone );

        // City & Address
        update_user_meta( $uid, 'address', $city );
        update_user_meta( $uid, 'user_city', $city );

        // Company name
        update_user_meta( $uid, 'company_name', $company );
        update_user_meta( $uid, 'user_company', $company );

        $referer = wp_get_referer() ?: home_url( '/kabinet/' );
        $redirect = add_query_arg( [ 'saved' => '1', 'tab' => 'profile' ], $referer );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Handle legacy 301 redirects
     */
    public static function handle_legacy_redirects() {
        $req = trim( $_SERVER['REQUEST_URI'] ?? '', '/' );
        if ( 'all-listings' === $req || preg_match( '#^all-listings/?$#', $req ) ) {
            wp_safe_redirect( home_url( '/company/' ), 301 );
            exit;
        }
    }

    /**
     * Filter ACADP deprecated template loader
     */
    public static function filter_acadp_template( $template_file, $name, $widget = '' ) {
        if ( strpos( $name, 'acadp-public-categories-text-list-display.php' ) !== false ) {
            $custom = VB_BOARD_PATH . 'templates/acadp-categories-list.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }
        return $template_file;
    }

    /**
     * Filter ACADP standard template loader
     */
    public static function filter_acadp_load_template( $template_file, $options = [] ) {
        if ( strpos( $template_file, 'layout-list.php' ) !== false && strpos( $template_file, 'categories' ) !== false ) {
            $custom = VB_BOARD_PATH . 'templates/acadp-categories-list.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }
        return $template_file;
    }

    /**
     * Hierarchical 3-Column Categories Grid (Exact layout with full keywords, H2 and H3)
     */
    public static function render_categories( $atts ) {
        $tax = taxonomy_exists( 'vb_category' ) ? 'vb_category' : 'acadp_categories';

        $col_slugs = [
            0 => [ 'avto', 'rabota-i-biznes', 'sredstva-svyazi' ],
            1 => [ 'razvlecheniya', 'dlya-doma', 'hozyajstvo' ],
            2 => [ 'nedvizhimost', 'raznoe', 'elektronika' ],
        ];

        ob_start();
        ?>
        <div class="vb-categories-columns-wrap">
            <div class="vb-cat-row">
                <?php for ( $col = 0; $col < 3; $col++ ) : ?>
                    <div class="vb-cat-col">
                        <?php foreach ( $col_slugs[ $col ] as $slug ) : 
                            $parent = get_term_by( 'slug', $slug, $tax );
                            if ( ! $parent ) continue;

                            $children = get_terms( [
                                'taxonomy'   => $tax,
                                'hide_empty' => false,
                                'parent'     => $parent->term_id,
                                'orderby'    => 'name',
                                'order'      => 'ASC',
                            ] );

                            $padded_count = (int) $parent->count;
                            if ( ! empty( $children ) && ! is_wp_error( $children ) ) {
                                foreach ( $children as $ch ) {
                                    $padded_count += (int) $ch->count;
                                }
                            }

                            $parent_url = get_term_link( $parent );
                            $clean_parent_name = trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $parent->name ) );
                        ?>
                            <div class="vb-cat-group">
                                <div class="vb-cat-accent-line"></div>
                                <h2 class="vb-cat-parent-title">
                                    <a href="<?php echo esc_url( $parent_url ); ?>">
                                        <span class="vb-cat-bullet">◉</span> <?php echo esc_html( $clean_parent_name ); ?>
                                        <span class="vb-count">(<?php echo esc_html( $padded_count ); ?>)</span>
                                    </a>
                                </h2>

                                <?php if ( ! empty( $children ) && ! is_wp_error( $children ) ) : ?>
                                    <ul class="vb-subcat-list">
                                        <?php foreach ( $children as $child ) : 
                                            $child_url = get_term_link( $child );
                                        ?>
                                            <li class="vb-subcat-item">
                                                <h3 class="vb-subcat-title">
                                                    <a href="<?php echo esc_url( $child_url ); ?>">
                                                        <?php echo esc_html( $child->name ); ?>
                                                        <span class="vb-count">(<?php echo (int) $child->count; ?>)</span>
                                                    </a>
                                                </h3>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Companies Directory View with Filter Bar (Category, Region/Location, Keywords Search)
     */
    public static function render_companies( $atts ) {
        $atts = shortcode_atts( [
            'per_page' => 24,
            'orderby'  => 'date',
            'order'    => 'DESC',
        ], $atts, 'vb_companies' );

        $search_val = sanitize_text_field( $_GET['vb_search'] ?? '' );
        $cat_slug   = sanitize_text_field( $_GET['vb_cat'] ?? '' );
        $loc_slug   = sanitize_text_field( $_GET['vb_loc'] ?? '' );

        $args = [
            'post_type'      => [ 'vb_listing', 'at_biz_dir' ],
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['per_page'],
            'orderby'        => $atts['orderby'],
            'order'          => $atts['order'],
        ];

        if ( ! empty( $search_val ) ) {
            $args['s'] = $search_val;
        }

        $tax_query = [];
        if ( ! empty( $cat_slug ) ) {
            $tax_query[] = [
                'taxonomy' => taxonomy_exists( 'acadp_categories' ) ? 'acadp_categories' : 'vb_category',
                'field'    => 'slug',
                'terms'    => $cat_slug,
            ];
        }
        if ( ! empty( $loc_slug ) ) {
            $tax_query[] = [
                'taxonomy' => taxonomy_exists( 'acadp_locations' ) ? 'acadp_locations' : 'vb_location',
                'field'    => 'slug',
                'terms'    => $loc_slug,
            ];
        }
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        $args['meta_query'] = [
            'relation' => 'OR',
            [
                'key'     => '_vb_type',
                'value'   => 'company',
                'compare' => '=',
            ],
            [
                'key'     => '_vb_type',
                'compare' => 'NOT EXISTS',
            ],
        ];

        $query = new WP_Query( $args );

        // Fetch categories for dropdown
        $tax_cat = taxonomy_exists( 'acadp_categories' ) ? 'acadp_categories' : 'vb_category';
        $top_cats = get_terms( [
            'taxonomy'   => $tax_cat,
            'hide_empty' => false,
            'parent'     => 0,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );

        // Fetch Czech Republic locations for dropdown
        $tax_loc = taxonomy_exists( 'acadp_locations' ) ? 'acadp_locations' : 'vb_location';
        $czech_term = get_term_by( 'slug', 'chehiya', $tax_loc );
        $locations = [];
        if ( $czech_term ) {
            $locations = get_terms( [
                'taxonomy'   => $tax_loc,
                'hide_empty' => false,
                'parent'     => $czech_term->term_id,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ] );
        }
        if ( empty( $locations ) || is_wp_error( $locations ) ) {
            $locations = get_terms( [
                'taxonomy'   => $tax_loc,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ] );
        }

        $has_filters = ( ! empty( $search_val ) || ! empty( $cat_slug ) || ! empty( $loc_slug ) );

        ob_start();
        ?>
        <div class="vb-company-directory-wrap">
            <!-- Filter Bar -->
            <form method="GET" action="<?php echo esc_url( get_permalink() ); ?>" class="vb-company-filter-bar">
                <div class="vb-filter-grid">
                    <div class="vb-filter-cell vb-filter-search-cell">
                        <span class="vb-f-icon">🔍</span>
                        <input type="text" name="vb_search" value="<?php echo esc_attr( $search_val ); ?>" placeholder="Поиск компании или услуги..." class="vb-f-input">
                    </div>

                    <div class="vb-filter-cell">
                        <span class="vb-f-icon">📁</span>
                        <select name="vb_cat" class="vb-f-select">
                            <option value="">Все категории</option>
                            <?php if ( ! empty( $top_cats ) && ! is_wp_error( $top_cats ) ) : ?>
                                <?php foreach ( $top_cats as $cat ) : 
                                    $c_clean = trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $cat->name ) );
                                ?>
                                    <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $cat_slug, $cat->slug ); ?>>
                                        ◉ <?php echo esc_html( $c_clean ); ?>
                                    </option>
                                    <?php 
                                    $sub_cats = get_terms( [
                                        'taxonomy'   => $tax_cat,
                                        'hide_empty' => false,
                                        'parent'     => $cat->term_id,
                                        'orderby'    => 'name',
                                        'order'      => 'ASC',
                                    ] );
                                    if ( ! empty( $sub_cats ) && ! is_wp_error( $sub_cats ) ) :
                                        foreach ( $sub_cats as $sub ) : ?>
                                            <option value="<?php echo esc_attr( $sub->slug ); ?>" <?php selected( $cat_slug, $sub->slug ); ?>>
                                                &nbsp;&nbsp;— <?php echo esc_html( $sub->name ); ?>
                                            </option>
                                        <?php endforeach;
                                    endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="vb-filter-cell">
                        <span class="vb-f-icon">📍</span>
                        <select name="vb_loc" class="vb-f-select">
                            <option value="">Все регионы Чехии</option>
                            <?php if ( ! empty( $locations ) && ! is_wp_error( $locations ) ) : ?>
                                <?php foreach ( $locations as $loc ) : ?>
                                    <option value="<?php echo esc_attr( $loc->slug ); ?>" <?php selected( $loc_slug, $loc->slug ); ?>>
                                        <?php echo esc_html( $loc->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="vb-filter-cell vb-filter-btn-cell">
                        <button type="submit" class="vb-f-btn-submit">Найти</button>
                        <?php if ( $has_filters ) : ?>
                            <a href="<?php echo esc_url( get_permalink() ); ?>" class="vb-f-btn-reset">Сброс</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <?php if ( ! $query->have_posts() ) : ?>
                <div class="vb-empty-box">
                    <p>По вашему запросу компаний не найдено.</p>
                    <?php if ( $has_filters ) : ?>
                        <p><a href="<?php echo esc_url( get_permalink() ); ?>" class="button is-outline">Сбросить фильтры</a></p>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="vb-company-grid">
                    <?php while ( $query->have_posts() ) : $query->the_post(); 
                        $id       = get_the_ID();
                        $address  = get_post_meta( $id, '_vb_address', true ) ?: get_post_meta( $id, '_address', true );
                        $phone    = get_post_meta( $id, '_vb_phone', true ) ?: get_post_meta( $id, '_phone', true );
                        $website  = get_post_meta( $id, '_vb_website', true ) ?: get_post_meta( $id, '_website', true );
                        $tagline  = get_post_meta( $id, '_vb_tagline', true ) ?: get_post_meta( $id, '_tagline', true );
                        $thumb    = get_the_post_thumbnail_url( $id, 'medium' ) ?: '/wp-content/plugins/wp-board-directory/assets/no-logo.svg';

                        $vote_stats = VB_Ratings::get_vote_stats( 'company', $id );
                        $likes      = $vote_stats['likes'];
                        $dislikes   = $vote_stats['dislikes'];
                    ?>
                        <div class="vb-company-card" data-id="<?php echo esc_attr( $id ); ?>">
                            <div class="vb-company-logo-wrap">
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="vb-company-logo">
                                </a>
                            </div>

                            <div class="vb-company-body">
                                <h3 class="vb-company-card-title">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <?php if ( ! empty( $tagline ) && trim( $tagline ) !== '' ) : ?>
                                    <div class="vb-company-tagline"><?php echo esc_html( $tagline ); ?></div>
                                <?php endif; ?>

                                <div class="vb-company-fields">
                                    <div class="vb-c-field">
                                        <span class="vb-c-icon">🌍</span>
                                        <span class="vb-c-text" title="<?php echo esc_attr( $address ?: '—' ); ?>"><?php echo esc_html( $address ?: '—' ); ?></span>
                                    </div>

                                    <div class="vb-c-field">
                                        <span class="vb-c-icon">📞</span>
                                        <?php if ( $phone ) : ?>
                                            <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="vb-c-link"><?php echo esc_html( $phone ); ?></a>
                                        <?php else : ?>
                                            <span class="vb-c-text">—</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="vb-c-field">
                                        <span class="vb-c-icon">🌐</span>
                                        <?php if ( $website ) : ?>
                                            <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="vb-c-link">
                                                <?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', rtrim( $website, '/' ) ) ); ?>
                                            </a>
                                        <?php else : ?>
                                            <span class="vb-c-text">—</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="vb-company-footer">
                                    <div class="vb-rating-dual" title="Оцените компанию (только для зарегистрированных)">
                                        <button type="button" class="vb-btn-vote vb-vote-up" data-target="company" data-id="<?php echo esc_attr( $id ); ?>" data-vote="1" title="Поставить лайк">
                                            <span class="vb-vote-icon">👍</span>
                                            <span class="vb-vote-num" id="vb-likes-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $likes ); ?></span>
                                        </button>
                                        <button type="button" class="vb-btn-vote vb-vote-down" data-target="company" data-id="<?php echo esc_attr( $id ); ?>" data-vote="-1" title="Поставить дизлайк">
                                            <span class="vb-vote-icon">👎</span>
                                            <span class="vb-vote-num" id="vb-dislikes-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $dislikes ); ?></span>
                                        </button>
                                    </div>
                                    <a href="<?php the_permalink(); ?>" class="vb-btn-view-profile">Подробнее &rarr;</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Listings Grid
     */
    public static function render_listings( $atts ) {
        $atts = shortcode_atts( [
            'per_page' => 24,
            'type'     => 'classified',
            'orderby'  => 'date',
            'order'    => 'DESC',
        ], $atts, 'vb_listings' );

        $query = new WP_Query( [
            'post_type'      => 'vb_listing',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['per_page'],
            'meta_query'     => [
                [
                    'key'   => '_vb_type',
                    'value' => $atts['type'],
                ],
            ],
            'orderby'        => $atts['orderby'],
            'order'          => $atts['order'],
        ] );

        if ( ! $query->have_posts() ) {
            return '<p class="vb-empty-state">Объявления не найдены.</p>';
        }

        ob_start();
        ?>
        <div class="vb-listings-grid">
            <?php while ( $query->have_posts() ) : $query->the_post(); 
                $id       = get_the_ID();
                $price    = get_post_meta( $id, '_vb_price', true );
                $thumb    = get_the_post_thumbnail_url( $id, 'medium' ) ?: '/wp-content/plugins/wp-board-directory/assets/no-photo.svg';
                $cats     = wp_get_post_terms( $id, 'acadp_categories', [ 'fields' => 'names' ] );
                $cat_name = ! empty( $cats ) ? $cats[0] : '';
                $stats    = VB_Ratings::get_vote_stats( 'listing', $id );
            ?>
                <div class="vb-card" data-id="<?php echo esc_attr( $id ); ?>">
                    <div class="vb-card-img-wrap">
                        <a href="<?php the_permalink(); ?>">
                            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="vb-card-img">
                        </a>
                    </div>
                    <div class="vb-card-body">
                        <?php if ( $cat_name ) : ?>
                            <div class="vb-card-category"><?php echo esc_html( $cat_name ); ?></div>
                        <?php endif; ?>
                        <h4 class="vb-card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                        <div class="vb-card-price">
                            <?php if ( $price > 0 ) : ?>
                                <strong><?php echo number_format( (float) $price, 0, '.', ' ' ); ?> Kč</strong>
                            <?php else : ?>
                                <strong class="vb-price-free">Договорная / Бесплатно</strong>
                            <?php endif; ?>
                        </div>
                        <div class="vb-card-meta">
                            <span class="vb-meta-date">🕒 <?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></span>
                            <div class="vb-rating-dual">
                                <button type="button" class="vb-btn-vote vb-vote-up" data-target="listing" data-id="<?php echo esc_attr( $id ); ?>" data-vote="1" title="Лайк">
                                    👍 <span id="vb-likes-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $stats['likes'] ); ?></span>
                                </button>
                                <button type="button" class="vb-btn-vote vb-vote-down" data-target="listing" data-id="<?php echo esc_attr( $id ); ?>" data-vote="-1" title="Дизлайк">
                                    👎 <span id="vb-dislikes-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $stats['dislikes'] ); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Turnkey Unified User Personal Dashboard & Profile
     */
    public static function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '
            <div class="vb-auth-box">
                <div class="vb-auth-icon">🔒</div>
                <h3>Вход в личный кабинет</h3>
                <p>Для управления вашими объявлениями, компаниями и профилем, пожалуйста, войдите в систему.</p>
                <div class="vb-auth-buttons">
                    <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="vb-btn-auth-login">Войти</a>
                    <a href="' . esc_url( wp_registration_url() ) . '" class="vb-btn-auth-reg">Регистрация</a>
                </div>
            </div>';
        }

        $user_id = get_current_user_id();
        $user    = wp_get_current_user();

        $active_tab = sanitize_key( $_GET['tab'] ?? 'profile' );
        if ( ! in_array( $active_tab, [ 'profile', 'ads', 'companies' ] ) ) {
            $active_tab = 'profile';
        }

        $phone   = get_user_meta( $user_id, 'phone', true ) ?: get_user_meta( $user_id, 'acadp_phone', true ) ?: get_user_meta( $user_id, 'atbdp_phone', true );
        $city    = get_user_meta( $user_id, 'address', true ) ?: get_user_meta( $user_id, 'user_city', true );
        $company = get_user_meta( $user_id, 'company_name', true ) ?: get_user_meta( $user_id, 'user_company', true );

        // Fetch user classified ads
        $user_ads = get_posts( [
            'post_type'      => [ 'acadp_listings', 'vb_listing' ],
            'post_status'    => [ 'publish', 'draft', 'pending' ],
            'author'         => $user_id,
            'posts_per_page' => -1,
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => '_vb_type',
                    'value'   => 'classified',
                    'compare' => '=',
                ],
                [
                    'key'     => '_vb_type',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ] );

        // Fetch user companies
        $user_companies = get_posts( [
            'post_type'      => [ 'at_biz_dir', 'vb_listing' ],
            'post_status'    => [ 'publish', 'draft', 'pending' ],
            'author'         => $user_id,
            'posts_per_page' => -1,
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => '_vb_type',
                    'value'   => 'company',
                    'compare' => '=',
                ],
                [
                    'key'     => '_vb_type',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ] );

        $is_saved = ! empty( $_GET['saved'] );

        ob_start();
        ?>
        <div class="vb-dashboard-wrap">
            <!-- Header Card -->
            <div class="vb-dash-header-card">
                <div class="vb-dash-user-info">
                    <div class="vb-dash-avatar">
                        <?php echo get_avatar( $user_id, 72 ); ?>
                    </div>
                    <div class="vb-dash-titles">
                        <h2 class="vb-dash-name"><?php echo esc_html( $user->display_name ); ?></h2>
                        <?php if ( $company ) : ?>
                            <div class="vb-dash-company-badge">🏢 <?php echo esc_html( $company ); ?></div>
                        <?php endif; ?>
                        <div class="vb-dash-meta">
                            <span>✉️ <?php echo esc_html( $user->user_email ); ?></span>
                            <?php if ( $phone ) : ?>
                                <span>📞 <?php echo esc_html( $phone ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="vb-dash-stats">
                    <div class="vb-stat-badge">
                        <span class="vb-stat-num"><?php echo count( $user_ads ); ?></span>
                        <span class="vb-stat-label">📢 Объявлений</span>
                    </div>
                    <div class="vb-stat-badge">
                        <span class="vb-stat-num"><?php echo count( $user_companies ); ?></span>
                        <span class="vb-stat-label">🏢 Компаний</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Navigation Tabs -->
            <div class="vb-dash-tabs">
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'profile', get_permalink() ) ); ?>" class="vb-dash-tab <?php echo $active_tab === 'profile' ? 'vb-tab-active' : ''; ?>">
                    👤 Мой профиль
                </a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'ads', get_permalink() ) ); ?>" class="vb-dash-tab <?php echo $active_tab === 'ads' ? 'vb-tab-active' : ''; ?>">
                    📢 Мои объявления (<?php echo count( $user_ads ); ?>)
                </a>
                <a href="<?php echo esc_url( add_query_arg( 'tab', 'companies', get_permalink() ) ); ?>" class="vb-dash-tab <?php echo $active_tab === 'companies' ? 'vb-tab-active' : ''; ?>">
                    🏢 Мои компании (<?php echo count( $user_companies ); ?>)
                </a>
            </div>

            <!-- Tab Content -->
            <div class="vb-dash-content">
                <?php if ( $active_tab === 'profile' ) : ?>
                    <!-- TAB 1: PROFILE EDITING -->
                    <div class="vb-tab-pane vb-pane-profile">
                        <?php if ( $is_saved ) : ?>
                            <div class="vb-alert-success">
                                ✅ Ваши данные успешно сохранены!
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="vb-profile-form">
                            <?php wp_nonce_field( 'vb_save_profile_action', 'vb_profile_nonce' ); ?>
                            <input type="hidden" name="vb_action" value="save_profile">

                            <div class="vb-form-grid">
                                <div class="vb-form-group">
                                    <label class="vb-label">Имя или псевдоним <span class="vb-req">*</span></label>
                                    <input type="text" name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required class="vb-form-input">
                                    <small class="vb-hint">Отображается автором на ваших объявлениях и компании</small>
                                </div>

                                <div class="vb-form-group">
                                    <label class="vb-label">Контактный телефон</label>
                                    <input type="text" name="phone" value="<?php echo esc_attr( $phone ); ?>" placeholder="+420..." class="vb-form-input">
                                    <small class="vb-hint">Будет автоматически подставляться в ваши новые публикации</small>
                                </div>

                                <div class="vb-form-group">
                                    <label class="vb-label">Город / Регион в Чехии</label>
                                    <input type="text" name="city" value="<?php echo esc_attr( $city ); ?>" placeholder="Прага, Брно, Пльзень..." class="vb-form-input">
                                </div>

                                <div class="vb-form-group">
                                    <label class="vb-label">Название компании (если фирма или ИП)</label>
                                    <input type="text" name="company" value="<?php echo esc_attr( $company ); ?>" placeholder="Название вашей фирмы / s.r.o. / OSVČ" class="vb-form-input">
                                </div>

                                <div class="vb-form-group vb-form-full">
                                    <label class="vb-label">Email для связи</label>
                                    <input type="email" value="<?php echo esc_attr( $user->user_email ); ?>" disabled class="vb-form-input vb-input-disabled">
                                    <small class="vb-hint">Для изменения email обратитесь к администратору сайта</small>
                                </div>

                                <div class="vb-form-group vb-form-full">
                                    <label class="vb-label">О себе / О компании</label>
                                    <textarea name="description" rows="4" class="vb-form-textarea" placeholder="Краткое описание вашей деятельности..."><?php echo esc_textarea( $user->description ); ?></textarea>
                                </div>
                            </div>

                            <div class="vb-form-submit-row">
                                <button type="submit" class="vb-btn-save-profile">💾 Сохранить данные профиля</button>
                            </div>
                        </form>
                    </div>

                <?php elseif ( $active_tab === 'ads' ) : ?>
                    <!-- TAB 2: USER ADS -->
                    <div class="vb-tab-pane vb-pane-ads">
                        <div class="vb-pane-toolbar">
                            <h3>Ваши объявления</h3>
                            <a href="<?php echo esc_url( home_url( '/new/' ) ); ?>" class="vb-btn-add-new">+ Подать объявление</a>
                        </div>

                        <?php if ( empty( $user_ads ) ) : ?>
                            <div class="vb-empty-state-box">
                                <p>У вас пока нет поданных объявлений.</p>
                                <a href="<?php echo esc_url( home_url( '/new/' ) ); ?>" class="vb-btn-add-new">+ Подать первое объявление</a>
                            </div>
                        <?php else : ?>
                            <div class="vb-dash-table-wrap">
                                <table class="vb-dash-table">
                                    <thead>
                                        <tr>
                                            <th>Фото</th>
                                            <th>Заголовок</th>
                                            <th>Рубрика</th>
                                            <th>Дата</th>
                                            <th>Статус</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $user_ads as $ad ) : 
                                            $thumb = get_the_post_thumbnail_url( $ad->ID, 'thumbnail' ) ?: '/wp-content/plugins/wp-board-directory/assets/no-photo.svg';
                                            $cats = wp_get_post_terms( $ad->ID, 'acadp_categories', [ 'fields' => 'names' ] );
                                            $cat_str = ! empty( $cats ) ? $cats[0] : '—';
                                            $status_label = ( $ad->post_status === 'publish' ) ? '<span class="vb-badge-pub">Опубликовано</span>' : '<span class="vb-badge-draft">' . esc_html( $ad->post_status ) . '</span>';
                                        ?>
                                            <tr>
                                                <td class="vb-col-thumb">
                                                    <img src="<?php echo esc_url( $thumb ); ?>" alt="" class="vb-table-thumb">
                                                </td>
                                                <td class="vb-col-title">
                                                    <a href="<?php echo esc_url( get_permalink( $ad->ID ) ); ?>" class="vb-table-title-link">
                                                        <strong><?php echo esc_html( $ad->post_title ); ?></strong>
                                                    </a>
                                                </td>
                                                <td class="vb-col-cat"><?php echo esc_html( $cat_str ); ?></td>
                                                <td class="vb-col-date"><?php echo esc_html( get_the_date( 'd.m.Y', $ad->ID ) ); ?></td>
                                                <td class="vb-col-status"><?php echo $status_label; ?></td>
                                                <td class="vb-col-actions">
                                                    <a href="<?php echo esc_url( get_permalink( $ad->ID ) ); ?>" class="vb-btn-action vb-btn-view" target="_blank">Просмотр</a>
                                                    <a href="<?php echo esc_url( home_url( '/new/?listing_id=' . $ad->ID ) ); ?>" class="vb-btn-action vb-btn-edit">Изменить</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ( $active_tab === 'companies' ) : ?>
                    <!-- TAB 3: USER COMPANIES -->
                    <div class="vb-tab-pane vb-pane-companies">
                        <div class="vb-pane-toolbar">
                            <h3>Ваши компании в каталоге</h3>
                            <a href="<?php echo esc_url( home_url( '/add-listing/' ) ); ?>" class="vb-btn-add-new">+ Добавить компанию</a>
                        </div>

                        <?php if ( empty( $user_companies ) ) : ?>
                            <div class="vb-empty-state-box">
                                <p>У вас пока нет зарегистрированных компаний в каталоге.</p>
                                <a href="<?php echo esc_url( home_url( '/add-listing/' ) ); ?>" class="vb-btn-add-new">+ Зарегистрировать компанию</a>
                            </div>
                        <?php else : ?>
                            <div class="vb-dash-table-wrap">
                                <table class="vb-dash-table">
                                    <thead>
                                        <tr>
                                            <th>Логотип</th>
                                            <th>Название</th>
                                            <th>Адрес / Телефон</th>
                                            <th>Оценки</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $user_companies as $cmp ) : 
                                            $logo = get_the_post_thumbnail_url( $cmp->ID, 'thumbnail' ) ?: '/wp-content/plugins/wp-board-directory/assets/no-logo.svg';
                                            $cmp_addr = get_post_meta( $cmp->ID, '_vb_address', true ) ?: get_post_meta( $cmp->ID, '_address', true );
                                            $cmp_phone = get_post_meta( $cmp->ID, '_vb_phone', true ) ?: get_post_meta( $cmp->ID, '_phone', true );
                                            $stats = VB_Ratings::get_vote_stats( 'company', $cmp->ID );
                                        ?>
                                            <tr>
                                                <td class="vb-col-thumb">
                                                    <img src="<?php echo esc_url( $logo ); ?>" alt="" class="vb-table-thumb">
                                                </td>
                                                <td class="vb-col-title">
                                                    <a href="<?php echo esc_url( get_permalink( $cmp->ID ) ); ?>" class="vb-table-title-link">
                                                        <strong><?php echo esc_html( $cmp->post_title ); ?></strong>
                                                    </a>
                                                </td>
                                                <td class="vb-col-info">
                                                    <div>📍 <?php echo esc_html( $cmp_addr ?: '—' ); ?></div>
                                                    <div>📞 <?php echo esc_html( $cmp_phone ?: '—' ); ?></div>
                                                </td>
                                                <td class="vb-col-ratings">
                                                    <span class="vb-rate-up">👍 <?php echo (int) $stats['likes']; ?></span>
                                                    <span class="vb-rate-down">👎 <?php echo (int) $stats['dislikes']; ?></span>
                                                </td>
                                                <td class="vb-col-actions">
                                                    <a href="<?php echo esc_url( get_permalink( $cmp->ID ) ); ?>" class="vb-btn-action vb-btn-view" target="_blank">Просмотр</a>
                                                    <a href="<?php echo esc_url( home_url( '/add-listing/?listing_id=' . $cmp->ID ) ); ?>" class="vb-btn-action vb-btn-edit">Изменить</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
