<?php
/**
 * Taxonomy Archive Template for vb_category and vb_location
 *
 * @package WP_Board_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$term = get_queried_object();
$term_name = $term ? trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $term->name ) ) : 'Категория';
$tax = $term ? $term->taxonomy : 'vb_category';

// Subcategories if parent
$children = [];
if ( $term && $term->term_id ) {
    $children = get_terms( [
        'taxonomy'   => $tax,
        'hide_empty' => false,
        'parent'     => $term->term_id,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );
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
                    <a href="<?php echo esc_url( home_url( '/inzeraty/' ) ); ?>">Все объявления</a>
                    <?php if ( $term && $term->parent ) : 
                        $parent_term = get_term( $term->parent, $tax );
                        if ( $parent_term && ! is_wp_error( $parent_term ) ) :
                    ?>
                        <span class="vb-bc-sep">&rsaquo;</span>
                        <a href="<?php echo esc_url( get_term_link( $parent_term ) ); ?>"><?php echo esc_html( trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $parent_term->name ) ) ); ?></a>
                    <?php endif; endif; ?>
                    <span class="vb-bc-sep">&rsaquo;</span>
                    <span class="vb-bc-current"><?php echo esc_html( $term_name ); ?></span>
                </nav>

                <div class="vb-tax-header">
                    <div class="vb-tax-title-wrap">
                        <h1 class="vb-tax-title">Объявления: <?php echo esc_html( $term_name ); ?></h1>
                        <span class="vb-tax-total-badge"><?php echo (int) ( $term->count ?? 0 ); ?> объявл.</span>
                    </div>
                    <div class="vb-tax-cta">
                        <a href="<?php echo esc_url( home_url( '/new/' ) ); ?>" class="vb-btn-add-new">+ Подать объявление</a>
                    </div>
                </div>

                <!-- Subcategories chips/pills -->
                <?php if ( ! empty( $children ) && ! is_wp_error( $children ) ) : ?>
                    <div class="vb-subcats-bar">
                        <?php foreach ( $children as $child ) : ?>
                            <a href="<?php echo esc_url( get_term_link( $child ) ); ?>" class="vb-subcat-chip">
                                <?php echo esc_html( $child->name ); ?> (<?php echo (int) $child->count; ?>)
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Listings Grid -->
                <?php if ( have_posts() ) : ?>
                    <div class="vb-listings-grid">
                        <?php while ( have_posts() ) : the_post(); 
                            $id       = get_the_ID();
                            $price    = get_post_meta( $id, '_vb_price', true );
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
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="vb-pagination-wrap">
                        <?php echo paginate_links(); ?>
                    </div>
                <?php else : ?>
                    <div class="vb-empty-state-box">
                        <p>В этой категории пока нет опубликованных объявлений.</p>
                        <a href="<?php echo esc_url( home_url( '/new/' ) ); ?>" class="vb-btn-add-new">+ Подать первое объявление</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php
get_footer();
