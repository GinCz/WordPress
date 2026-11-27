<?php
/**
 * Single Listing Template (Classified Ad & Company Profile)
 *
 * @package WP_Board_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$post_id   = get_the_ID();
$vb_type   = get_post_meta( $post_id, '_vb_type', true ) ?: 'classified';
$price     = get_post_meta( $post_id, '_vb_price', true );
$phone     = get_post_meta( $post_id, '_vb_phone', true ) ?: get_post_meta( $post_id, '_phone', true );
$email     = get_post_meta( $post_id, '_vb_email', true ) ?: get_post_meta( $post_id, '_email', true );
$addr      = get_post_meta( $post_id, '_vb_address', true ) ?: get_post_meta( $post_id, '_address', true );
$wa        = get_post_meta( $post_id, '_vb_whatsapp', true );
$tg        = get_post_meta( $post_id, '_vb_telegram', true );
$web       = get_post_meta( $post_id, '_vb_website', true ) ?: get_post_meta( $post_id, '_website', true );

// Images
$raw_images = get_post_meta( $post_id, '_vb_images', true );
$images = [];
if ( ! empty( $raw_images ) && is_array( $raw_images ) ) {
    foreach ( $raw_images as $img_id ) {
        $url = wp_get_attachment_image_url( $img_id, 'large' );
        if ( $url ) {
            $images[] = [ 'id' => $img_id, 'url' => $url ];
        }
    }
}
if ( empty( $images ) ) {
    $thumb_id = get_post_thumbnail_id( $post_id );
    if ( $thumb_id ) {
        $images[] = [ 'id' => $thumb_id, 'url' => wp_get_attachment_image_url( $thumb_id, 'large' ) ];
    }
}

// Categories & Location
$cats = wp_get_post_terms( $post_id, 'vb_category' );
$primary_cat = ! empty( $cats ) && ! is_wp_error( $cats ) ? $cats[0] : null;

$locs = wp_get_post_terms( $post_id, 'vb_location' );
$primary_loc = ! empty( $locs ) && ! is_wp_error( $locs ) ? $locs[0] : null;

// Rating stats
$stats = VB_Ratings::get_vote_stats( $vb_type === 'company' ? 'company' : 'listing', $post_id );

// Author info
$author_id          = (int) get_post_field( 'post_author', $post_id );
$author_name        = get_the_author_meta( 'display_name', $author_id ) ?: 'Пользователь';
$author_url         = $author_id ? get_author_posts_url( $author_id ) : '';
$author_ads_count   = $author_id ? (int) count_user_posts( $author_id, 'vb_listing', true ) : 0;
$current_uid    = get_current_user_id();
$is_real_author = is_user_logged_in() && ( $author_id === $current_uid );
$is_admin_mode  = is_user_logged_in() && ! $is_real_author && current_user_can( 'edit_post', $post_id );
$can_manage     = $is_real_author || $is_admin_mode;
$edit_url           = home_url( '/new/?listing_id=' . $post_id );
$delete_url         = wp_nonce_url( add_query_arg( [ 'vb_action' => 'delete_item', 'item_id' => $post_id, 'tab' => ( $vb_type === 'company' ? 'companies' : 'ads' ) ], home_url( '/my-ads/' ) ), 'vb_delete_item_' . $post_id );

// Fallback contact details from user meta if not specified in post
if ( empty( $phone ) && $author_id ) {
    $phone = get_user_meta( $author_id, 'phone', true ) ?: get_user_meta( $author_id, 'acadp_phone', true ) ?: get_user_meta( $author_id, 'billing_phone', true );
}
if ( empty( $wa ) && $author_id ) {
    $wa = get_user_meta( $author_id, 'whatsapp', true ) ?: get_user_meta( $author_id, 'vb_whatsapp', true );
}
if ( empty( $tg ) && $author_id ) {
    $tg = get_user_meta( $author_id, 'telegram', true ) ?: get_user_meta( $author_id, 'vb_telegram', true );
}

// Robust multi-phone parser
$phones_list = [];
if ( ! empty( $phone ) ) {
    $raw_phones = preg_split( '/[,;\/|\n]+/', (string) $phone );
    foreach ( $raw_phones as $p_item ) {
        $p_trimmed = trim( $p_item );
        $p_clean   = preg_replace( '/[^\+0-9]/', '', $p_trimmed );
        if ( strlen( preg_replace( '/[^0-9]/', '', $p_clean ) ) >= 7 ) {
            $phones_list[] = [
                'display' => $p_trimmed,
                'clean'   => $p_clean,
            ];
        }
    }
}

// Robust WhatsApp buttons parser
$wa_buttons = [];
if ( ! empty( $wa ) ) {
    $raw_wa = preg_split( '/[,;\/|\n]+/', (string) $wa );
    foreach ( $raw_wa as $w_item ) {
        $w_digits = preg_replace( '/[^0-9]/', '', (string) $w_item );
        if ( strlen( $w_digits ) >= 7 ) {
            $wa_buttons[] = [
                'digits'  => $w_digits,
                'display' => trim( $w_item ),
            ];
        }
    }
} elseif ( ! empty( $phones_list ) ) {
    foreach ( $phones_list as $p_item ) {
        $w_digits = preg_replace( '/[^0-9]/', '', $p_item['clean'] );
        if ( strlen( $w_digits ) >= 7 ) {
            $wa_buttons[] = [
                'digits'  => $w_digits,
                'display' => $p_item['display'],
            ];
        }
    }
}
?>

<div id="content" class="content-area page-wrapper" role="main">
    <div class="row row-main">
        <div class="large-12 col">
            <div class="col-inner">

                <!-- Breadcrumbs -->
                <nav class="vb-single-breadcrumbs">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
                    <span class="vb-bc-sep">&rsaquo;</span>
                    <?php if ( $vb_type === 'company' ) : ?>
                        <a href="<?php echo esc_url( home_url( '/company/' ) ); ?>">Каталог компаний</a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( home_url( '/ads/' ) ); ?>">Все объявления</a>
                    <?php endif; ?>
                    <?php if ( $primary_cat ) : ?>
                        <span class="vb-bc-sep">&rsaquo;</span>
                        <a href="<?php echo esc_url( get_term_link( $primary_cat ) ); ?>"><?php echo esc_html( trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $primary_cat->name ) ) ); ?></a>
                    <?php endif; ?>
                    <span class="vb-bc-sep">&rsaquo;</span>
                    <span class="vb-bc-current"><?php the_title(); ?></span>
                </nav>

                <?php if ( ! empty( $_GET['updated'] ) ) : ?>
                    <div class="vb-alert-success" style="margin-bottom: 20px; padding: 14px 20px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; color: #065f46; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        ✅ Изменения успешно сохранены!
                    </div>
                <?php endif; ?>

                <div class="vb-single-layout">
                    <!-- Left / Main Column -->
                    <div class="vb-single-main">
                        <?php if ( $can_manage ) : ?>
                            <div class="vb-author-manage-bar <?php echo $is_admin_mode ? 'vb-admin-mode-bar' : ''; ?>">
                                <div class="vb-author-manage-left">
                                    <?php if ( $is_real_author ) : ?>
                                        <span class="vb-author-badge">👤 Ваша публикация</span>
                                    <?php else : ?>
                                        <span class="vb-author-badge vb-admin-badge">🛡️ Управление (Администратор)</span>
                                    <?php endif; ?>
                                    <span class="vb-author-meta-status">Статус: <strong><?php echo 'publish' === get_post_status( $post_id ) ? 'Опубликовано' : esc_html( get_post_status( $post_id ) ); ?></strong></span>
                                </div>
                                <div class="vb-author-manage-actions">
                                    <a href="<?php echo esc_url( $edit_url ); ?>" class="vb-btn-author-edit">
                                        ✏️ Редактировать
                                    </a>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" class="vb-btn-author-delete" onclick="return confirm('Вы действительно хотите удалить это объявление? Это действие необратимо.');">
                                        🗑️ Удалить
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h2 class="vb-single-title"><?php the_title(); ?></h2>

                        <div class="vb-single-meta-bar">
                            <span class="vb-meta-item">📅 <?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></span>
                            <?php if ( $primary_loc ) : ?>
                                <span class="vb-meta-item">📍 <?php echo esc_html( $primary_loc->name ); ?></span>
                            <?php elseif ( $addr ) : ?>
                                <span class="vb-meta-item">📍 <?php echo esc_html( $addr ); ?></span>
                            <?php endif; ?>
                            <span class="vb-meta-item">👁️ <?php echo (int) get_post_meta( $post_id, 'views', true ) ?: 1; ?> просм.</span>
                            <span class="vb-meta-item vb-meta-id">ID: #<?php echo esc_html( $post_id ); ?></span>
                        </div>

                        <!-- Gallery / Image Box -->
                        <?php if ( ! empty( $images ) ) : ?>
                            <div class="vb-single-gallery">
                                <div class="vb-main-photo-wrap">
                                    <img id="vb-gallery-main" src="<?php echo esc_url( $images[0]['url'] ); ?>" alt="<?php the_title_attribute(); ?>" class="vb-main-photo">
                                </div>
                                <?php if ( count( $images ) > 1 ) : ?>
                                    <div class="vb-thumbs-strip">
                                        <?php foreach ( $images as $idx => $img ) : ?>
                                            <div class="vb-thumb-box <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="document.getElementById('vb-gallery-main').src='<?php echo esc_url( $img['url'] ); ?>'; document.querySelectorAll('.vb-thumb-box').forEach(function(b){b.classList.remove('active');}); this.classList.add('active');">
                                                <img src="<?php echo esc_url( $img['url'] ); ?>" alt="">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Full Description -->
                        <div class="vb-single-desc-card">
                            <h3 class="vb-section-heading">Описание</h3>
                            <div class="vb-desc-content">
                                <?php the_content(); ?>
                            </div>
                        </div>

                        <!-- Safety & Anti-Fraud Notice -->
                        <div class="vb-safety-box">
                            <div class="vb-safety-icon">🛡️</div>
                            <div class="vb-safety-text">
                                <strong>Безопасные сделки:</strong> Никогда не отправляйте предоплату незнакомым лицам. Встречайтесь лично в публичных местах. Проверяйте товар перед оплатой.
                            </div>
                        </div>

                        <!-- Rating & Anti-Fraud Dual Vote -->
                        <div class="vb-single-rating-box">
                            <span class="vb-rating-label">Оценка публикации (защита от спама и мошенников):</span>
                            <div class="vb-rating-dual">
                                <button type="button" class="vb-btn-vote vb-vote-up" data-target="<?php echo $vb_type === 'company' ? 'company' : 'listing'; ?>" data-id="<?php echo esc_attr( $post_id ); ?>" data-vote="1">
                                    👍 Рекомендую (<span id="vb-likes-<?php echo esc_attr( $post_id ); ?>"><?php echo (int) $stats['likes']; ?></span>)
                                </button>
                                <button type="button" class="vb-btn-vote vb-vote-down" data-target="<?php echo $vb_type === 'company' ? 'company' : 'listing'; ?>" data-id="<?php echo esc_attr( $post_id ); ?>" data-vote="-1">
                                    👎 Сомнительно / Спам (<span id="vb-dislikes-<?php echo esc_attr( $post_id ); ?>"><?php echo (int) $stats['dislikes']; ?></span>)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sidebar / Contact Card -->
                    <div class="vb-single-sidebar">
                        <div class="vb-contact-card">
                            <?php if ( $can_manage ) : ?>
                                <div class="vb-sidebar-author-box <?php echo $is_admin_mode ? 'vb-sidebar-admin-box' : ''; ?>">
                                    <div class="vb-sidebar-author-title">
                                        <?php if ( $is_real_author ) : ?>
                                            ⚙️ Управление объявлением:
                                        <?php else : ?>
                                            🛡️ Режим администратора:
                                        <?php endif; ?>
                                    </div>
                                    <div class="vb-sidebar-author-buttons">
                                        <a href="<?php echo esc_url( $edit_url ); ?>" class="vb-btn-contact vb-btn-sidebar-edit">
                                            ✏️ Редактировать объявление
                                        </a>
                                        <a href="<?php echo esc_url( $delete_url ); ?>" class="vb-btn-contact vb-btn-sidebar-delete" onclick="return confirm('Вы действительно хотите удалить это объявление? Это действие необратимо.');">
                                            🗑️ Удалить объявление
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Price Block -->
                            <div class="vb-single-price-box">
                                <?php if ( $price > 0 ) : 
                                    $curr_info   = VB_CPT::get_currency_info();
                                    $curr_symbol = $curr_info['symbol'];
                                    $curr_code   = $curr_info['currency'];
                                ?>
                                    <div class="vb-single-price"><?php echo number_format( (float) $price, 0, '.', ' ' ); ?> <?php echo esc_html( $curr_symbol ); ?></div>
                                    <?php if ( 'CZK' === $curr_code ) : ?>
                                        <div class="vb-price-eur">~ <?php echo number_format( (float) $price / 25, 0, '.', ' ' ); ?> €</div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div class="vb-single-price vb-price-free">Договорная / Бесплатно</div>
                                <?php endif; ?>
                            </div>

                            <!-- Seller Info -->
                            <div class="vb-seller-block">
                                <div class="vb-seller-avatar">
                                    <?php if ( $author_url ) : ?>
                                        <a href="<?php echo esc_url( $author_url ); ?>" title="Посмотреть все объявления автора">
                                            <?php echo get_avatar( $author_id, 56 ); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo get_avatar( $author_id, 56 ); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="vb-seller-meta">
                                    <div class="vb-seller-name">
                                        <?php if ( $author_url ) : ?>
                                            <a href="<?php echo esc_url( $author_url ); ?>" class="vb-seller-link">
                                                <?php echo esc_html( $author_name ); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo esc_html( $author_name ); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vb-seller-sub">Автор публикации</div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="vb-contact-actions">
                                <?php if ( ! empty( $phones_list ) ) : ?>
                                    <?php foreach ( $phones_list as $ph ) : ?>
                                        <a href="tel:<?php echo esc_attr( $ph['clean'] ); ?>" class="vb-btn-contact vb-btn-phone">
                                            📞 Позвонить: <?php echo esc_html( $ph['display'] ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if ( ! empty( $wa_buttons ) ) : ?>
                                    <?php foreach ( $wa_buttons as $wb ) : ?>
                                        <a href="https://wa.me/<?php echo esc_attr( $wb['digits'] ); ?>" target="_blank" rel="noopener" class="vb-btn-contact vb-btn-whatsapp">
                                            💬 WhatsApp<?php echo count( $wa_buttons ) > 1 ? ': ' . esc_html( $wb['display'] ) : ' — написать'; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if ( $tg ) : 
                                    $tg_user = ltrim( trim( $tg ), '@' );
                                ?>
                                    <a href="https://t.me/<?php echo esc_attr( $tg_user ); ?>" target="_blank" rel="noopener" class="vb-btn-contact vb-btn-telegram">
                                        ✈️ Написать в Telegram
                                    </a>
                                <?php endif; ?>

                                <?php if ( $web ) : ?>
                                    <a href="<?php echo esc_url( $web ); ?>" target="_blank" rel="noopener nofollow" class="vb-btn-contact vb-btn-website">
                                        🌐 Перейти на сайт компании
                                    </a>
                                <?php endif; ?>

                                <?php if ( $author_url ) : ?>
                                    <a href="<?php echo esc_url( $author_url ); ?>" class="vb-btn-contact vb-btn-author-all">
                                        📋 Посмотреть все объявления<?php echo $author_ads_count > 0 ? ' (' . $author_ads_count . ')' : ''; ?>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if ( $addr || $primary_loc ) : ?>
                                <div class="vb-sidebar-info-row">
                                    <strong>Локация:</strong>
                                    <span>
                                        📍 <?php if ( $primary_loc ) : ?>
                                            <a href="<?php echo esc_url( get_term_link( $primary_loc ) ); ?>" class="vb-sidebar-loc-link">
                                                <?php echo esc_html( $primary_loc->name ); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo esc_html( $addr ); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="vb-sidebar-info-row">
                                <strong>Категория:</strong>
                                <span>
                                    <?php if ( $primary_cat ) : ?>
                                        <a href="<?php echo esc_url( get_term_link( $primary_cat ) ); ?>" class="vb-sidebar-cat-link">
                                            <?php echo esc_html( trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $primary_cat->name ) ) ); ?>
                                        </a>
                                    <?php else : ?>
                                        Общие
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="vb-sidebar-post-ad-banner">
                                <p>Есть что продать или предложить?</p>
                                <a href="<?php echo esc_url( home_url( '/new/' ) ); ?>" class="vb-btn-sidebar-new">+ Подать объявление бесплатно</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Similar Listings from the same category -->
                <?php if ( $primary_cat ) : 
                    $similar = new WP_Query( [
                        'post_type'      => 'vb_listing',
                        'post_status'    => 'publish',
                        'posts_per_page' => 3,
                        'post__not_in'   => [ $post_id ],
                        'tax_query'      => [
                            [
                                'taxonomy' => 'vb_category',
                                'field'    => 'term_id',
                                'terms'    => $primary_cat->term_id,
                            ],
                        ],
                    ] );
                    if ( $similar->have_posts() ) :
                ?>
                    <div class="vb-similar-section">
                        <h3 class="vb-similar-heading">Похожие объявления в рубрике «<?php echo esc_html( trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $primary_cat->name ) ) ); ?>»</h3>
                        <div class="vb-listings-grid">
                            <?php while ( $similar->have_posts() ) : $similar->the_post(); 
                                $sim_id = get_the_ID();
                                $sim_price = get_post_meta( $sim_id, '_vb_price', true );
                                $sim_thumb = get_the_post_thumbnail_url( $sim_id, 'medium' ) ?: ( defined( 'VB_BOARD_URL' ) ? ( VB_BOARD_URL . 'assets/no-photo.svg' ) : '/wp-content/plugins/wp-board-directory/assets/no-photo.svg' );
                                $sim_stats = VB_Ratings::get_vote_stats( 'listing', $sim_id );
                            ?>
                                <div class="vb-card" data-id="<?php echo esc_attr( $sim_id ); ?>">
                                    <div class="vb-card-img-wrap">
                                        <a href="<?php the_permalink(); ?>">
                                            <img src="<?php echo esc_url( $sim_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" class="vb-card-img">
                                        </a>
                                    </div>
                                    <div class="vb-card-body">
                                        <h4 class="vb-card-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h4>
                                        <div class="vb-card-price">
                                            <?php if ( $sim_price > 0 ) : ?>
                                                <strong><?php echo number_format( (float) $sim_price, 0, '.', ' ' ); ?> <?php echo esc_html( VB_CPT::get_currency_symbol() ); ?></strong>
                                            <?php else : ?>
                                                <strong class="vb-price-free">Договорная</strong>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </div>
                    </div>
                <?php endif; endif; ?>

            </div>
        </div>
    </div>
</div>

<?php
get_footer();
