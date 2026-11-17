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

        // 301 Redirect for legacy /all-listings/ to /company/
        add_action( 'template_redirect', [ __CLASS__, 'handle_legacy_redirects' ] );
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
     * Hierarchical 3-Column Categories Grid (Exact layout with full keywords, H2 and H3)
     */
    public static function render_categories( $atts ) {
        // Taxonomy preference: vb_category, fallback to acadp_categories
        $tax = taxonomy_exists( 'vb_category' ) ? 'vb_category' : 'acadp_categories';

        // 9 standard parent categories grouped into 3 columns
        $col_slugs = [
            0 => [ 'avto', 'rabota-i-biznes', 'sredstva-svyazi' ],
            1 => [ 'dlya-doma', 'razvlecheniya', 'hozyajstvo' ],
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

                            // Fetch children
                            $children = get_terms( [
                                'taxonomy'   => $tax,
                                'hide_empty' => false,
                                'parent'     => $parent->term_id,
                                'orderby'    => 'name',
                                'order'      => 'ASC',
                            ] );

                            // Calculate padded count (sum of all children + parent)
                            $padded_count = (int) $parent->count;
                            if ( ! empty( $children ) && ! is_wp_error( $children ) ) {
                                foreach ( $children as $ch ) {
                                    $padded_count += (int) $ch->count;
                                }
                            }

                            $parent_url = get_term_link( $parent );
                        ?>
                            <div class="vb-cat-group">
                                <h2 class="vb-cat-parent-title">
                                    <a href="<?php echo esc_url( $parent_url ); ?>">
                                        <?php echo esc_html( $parent->name ); ?>
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
                                                        <span class="vb-count">(<?php echo esc_html( $child->count ); ?>)</span>
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
     * Companies Directory View (Equal height cards, 3 in a row, Likes & Dislikes, no red badges)
     */
    public static function render_companies( $atts ) {
        $atts = shortcode_atts( [
            'per_page' => 24,
            'orderby'  => 'date',
            'order'    => 'DESC',
        ], $atts, 'vb_companies' );

        $query = new WP_Query( [
            'post_type'      => 'vb_listing',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['per_page'],
            'meta_query'     => [
                [
                    'key'   => '_vb_type',
                    'value' => 'company',
                ],
            ],
            'orderby'        => $atts['orderby'],
            'order'          => $atts['order'],
        ] );

        if ( ! $query->have_posts() ) {
            return '<div class="vb-empty-box"><p>Компании пока не зарегистрированы в каталоге.</p></div>';
        }

        ob_start();
        ?>
        <div class="vb-company-directory-wrap">
            <div class="vb-company-grid">
                <?php while ( $query->have_posts() ) : $query->the_post(); 
                    $id       = get_the_ID();
                    $address  = get_post_meta( $id, '_vb_address', true );
                    $phone    = get_post_meta( $id, '_vb_phone', true );
                    $website  = get_post_meta( $id, '_vb_website', true );
                    $tagline  = get_post_meta( $id, '_vb_tagline', true );
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

                            <?php if ( $tagline ) : ?>
                                <div class="vb-company-tagline"><?php echo esc_html( $tagline ); ?></div>
                            <?php else : ?>
                                <div class="vb-company-tagline">&nbsp;</div>
                            <?php endif; ?>

                            <div class="vb-company-fields">
                                <?php if ( $address ) : ?>
                                    <div class="vb-c-field">
                                        <span class="vb-c-icon">🌍</span>
                                        <span class="vb-c-text" title="<?php echo esc_attr( $address ); ?>"><?php echo esc_html( $address ); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ( $phone ) : ?>
                                    <div class="vb-c-field">
                                        <span class="vb-c-icon">📞</span>
                                        <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="vb-c-link"><?php echo esc_html( $phone ); ?></a>
                                    </div>
                                <?php endif; ?>

                                <?php if ( $website ) : ?>
                                    <div class="vb-c-field">
                                        <span class="vb-c-icon">🌐</span>
                                        <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="vb-c-link">
                                            <?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', rtrim( $website, '/' ) ) ); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
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
                $phone    = get_post_meta( $id, '_vb_phone', true );
                $thumb    = get_the_post_thumbnail_url( $id, 'medium' ) ?: '/wp-content/plugins/wp-board-directory/assets/no-photo.svg';
                $cats     = wp_get_post_terms( $id, 'vb_category', [ 'fields' => 'names' ] );
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
     * User Personal Dashboard
     */
    public static function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '<div class="vb-auth-box"><h3>Личный кабинет</h3><p>Для управления вашими объявлениями и профилем, пожалуйста, <a href="' . wp_login_url( get_permalink() ) . '">войдите</a> или <a href="' . wp_registration_url() . '">зарегистрируйтесь</a>.</p></div>';
        }

        $user_id = get_current_user_id();
        $user    = wp_get_current_user();

        $user_posts = get_posts( [
            'post_type'      => 'vb_listing',
            'post_status'    => 'any',
            'author'         => $user_id,
            'posts_per_page' => -1,
        ] );

        ob_start();
        ?>
        <div class="vb-dashboard">
            <div class="vb-dash-header">
                <h3>Здравствуйте, <?php echo esc_html( $user->display_name ); ?>!</h3>
                <p>Ваш email: <strong><?php echo esc_html( $user->user_email ); ?></strong></p>
            </div>

            <h4>Ваши активные объявления и компании (<?php echo count( $user_posts ); ?>):</h4>

            <?php if ( empty( $user_posts ) ) : ?>
                <p>У вас пока нет опубликованных объявлений.</p>
            <?php else : ?>
                <table class="vb-dash-table" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <thead>
                        <tr style="background: #f4f4f4; text-align: left;">
                            <th style="padding: 10px; border: 1px solid #ddd;">Заголовок</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Тип</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Дата</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Оценки</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $user_posts as $p ) : 
                            $type  = get_post_meta( $p->ID, '_vb_type', true );
                            $stats = VB_Ratings::get_vote_stats( $type === 'company' ? 'company' : 'listing', $p->ID );
                        ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd;"><a href="<?php echo get_permalink( $p->ID ); ?>"><strong><?php echo esc_html( $p->post_title ); ?></strong></a></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $type === 'company' ? '🏢 Фирма' : '📢 Объявление'; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo get_the_date( 'd.m.Y', $p->ID ); ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;">👍 <?php echo $stats['likes']; ?> | 👎 <?php echo $stats['dislikes']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <a href="<?php echo get_permalink( $p->ID ); ?>" class="button button-small">Просмотр</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
