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

        // Compatibility Aliases for seamless drop-in
        add_shortcode( 'acadp_categories', [ __CLASS__, 'render_categories' ] );
        add_shortcode( 'acadp_listings', [ __CLASS__, 'render_listings' ] );
    }

    /**
     * Categories Grid
     */
    public static function render_categories( $atts ) {
        $terms = get_terms( [
            'taxonomy'   => 'vb_category',
            'hide_empty' => false,
            'parent'     => 0,
        ] );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return '<p class="vb-empty-state">Категории пока не созданы или не импортированы.</p>';
        }

        ob_start();
        ?>
        <div class="vb-categories-grid">
            <?php foreach ( $terms as $term ) : 
                $count = $term->count;
                $link  = get_term_link( $term );
            ?>
                <a href="<?php echo esc_url( $link ); ?>" class="vb-category-card">
                    <span class="vb-cat-icon">📂</span>
                    <span class="vb-cat-title"><?php echo esc_html( $term->name ); ?></span>
                    <span class="vb-cat-count"><?php echo esc_html( $count ); ?></span>
                </a>
            <?php endforeach; ?>
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
            'type'     => 'classified', // 'classified' or 'company' or 'all'
            'orderby'  => 'date',
            'order'    => 'DESC',
        ], $atts, 'vb_listings' );

        $query_args = [
            'post_type'      => 'vb_listing',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['per_page'],
            'orderby'        => $atts['orderby'],
            'order'          => $atts['order'],
        ];

        if ( 'all' !== $atts['type'] ) {
            $query_args['meta_query'] = [
                [
                    'key'   => '_vb_type',
                    'value' => $atts['type'],
                ],
            ];
        }

        $query = new WP_Query( $query_args );

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
                $type     = get_post_meta( $id, '_vb_type', true );
                $rating   = (int) get_post_meta( $id, '_vb_rating_score', true );
                $thumb    = get_the_post_thumbnail_url( $id, 'medium' ) ?: '/wp-content/plugins/wp-board-directory/assets/no-photo.svg';
                $cats     = wp_get_post_terms( $id, 'vb_category', [ 'fields' => 'names' ] );
                $cat_name = ! empty( $cats ) ? $cats[0] : '';
            ?>
                <div class="vb-card" data-id="<?php echo esc_attr( $id ); ?>">
                    <div class="vb-card-img-wrap">
                        <a href="<?php the_permalink(); ?>">
                            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="vb-card-img">
                        </a>
                        <?php if ( 'company' === $type ) : ?>
                            <span class="vb-badge vb-badge-company">🏢 Компания</span>
                        <?php endif; ?>
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
                            <div class="vb-rating-wrap" title="Рейтинг и оценки">
                                <button type="button" class="vb-btn-vote vb-vote-up" data-target="listing" data-id="<?php echo esc_attr( $id ); ?>" data-vote="1" title="Нравится">👍</button>
                                <span class="vb-vote-score" id="vb-score-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $rating ); ?></span>
                                <button type="button" class="vb-btn-vote vb-vote-down" data-target="listing" data-id="<?php echo esc_attr( $id ); ?>" data-vote="-1" title="Не нравится">👎</button>
                            </div>
                        </div>
                        <?php if ( $phone ) : ?>
                            <div class="vb-card-actions">
                                <a href="tel:<?php echo esc_attr( $phone ); ?>" class="vb-btn-action vb-btn-phone">📞 Позвонить</a>
                                <a href="https://wa.me/<?php echo preg_replace( '/\D/', '', $phone ); ?>" target="_blank" class="vb-btn-action vb-btn-wa">💬 WhatsApp</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Companies Directory View
     */
    public static function render_companies( $atts ) {
        $atts['type'] = 'company';
        return self::render_listings( $atts );
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
                            <th style="padding: 10px; border: 1px solid #ddd;">Статус</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $user_posts as $p ) : 
                            $type = get_post_meta( $p->ID, '_vb_type', true );
                        ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd;"><a href="<?php echo get_permalink( $p->ID ); ?>"><strong><?php echo esc_html( $p->post_title ); ?></strong></a></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $type === 'company' ? '🏢 Фирма' : '📢 Объявление'; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo get_the_date( 'd.m.Y', $p->ID ); ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo esc_html( $p->post_status ); ?></td>
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
