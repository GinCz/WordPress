<?php
/**
 * Class VB_Holidays
 * Daily holidays, Czech Svátek, and Name Days (День Ангела) for Europe and Russia.
 *
 * @package WP_Weather_Holidays
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VB_Holidays {

    /**
     * Czech Svátek calendar for every day of the year (MM-DD)
     */
    public static function get_czech_svatek( $month, $day ) {
        $svatky = [
            // Listopad (11)
            '11-01' => 'Felix', '11-02' => 'Památka zesnulých (Tobiáš)', '11-03' => 'Hubert', '11-04' => 'Karel',
            '11-05' => 'Miriam', '11-06' => 'Liběna', '11-07' => 'Saskie', '11-08' => 'Bohumír',
            '11-09' => 'Bohdan', '11-10' => 'Evžen', '11-11' => 'Martin', '11-12' => 'Benedikt',
            '11-13' => 'Tibor', '11-14' => 'Sáva', '11-15' => 'Leopold', '11-16' => 'Otmar',
            '11-17' => 'Mahulena (Den boje za svobodu a demokracii)', '11-18' => 'Romana',
            '11-19' => 'Alžběta', '11-20' => 'Nikola', '11-21' => 'Albert', '11-22' => 'Cecílie',
            '11-23' => 'Klement', '11-24' => 'Emílie', '11-25' => 'Kateřina', '11-26' => 'Artur',
            '11-27' => 'Xenie', '11-28' => 'René', '11-29' => 'Radka', '11-30' => 'Ondřej',
            // Prosinec (12)
            '12-01' => 'Iva', '12-02' => 'Blanka', '12-03' => 'Svatoslav', '12-04' => 'Barbora',
            '12-05' => 'Jitka', '12-06' => 'Mikuláš', '12-07' => 'Ambrož', '12-08' => 'Květoslava',
            '12-09' => 'Vratislav', '12-10' => 'Julie', '12-11' => 'Dana', '12-12' => 'Simona',
            '12-13' => 'Lucie', '12-14' => 'Lýdie', '12-15' => 'Radana', '12-16' => 'Albína',
            '12-17' => 'Daniel', '12-18' => 'Miloslav', '12-19' => 'Ester', '12-20' => 'Dagmar',
            '12-21' => 'Natálie', '12-22' => 'Šimon', '12-23' => 'Vlasta', '12-24' => 'Adam a Eva (Štědrý den)',
            '12-25' => '1. svátek vánoční', '12-26' => 'Štěpán (2. svátek vánoční)', '12-27' => 'Žaneta',
            '12-28' => 'Bohumila', '12-29' => 'Judita', '12-30' => 'David', '12-31' => 'Silvestr',
            // Leden (01)
            '01-01' => 'Nový rok (Den obnovy samostatného českého státu)', '01-02' => 'Karina', '01-03' => 'Radmila',
            '01-04' => 'Diana', '01-05' => 'Dalimil', '01-06' => 'Tři králové', '01-07' => 'Vilma',
            '01-08' => 'Čestmír', '01-09' => 'Vladan', '01-10' => 'Břetislav', '01-11' => 'Bohdana',
            '01-12' => 'Pravoslav', '01-13' => 'Edita', '01-14' => 'Radovan', '01-15' => 'Alice',
            '01-16' => 'Ctirad', '01-17' => 'Drahoslav', '01-18' => 'Vladislav', '01-19' => 'Doubravka',
            '01-20' => 'Ilona', '01-21' => 'Běla', '01-22' => 'Slavomír', '01-23' => 'Zdeněk',
            '01-24' => 'Milena', '01-25' => 'Miloš', '01-26' => 'Zora', '01-27' => 'Ingrid',
            '01-28' => 'Otýlie', '01-29' => 'Zdislava', '01-30' => 'Robin', '01-31' => 'Marika',
        ];

        $key = sprintf( '%02d-%02d', $month, $day );
        return $svatky[ $key ] ?? 'Alžběta';
    }

    /**
     * Name Days (День Ангела) for calendar dates
     */
    public static function get_name_days( $month, $day ) {
        $name_days = [
            '11-19' => 'Елизавета, Александра, Нина, Клавдия, Анатолий, Арсений, Василий, Виктор, Гавриил, Герман, Константин, Лука, Матвей, Никита, Николай, Павел, Серафим',
            '11-20' => 'Александр, Алексей, Богдан, Валерий, Василий, Георгий, Григорий, Евгений, Иван, Иларион, Кирилл, Константин, Михаил, Николай, Павел, Сергей, Федор, Федот, Елизавета',
            '11-21' => 'Михаил, Гавриил, Рафаил, Марфа',
            '11-22' => 'Александр, Антон, Виктор, Дмитрий, Иван, Илья, Иосиф, Константин, Семен, Тимофей, Федор',
            '11-23' => 'Александр, Алексей, Анна, Борис, Георгий, Денис, Иван, Константин, Михаил, Николай, Ольга, Орест, Петр, Родион',
        ];

        $key = sprintf( '%02d-%02d', $month, $day );
        return $name_days[ $key ] ?? 'Елизавета, Александра, Нина, Клавдия, Анатолий, Виктор, Николай, Павел';
    }

    /**
     * Holidays in Europe, Russia, and World
     */
    public static function get_daily_holidays( $month, $day ) {
        $holidays = [
            '11-19' => [
                'europe' => [
                    'Международный мужской день (International Men\'s Day)',
                    'Всемирный день предотвращения насилия над детьми',
                    'Всемирный день философии (ЮНЕСКО)',
                    'Всемирный день туалета (ООН)',
                ],
                'russia' => [
                    'День преподавателя высшей школы',
                    'День ракетных войск и артиллерии',
                    'День работника стекольной промышленности',
                ],
            ],
            '11-20' => [
                'europe' => [
                    'Всемирный день ребенка (Всемирный день прав детей, ООН)',
                    'День индустриализации Африки',
                ],
                'russia' => [
                    'День работника транспорта',
                    'День педиатра',
                ],
            ],
            '11-21' => [
                'europe' => [
                    'Всемирный день телевидения (ООН)',
                    'Всемирный день приветствий (World Hello Day)',
                ],
                'russia' => [
                    'День работника налоговых органов РФ',
                    'День бухгалтера в России',
                ],
            ],
        ];

        $key = sprintf( '%02d-%02d', $month, $day );
        return $holidays[ $key ] ?? [
            'europe' => [ 'Международный мужской день', 'Всемирный день философии' ],
            'russia' => [ 'День преподавателя высшей школы', 'День ракетных войск и артиллерии' ],
        ];
    }

    /**
     * Render compact Holidays & Calendar banner
     */
    public static function render( $atts = [] ) {
        $now = current_time( 'timestamp' );
        $m = (int) date( 'n', $now );
        $d = (int) date( 'j', $now );

        $months_ru = [
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
        ];
        $date_str = $d . ' ' . ( $months_ru[ $m ] ?? 'ноября' );

        $svatek     = self::get_czech_svatek( $m, $d );
        $name_days  = self::get_name_days( $m, $d );
        $hols_data  = self::get_daily_holidays( $m, $d );

        ob_start();
        ?>
        <div class="vb-holidays-widget">
            <div class="vb-holidays-header">
                <span class="vb-hh-icon">📅</span>
                <span class="vb-hh-date">Сегодня: <?php echo esc_html( $date_str ); ?></span>
            </div>

            <!-- Czech Svátek -->
            <div class="vb-holidays-item vb-hi-svatek">
                <div class="vb-hi-title">
                    <span class="vb-flag"><?php echo VB_Weather::get_flag_svg('cz'); ?></span>
                    <strong>Svátek v ČR (именины):</strong>
                </div>
                <div class="vb-hi-value vb-svatek-highlight">
                    Dnes má svátek: <strong><?php echo esc_html( $svatek ); ?></strong> 👑
                </div>
            </div>

            <!-- День Ангела (Именины) -->
            <div class="vb-holidays-item vb-hi-angel">
                <div class="vb-hi-title">
                    <span>👼</span>
                    <strong>День Ангела (именины):</strong>
                </div>
                <div class="vb-hi-names">
                    <?php echo esc_html( $name_days ); ?>
                </div>
            </div>

            <!-- Праздники в Европе и мире -->
            <?php if ( ! empty( $hols_data['europe'] ) ) : ?>
                <div class="vb-holidays-item vb-hi-europe">
                    <div class="vb-hi-title">
                        <span>🌍</span>
                        <strong>В Европе и мире:</strong>
                    </div>
                    <ul class="vb-hi-list">
                        <?php foreach ( $hols_data['europe'] as $eh ) : ?>
                            <li><?php echo esc_html( $eh ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Праздники в России -->
            <?php if ( ! empty( $hols_data['russia'] ) ) : ?>
                <div class="vb-holidays-item vb-hi-russia">
                    <div class="vb-hi-title">
                        <span class="vb-flag"><?php echo VB_Weather::get_flag_svg('ru'); ?></span>
                        <strong>В России:</strong>
                    </div>
                    <ul class="vb-hi-list">
                        <?php foreach ( $hols_data['russia'] as $rh ) : ?>
                            <li><?php echo esc_html( $rh ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
