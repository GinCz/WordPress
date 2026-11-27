<?php
/**
 * Author Listings Template
 *
 * Displays all ads posted by a specific author/client.
 *
 * @package WP_Board_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$author = get_queried_object();
$author_id = $author && isset( $author->ID ) ? (int) $author->ID : 0;
$author_name = $author ? ( $author->display_name ?: $author->user_login ) : 'Пользователь';

// Author Meta
$reg_date = $author && ! empty( $author->user_registered ) ? date_i18n( 'F Y', strtotime( $author->user_registered ) ) : '';
$author_phone = get_user_meta( $author_id, 'phone', true ) ?: get_user_meta( $author_id, 'acadp_phone', true ) ?: get_user_meta( $author_id, 'billing_phone', true );
$author_wa = get_user_meta( $author_id, 'whatsapp', true ) ?: get_user_meta( $author_id, 'vb_whatsapp', true );
$author_city = get_user_meta( $author_id, 'address', true ) ?: get_user_meta( $author_id, 'user_city', true );
$author_bio = $author ? $author->description : '';

// Published Ads Query
$paged = max( 1, (int) ( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ?: ( $_GET['paged'] ?? 1 ) ) );
$author_query = new WP_Query( [
    'post_type'      => 'vb_listing',
    'post_status'    => 'publish',
    'author'         => $author_id,
    'posts_per_page' => 24,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
] );

$total_ads = $author_query->found_posts;
?>

<div id="content" class="content-area page-wrapper" role="main">
    <div class="row row-main">
        <div class="large-12 col">
            <div class="col-inner">

                <!-- Breadcrumbs -->
                <nav class="vb-single-breadcrumbs">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
                    <span class="vb-bc-sep">&rsaquo;</span>
                    <a href="<?php echo esc_url( home_url( '/ads/' ) ); ?>">Все объявления</a>
                    <span class="vb-bc-sep">&rsaquo;</span>
                    <span class="vb-bc-current">Объявления автора: <?php echo esc_html( $author_name ); ?></span>
                </nav>

                <!-- Author Profile Card -->
                <div class="vb-author-profile-card">
                    <div class="vb-author-profile-header">
                        <div class="vb-author-avatar-big">
                            <?php echo get_avatar( $author_id, 80 ); ?>
                        </div>
                        <div class="vb-author-profile-info">
                            <h1 class="vb-author-profile-name"><?php echo esc_html( $author_name ); ?></h1>
                            <div class="vb-author-profile-badges">
                                <span class="vb-author-badge-pill">👤 Автор объявлений</span>
                                <span class="vb-author-badge-pill vb-badge-count">📋 <?php echo esc_html( $total_ads ); ?> <?php echo ( 1 === (int) $total_ads ? 'объявление' : ( in_array( $total_ads % 10, [ 2, 3, 4 ], true ) && ! in_array( $total_ads % 100, [ 12, 13, 14 ], true ) ? 'объявления' : 'объявлений' ) ); ?></span>
                                <?php if ( $reg_date ) : ?>
                                    <span class="vb-author-badge-pill vb-badge-date">📅 На сайте с <?php echo esc_html( $reg_date ); ?></span>
                                <?php endif; ?>
                                <?php if ( $author_city ) : ?>
                                    <span class="vb-author-badge-pill vb-badge-city">📍 <?php echo esc_html( $author_city ); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ( ! empty( $author_bio ) ) : ?>
                                <p class="vb-author-bio"><?php echo nl2br( esc_html( $author_bio ) ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Listings Section Header -->
                <div class="vb-tax-header">
                    <div class="vb-tax-title-wrap">
                        <h2 class="vb-tax-title">Объявления автора</h2>
                        <span class="vb-tax-total-badge"><?php echo (int) $total_ads; ?> объявл.</span>
                    </div>
                    <div class="vb-tax-cta">
                        <a href="<?php echo esc_url( home_url( '/new/' ) ); ?>" class="vb-btn-add-new">+ Подать объявление</a>
                    </div>
                </div>

                <!-- Listings Grid -->
                <?php if ( $author_query->have_posts() ) : ?>
                    <div class="vb-listings-grid">
                        <?php while ( $author_query->have_posts() ) : $author_query->the_post(); 
                            $item_id   = get_the_ID();
                            $price     = get_post_meta( $item_id, '_vb_price', true );
                            $thumb     = get_the_post_thumbnail_url( $item_id, 'medium' );
                            if ( ! $thumb ) {
                                $raw_imgs = get_post_meta( $item_id, '_vb_images', true );
                                $imgs     = maybe_unserialize( $raw_imgs );
                                if ( is_array( $imgs ) && ! empty( $imgs ) && is_numeric( $imgs[0] ) ) {
                                    $thumb = wp_get_attachment_image_url( (int) $imgs[0], 'medium' );
                                }
                            }
                            if ( ! $thumb ) {
                                $thumb = defined( 'VB_BOARD_URL' ) ? ( VB_BOARD_URL . 'assets/no-photo.svg' ) : '/wp-content/plugins/wp-board-directory/assets/no-photo.svg';
                            }
                            $cats     = wp_get_post_terms( $item_id, 'vb_category', [ 'fields' => 'names' ] );
                            $cat_name = ! empty( $cats ) ? $cats[0] : '';
                            $locs     = wp_get_post_terms( $item_id, 'vb_location', [ 'fields' => 'names' ] );
                            $loc_name = ! empty( $locs ) ? $locs[0] : '';
                            $curr_info = VB_CPT::get_currency_info();
                        ?>
                            <div class="vb-card" data-id="<?php echo esc_attr( $item_id ); ?>">
                                <div class="vb-card-img-wrap">
                                    <a href="<?php the_permalink(); ?>">
                                        <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="vb-card-img">
                                    </a>
                                </div>
                                <div class="vb-card-body">
                                    <?php if ( $cat_name ) : ?>
                                        <div class="vb-card-category"><?php echo esc_html( trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $cat_name ) ) ); ?></div>
                                    <?php endif; ?>
                                    <h4 class="vb-card-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h4>
                                    <div class="vb-card-price">
                                        <?php if ( $price > 0 ) : ?>
                                            <strong><?php echo number_format( (float) $price, 0, '.', ' ' ); ?> <?php echo esc_html( $curr_info['symbol'] ); ?></strong>
                                            <?php if ( 'CZK' === $curr_info['currency'] ) : ?>
                                                <span class="vb-price-eur">~ <?php echo number_format( (float) $price / 25, 0, '.', ' ' ); ?> €</span>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <strong class="vb-price-free">Договорная / Бесплатно</strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vb-card-meta">
                                        <span class="vb-meta-date">🕒 <?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></span>
                                        <?php if ( $loc_name ) : ?>
                                            <span class="vb-meta-loc">📍 <?php echo esc_html( $loc_name ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ( $author_query->max_num_pages > 1 ) : ?>
                        <div class="vb-pagination">
                            <?php
                            echo paginate_links( [
                                'total'     => $author_query->max_num_pages,
                                'current'   => $paged,
                                'prev_text' => '&laquo; Назад',
                                'next_text' => 'Вперед &raquo;',
                            ] );
                            ?>
                        </div>
                    <?php endif; ?>

                <?php else : ?>
                    <div class="vb-empty-state-box" style="text-align: center; padding: 60px 20px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; margin: 30px 0;">
                        <div style="font-size: 3rem; margin-bottom: 12px;">📭</div>
                        <h3 style="color: #334155; margin-bottom: 8px;">У автора пока нет активных объявлений</h3>
                        <p style="color: #64748b; margin-bottom: 24px;">Все опубликованные объявления пользователя будут отображаться здесь.</p>
                        <a href="<?php echo esc_url( home_url( '/ads/' ) ); ?>" class="vb-btn-add-new">Смотреть все объявления на сайте</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php
get_footer();
