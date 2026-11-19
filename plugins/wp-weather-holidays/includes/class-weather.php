<?php
/**
 * Class VB_Weather
 * Fetches and caches live weather for 11 capitals via Open-Meteo API
 *
 * @package WP_Weather_Holidays
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VB_Weather {

    const TRANSIENT_KEY = 'vb_capitals_weather_cache';
    const CACHE_TIME    = 3600; // 1 hour cache

    public static $capitals = [
        'prague' => [
            'name' => 'Прага',
            'flag' => '🇨🇿',
            'lat'  => 50.08,
            'lon'  => 14.43,
        ],
        'berlin' => [
            'name' => 'Берлин',
            'flag' => '🇩🇪',
            'lat'  => 52.52,
            'lon'  => 13.40,
        ],
        'paris' => [
            'name' => 'Париж',
            'flag' => '🇫🇷',
            'lat'  => 48.85,
            'lon'  => 2.35,
        ],
        'madrid' => [
            'name' => 'Мадрид',
            'flag' => '🇪🇸',
            'lat'  => 40.41,
            'lon'  => -3.70,
        ],
        'rome' => [
            'name' => 'Рим',
            'flag' => '🇮🇹',
            'lat'  => 41.90,
            'lon'  => 12.49,
        ],
        'warsaw' => [
            'name' => 'Варшава',
            'flag' => '🇵🇱',
            'lat'  => 52.23,
            'lon'  => 21.01,
        ],
        'budapest' => [
            'name' => 'Будапешт',
            'flag' => '🇭🇺',
            'lat'  => 47.49,
            'lon'  => 19.04,
        ],
        'athens' => [
            'name' => 'Афины',
            'flag' => '🇬🇷',
            'lat'  => 37.98,
            'lon'  => 23.72,
        ],
        'jerusalem' => [
            'name' => 'Иерусалим',
            'flag' => '🇮🇱',
            'lat'  => 31.76,
            'lon'  => 35.21,
        ],
        'chisinau' => [
            'name' => 'Кишинев',
            'flag' => '🇲🇩',
            'lat'  => 47.01,
            'lon'  => 28.86,
        ],
        'kyiv' => [
            'name' => 'Киев',
            'flag' => '🇺🇦',
            'lat'  => 50.45,
            'lon'  => 30.52,
        ],
    ];

    /**
     * Map WMO code to emoji icon and description
     */
    public static function get_weather_desc( $code ) {
        $code = (int) $code;
        switch ( $code ) {
            case 0:
                return [ 'icon' => '☀️', 'desc' => 'Ясно' ];
            case 1:
                return [ 'icon' => '🌤️', 'desc' => 'Преимущественно ясно' ];
            case 2:
                return [ 'icon' => '⛅', 'desc' => 'Переменная облачность' ];
            case 3:
                return [ 'icon' => '☁️', 'desc' => 'Пасмурно' ];
            case 45:
            case 48:
                return [ 'icon' => '🌫️', 'desc' => 'Туман' ];
            case 51:
            case 53:
            case 55:
                return [ 'icon' => '🌦️', 'desc' => 'Морось' ];
            case 61:
            case 63:
            case 65:
            case 80:
            case 81:
            case 82:
                return [ 'icon' => '🌧️', 'desc' => 'Дождь' ];
            case 71:
            case 73:
            case 75:
            case 77:
            case 85:
            case 86:
                return [ 'icon' => '❄️', 'desc' => 'Снег' ];
            case 95:
            case 96:
            case 99:
                return [ 'icon' => '⛈️', 'desc' => 'Гроза' ];
            default:
                return [ 'icon' => '⛅', 'desc' => 'Облачно' ];
        }
    }

    /**
     * Fetch weather data with caching
     */
    public static function get_weather_data() {
        $cached = get_transient( self::TRANSIENT_KEY );
        if ( false !== $cached && is_array( $cached ) && ! empty( $cached ) ) {
            return $cached;
        }

        // Build batch query for Open-Meteo
        $lats = [];
        $lons = [];
        $keys = array_keys( self::$capitals );

        foreach ( self::$capitals as $c ) {
            $lats[] = $c['lat'];
            $lons[] = $c['lon'];
        }

        $url = sprintf(
            'https://api.open-meteo.com/v1/forecast?latitude=%s&longitude=%s&current_weather=true',
            implode( ',', $lats ),
            implode( ',', $lons )
        );

        $response = wp_remote_get( $url, [ 'timeout' => 5 ] );

        $data = [];
        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( is_array( $body ) ) {
                // If single item vs multiple items
                $items = isset( $body[0] ) ? $body : [ $body ];
                foreach ( $items as $idx => $entry ) {
                    $key = $keys[ $idx ] ?? null;
                    if ( ! $key ) continue;

                    $curr = $entry['current_weather'] ?? [];
                    $temp = isset( $curr['temperature'] ) ? round( (float) $curr['temperature'] ) : null;
                    $wcode = $curr['weathercode'] ?? 2;
                    $info = self::get_weather_desc( $wcode );

                    $data[ $key ] = [
                        'name'  => self::$capitals[ $key ]['name'],
                        'flag'  => self::$capitals[ $key ]['flag'],
                        'temp'  => $temp,
                        'icon'  => $info['icon'],
                        'desc'  => $info['desc'],
                    ];
                }
            }
        }

        // Fallback realistic values if API fails
        if ( empty( $data ) ) {
            foreach ( self::$capitals as $k => $c ) {
                $data[ $k ] = [
                    'name' => $c['name'],
                    'flag' => $c['flag'],
                    'temp' => 10,
                    'icon' => '⛅',
                    'desc' => 'Облачно',
                ];
            }
        }

        set_transient( self::TRANSIENT_KEY, $data, self::CACHE_TIME );
        return $data;
    }

    /**
     * Return SVG vector flag for country
     */
    public static function get_flag_svg( $code ) {
        $flags = [
            'cz'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="5.5" fill="#fff"/><rect y="5.5" width="16" height="5.5" fill="#d7141a"/><polygon points="0,0 8,5.5 0,11" fill="#11457e"/></svg>',
            'prague'   => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="5.5" fill="#fff"/><rect y="5.5" width="16" height="5.5" fill="#d7141a"/><polygon points="0,0 8,5.5 0,11" fill="#11457e"/></svg>',
            'de'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="3.67" fill="#000"/><rect y="3.67" width="16" height="3.67" fill="#dd0000"/><rect y="7.34" width="16" height="3.66" fill="#ffce00"/></svg>',
            'berlin'   => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="3.67" fill="#000"/><rect y="3.67" width="16" height="3.67" fill="#dd0000"/><rect y="7.34" width="16" height="3.66" fill="#ffce00"/></svg>',
            'fr'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="5.33" height="11" fill="#002654"/><rect x="5.33" width="5.34" height="11" fill="#fff"/><rect x="10.67" width="5.33" height="11" fill="#ce1126"/></svg>',
            'paris'    => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="5.33" height="11" fill="#002654"/><rect x="5.33" width="5.34" height="11" fill="#fff"/><rect x="10.67" width="5.33" height="11" fill="#ce1126"/></svg>',
            'es'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="2.75" fill="#aa151b"/><rect y="2.75" width="16" height="5.5" fill="#f1bf00"/><rect y="8.25" width="16" height="2.75" fill="#aa151b"/></svg>',
            'madrid'   => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="2.75" fill="#aa151b"/><rect y="2.75" width="16" height="5.5" fill="#f1bf00"/><rect y="8.25" width="16" height="2.75" fill="#aa151b"/></svg>',
            'it'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="5.33" height="11" fill="#009246"/><rect x="5.33" width="5.34" height="11" fill="#fff"/><rect x="10.67" width="5.33" height="11" fill="#ce2b37"/></svg>',
            'rome'     => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="5.33" height="11" fill="#009246"/><rect x="5.33" width="5.34" height="11" fill="#fff"/><rect x="10.67" width="5.33" height="11" fill="#ce2b37"/></svg>',
            'pl'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="5.5" fill="#fff"/><rect y="5.5" width="16" height="5.5" fill="#dc143c"/></svg>',
            'warsaw'   => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="5.5" fill="#fff"/><rect y="5.5" width="16" height="5.5" fill="#dc143c"/></svg>',
            'hu'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="3.67" fill="#ce2939"/><rect y="3.67" width="16" height="3.67" fill="#fff"/><rect y="7.34" width="16" height="3.66" fill="#477050"/></svg>',
            'budapest' => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="3.67" fill="#ce2939"/><rect y="3.67" width="16" height="3.67" fill="#fff"/><rect y="7.34" width="16" height="3.66" fill="#477050"/></svg>',
            'gr'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="11" fill="#0d5eaf"/><rect y="1.22" width="16" height="1.22" fill="#fff"/><rect y="3.66" width="16" height="1.22" fill="#fff"/><rect y="6.10" width="16" height="1.22" fill="#fff"/><rect y="8.54" width="16" height="1.22" fill="#fff"/><rect width="5.5" height="5.5" fill="#0d5eaf"/><rect x="2.1" width="1.3" height="5.5" fill="#fff"/><rect y="2.1" width="5.5" height="1.3" fill="#fff"/></svg>',
            'athens'   => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="11" fill="#0d5eaf"/><rect y="1.22" width="16" height="1.22" fill="#fff"/><rect y="3.66" width="16" height="1.22" fill="#fff"/><rect y="6.10" width="16" height="1.22" fill="#fff"/><rect y="8.54" width="16" height="1.22" fill="#fff"/><rect width="5.5" height="5.5" fill="#0d5eaf"/><rect x="2.1" width="1.3" height="5.5" fill="#fff"/><rect y="2.1" width="5.5" height="1.3" fill="#fff"/></svg>',
            'il'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="11" fill="#fff"/><rect y="1.2" width="16" height="1.4" fill="#0038b8"/><rect y="8.4" width="16" height="1.4" fill="#0038b8"/><polygon points="8,3.5 9.8,7 6.2,7" fill="none" stroke="#0038b8" stroke-width="0.7"/><polygon points="8,7.5 9.8,4 6.2,4" fill="none" stroke="#0038b8" stroke-width="0.7"/></svg>',
            'jerusalem'=> '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="11" fill="#fff"/><rect y="1.2" width="16" height="1.4" fill="#0038b8"/><rect y="8.4" width="16" height="1.4" fill="#0038b8"/><polygon points="8,3.5 9.8,7 6.2,7" fill="none" stroke="#0038b8" stroke-width="0.7"/><polygon points="8,7.5 9.8,4 6.2,4" fill="none" stroke="#0038b8" stroke-width="0.7"/></svg>',
            'md'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="5.33" height="11" fill="#003da5"/><rect x="5.33" width="5.34" height="11" fill="#ffd100"/><rect x="10.67" width="5.33" height="11" fill="#c8102e"/><circle cx="8" cy="5.5" r="1.5" fill="#a05a2c"/></svg>',
            'chisinau' => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="5.33" height="11" fill="#003da5"/><rect x="5.33" width="5.34" height="11" fill="#ffd100"/><rect x="10.67" width="5.33" height="11" fill="#c8102e"/><circle cx="8" cy="5.5" r="1.5" fill="#a05a2c"/></svg>',
            'ua'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="5.5" fill="#0057b7"/><rect y="5.5" width="16" height="5.5" fill="#ffd700"/></svg>',
            'kyiv'     => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="5.5" fill="#0057b7"/><rect y="5.5" width="16" height="5.5" fill="#ffd700"/></svg>',
            'ru'       => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="3.67" fill="#fff"/><rect y="3.67" width="16" height="3.67" fill="#0039a6"/><rect y="7.34" width="16" height="3.66" fill="#d52b1e"/></svg>',
            'russia'   => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="3.67" fill="#fff"/><rect y="3.67" width="16" height="3.67" fill="#0039a6"/><rect y="7.34" width="16" height="3.66" fill="#d52b1e"/></svg>',
            'esp'      => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="2.75" fill="#aa151b"/><rect y="2.75" width="16" height="5.5" fill="#f1bf00"/><rect y="8.25" width="16" height="2.75" fill="#aa151b"/></svg>',
            'hun'      => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="3.67" fill="#ce2939"/><rect y="3.67" width="16" height="3.67" fill="#fff"/><rect y="7.34" width="16" height="3.66" fill="#477050"/></svg>',
            'isl'      => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="16" height="11" fill="#fff"/><rect y="1.2" width="16" height="1.4" fill="#0038b8"/><rect y="8.4" width="16" height="1.4" fill="#0038b8"/><polygon points="8,3.5 9.8,7 6.2,7" fill="none" stroke="#0038b8" stroke-width="0.7"/><polygon points="8,7.5 9.8,4 6.2,4" fill="none" stroke="#0038b8" stroke-width="0.7"/></svg>',
            'mld'      => '<svg class="vb-flag-svg" viewBox="0 0 16 11" width="16" height="11"><rect width="5.33" height="11" fill="#003da5"/><rect x="5.33" width="5.34" height="11" fill="#ffd100"/><rect x="10.67" width="5.33" height="11" fill="#c8102e"/><circle cx="8" cy="5.5" r="1.5" fill="#a05a2c"/></svg>',
        ];

        return $flags[ strtolower( $code ) ] ?? '<span class="vb-flag-fallback">🌐</span>';
    }

    /**
     * Render compact 1-column weather widget
     */
    public static function render( $atts = [] ) {
        $data = self::get_weather_data();

        ob_start();
        ?>
        <div class="vb-weather-widget">
            <div class="vb-weather-title">
                <span class="vb-wt-icon">🌤️</span>
                <span class="vb-wt-text">Погода</span>
            </div>
            <div class="vb-weather-list">
                <?php foreach ( $data as $k => $c ) : 
                    $temp_str = ( $c['temp'] > 0 ) ? ( '+' . $c['temp'] . '°' ) : ( $c['temp'] . '°' );
                ?>
                    <div class="vb-weather-row" title="<?php echo esc_attr( $c['name'] . ': ' . $c['desc'] . ', ' . $temp_str ); ?>">
                        <div class="vb-wr-left">
                            <span class="vb-wr-flag"><?php echo self::get_flag_svg( $k ); ?></span>
                            <span class="vb-wr-city"><?php echo esc_html( $c['name'] ); ?></span>
                        </div>
                        <div class="vb-wr-right">
                            <span class="vb-wr-temp"><?php echo esc_html( $temp_str ); ?></span>
                            <span class="vb-wr-icon"><?php echo esc_html( $c['icon'] ); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
