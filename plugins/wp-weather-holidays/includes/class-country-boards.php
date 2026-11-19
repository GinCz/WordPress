<?php
/**
 * Class VB_Country_Boards
 * Horizontal country boards widget with SVG flags and large bold typography
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VB_Country_Boards {

    public static $boards = [
        'cz'  => [ 'name' => 'Чехия',    'url' => 'https://doska-cz.ru/',  'flag' => 'cz' ],
        'de'  => [ 'name' => 'Германия', 'url' => 'https://doska-de.ru/',  'flag' => 'de' ],
        'fr'  => [ 'name' => 'Франция',  'url' => 'https://doska-fr.ru/',  'flag' => 'fr' ],
        'esp' => [ 'name' => 'Испания',  'url' => 'https://doska-esp.ru/', 'flag' => 'es' ],
        'it'  => [ 'name' => 'Италия',   'url' => 'https://doska-it.ru/',  'flag' => 'it' ],
        'pl'  => [ 'name' => 'Польша',   'url' => 'https://doska-pl.ru/',  'flag' => 'pl' ],
        'hun' => [ 'name' => 'Венгрия',  'url' => 'https://doska-hun.ru/', 'flag' => 'hu' ],
        'gr'  => [ 'name' => 'Греция',   'url' => 'https://doska-gr.ru/',  'flag' => 'gr' ],
        'isl' => [ 'name' => 'Израиль',  'url' => 'https://doska-isl.ru/', 'flag' => 'il' ],
        'mld' => [ 'name' => 'Молдова',  'url' => 'https://doska-mld.ru/', 'flag' => 'md' ],
        'ua'  => [ 'name' => 'Украина',  'url' => 'https://doska-ua.ru/',  'flag' => 'ua' ],
    ];

    public static function render( $atts = [] ) {
        $current_host = $_SERVER['HTTP_HOST'] ?? '';

        ob_start();
        ?>
        <div class="vb-country-boards-block">
            <div class="vb-cbb-header">
                <span class="vb-cbb-icon">🌍</span>
                <h3 class="vb-cbb-title">Наши доски объявлений в других странах</h3>
            </div>
            <div class="vb-cbb-grid">
                <?php foreach ( self::$boards as $key => $b ) : 
                    $is_current = ( false !== strpos( $current_host, 'doska-' . $key . '.ru' ) );
                    $card_class = 'vb-cbb-card' . ( $is_current ? ' is-current' : '' );
                ?>
                    <a href="<?php echo esc_url( $b['url'] ); ?>" class="<?php echo esc_attr( $card_class ); ?>" target="_blank" rel="noopener" title="Доска объявлений: <?php echo esc_attr( $b['name'] ); ?>">
                        <span class="vb-cbb-flag"><?php echo VB_Weather::get_flag_svg( $b['flag'] ); ?></span>
                        <span class="vb-cbb-name"><?php echo esc_html( mb_strtoupper( $b['name'], 'UTF-8' ) ); ?></span>
                        <?php if ( $is_current ) : ?>
                            <span class="vb-cbb-badge">ВЫ ЗДЕСЬ</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
