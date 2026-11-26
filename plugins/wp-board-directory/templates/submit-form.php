<?php
/**
 * Modern Turnkey Frontend Submission Form (Ads & Companies)
 *
 * @package WP_Board_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user = wp_get_current_user();
$uid  = get_current_user_id();

$edit_id   = absint( $_GET['listing_id'] ?? 0 );
$is_edit   = false;
$edit_post = null;
if ( $edit_id > 0 ) {
    $edit_post = get_post( $edit_id );
    if ( $edit_post && ( (int) $edit_post->post_author === (int) $uid || current_user_can( 'edit_post', $edit_id ) ) ) {
        $is_edit = true;
    }
}

$default_phone   = $uid ? ( get_user_meta( $uid, 'phone', true ) ?: get_user_meta( $uid, 'acadp_phone', true ) ) : '';
$default_city    = $uid ? ( get_user_meta( $uid, 'address', true ) ?: get_user_meta( $uid, 'user_city', true ) ) : '';
$default_email   = $uid ? $user->user_email : '';
$default_company = $uid ? ( get_user_meta( $uid, 'company_name', true ) ?: get_user_meta( $uid, 'user_company', true ) ) : '';

if ( $is_edit ) {
    $val_title    = $edit_post->post_title;
    $val_content  = $edit_post->post_content;
    $val_type     = get_post_meta( $edit_id, '_vb_type', true ) ?: 'classified';
    $val_price    = get_post_meta( $edit_id, '_vb_price', true );
    $val_phone    = get_post_meta( $edit_id, '_vb_phone', true ) ?: $default_phone;
    $val_wa       = get_post_meta( $edit_id, '_vb_whatsapp', true );
    $val_tg       = get_post_meta( $edit_id, '_vb_telegram', true );
    $val_email    = get_post_meta( $edit_id, '_vb_email', true ) ?: $default_email;
    $val_address  = get_post_meta( $edit_id, '_vb_address', true ) ?: $default_city;

    $item_cats    = wp_get_post_terms( $edit_id, 'vb_category', [ 'fields' => 'ids' ] );
    $val_cat_id   = ( ! empty( $item_cats ) && ! is_wp_error( $item_cats ) ) ? $item_cats[0] : 0;

    $item_locs    = wp_get_post_terms( $edit_id, 'vb_location', [ 'fields' => 'slugs' ] );
    $val_loc_slug = ( ! empty( $item_locs ) && ! is_wp_error( $item_locs ) ) ? $item_locs[0] : '';
} else {
    $req_type     = sanitize_key( $_GET['type'] ?? '' );
    $val_title    = '';
    $val_content  = '';
    $val_type     = in_array( $req_type, [ 'company', 'biz', 'firm' ], true ) ? 'company' : 'classified';
    $val_price    = '';
    $val_phone    = $default_phone;
    $val_wa       = '';
    $val_tg       = '';
    $val_email    = $default_email;
    $val_address  = $default_city;
    $val_cat_id   = 0;
    $val_loc_slug = $default_city;
}

// Get all parent categories and their children
$parents = get_terms( [
    'taxonomy'   => 'vb_category',
    'hide_empty' => false,
    'parent'     => 0,
    'orderby'    => 'name',
    'order'      => 'ASC',
] );

// Get Czech locations
$locations = get_terms( [
    'taxonomy'   => 'vb_location',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
] );

$action_url = esc_url( admin_url( 'admin-post.php' ) );
?>

<?php if ( ! is_user_logged_in() ) : ?>
<div class="vb-submit-wrapper">
    <div class="vb-auth-required-box" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 40px 24px; text-align: center; max-width: 640px; margin: 30px auto; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
        <div style="font-size: 52px; margin-bottom: 12px;">🔒</div>
        <h2 style="font-size: 23px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Подача объявлений доступна только после входа</h2>
        <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 25px;">
            Чтобы защитить пользователей портала от спам-ботов и мошенников, публикация объявлений и добавление компаний открыты только для авторизованных пользователей.
        </p>

        <?php if ( shortcode_exists( 'miniorange_social_login' ) ) : ?>
            <div class="vb-social-login-block" style="background: #f8fafc; border-radius: 10px; padding: 20px 15px; margin-bottom: 22px;">
                <p style="font-weight: 600; color: #334155; margin-bottom: 12px; font-size: 15px;">Быстрый вход через соцсети в 1 клик:</p>
                <?php echo do_shortcode( '[miniorange_social_login]' ); ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 18px; font-size: 14px; color: #64748b;">
            Уже есть аккаунт? <a href="<?php echo esc_url( wp_login_url( home_url( '/new/' ) ) ); ?>" style="color: #2563eb; font-weight: 600; text-decoration: underline;">Войти по логину</a> &nbsp;•&nbsp; <a href="<?php echo esc_url( wp_registration_url() ); ?>" style="color: #2563eb; font-weight: 600; text-decoration: underline;">Регистрация</a>
        </div>
    </div>
</div>
<?php return; endif; ?>

<div class="vb-submit-wrapper">
    <div class="vb-submit-header">
        <h2 class="vb-submit-title" id="vb-page-submit-title"><?php echo $is_edit ? '✏️ Редактирование ' . ( $val_type === 'company' ? 'компании' : 'объявления' ) : ( $val_type === 'company' ? '🏢 Добавить компанию в каталог' : '📢 Подать объявление на ' . esc_html( get_bloginfo( 'name' ) ) ); ?></h2>
        <p class="vb-submit-subtitle" id="vb-page-submit-sub"><?php echo $is_edit ? 'Внесите необходимые изменения и сохраните публикацию.' : ( $val_type === 'company' ? 'Заполните профиль вашей организации, услуги и контакты для каталога фирм.' : 'Быстрая публикация без лишней бюрократии. Фотографии автоматически сжимаются для максимальной скорости загрузки на телефонах.' ); ?></p>
    </div>

    <?php if ( ! empty( $_GET['error'] ) ) : ?>
        <div class="vb-alert-danger">
            ⚠️ Ошибка при сохранении: <?php echo esc_html( sanitize_text_field( $_GET['error'] ) ); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo $action_url; ?>" class="vb-submit-form" id="vb-submit-form" enctype="multipart/form-data">
        <?php wp_nonce_field( 'vb_submit_listing_action', 'vb_submit_nonce' ); ?>
        <input type="hidden" name="action" value="vb_handle_submit_listing">
        <!-- Anti-Spam Honeypot Trap (invisible to humans) -->
        <div style="position:absolute; left:-9999px; top:-9999px; opacity:0; pointer-events:none;" aria-hidden="true">
            <input type="text" name="vb_honey_check" value="" tabindex="-1" autocomplete="off">
        </div>
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="listing_id" value="<?php echo esc_attr( $edit_id ); ?>">
        <?php endif; ?>

        <!-- Type Selector -->
        <div class="vb-form-section">
            <h3 class="vb-form-sec-title">1. Тип публикации</h3>
            <div class="vb-type-toggle-group">
                <label class="vb-type-radio-card <?php echo $val_type === 'classified' ? 'active' : ''; ?>">
                    <input type="radio" name="listing_type" value="classified" <?php checked( $val_type, 'classified' ); ?> onchange="vbUpdateTypeSelection('classified');">
                    <span class="vb-radio-icon">📢</span>
                    <span class="vb-radio-text">
                        <strong>Частное объявление</strong>
                        <small>Продажа, покупка, аренда, услуги, работа</small>
                    </span>
                </label>
                <label class="vb-type-radio-card <?php echo $val_type === 'company' ? 'active' : ''; ?>">
                    <input type="radio" name="listing_type" value="company" <?php checked( $val_type, 'company' ); ?> onchange="vbUpdateTypeSelection('company');">
                    <span class="vb-radio-icon">🏢</span>
                    <span class="vb-radio-text">
                        <strong>Компания / Каталог фирм</strong>
                        <small>Карточка бизнеса, s.r.o., OSVČ, сервис</small>
                    </span>
                </label>
            </div>
        </div>

        <script>
        function vbUpdateTypeSelection(type) {
            document.querySelectorAll('.vb-type-radio-card').forEach(function(c){ c.classList.remove('active'); });
            var checkedRadio = document.querySelector('.vb-type-radio-card input[value="' + type + '"]');
            if (checkedRadio) {
                checkedRadio.checked = true;
                checkedRadio.closest('.vb-type-radio-card').classList.add('active');
            }
            var titleEl = document.getElementById('vb-page-submit-title');
            var subEl = document.getElementById('vb-page-submit-sub');
            var lblEl = document.getElementById('vb-title-label');
            var inpEl = document.getElementById('vb-title-input');
            if (type === 'company') {
                if (titleEl) titleEl.innerText = '🏢 Добавить компанию в каталог';
                if (subEl) subEl.innerText = 'Заполните профиль вашей организации, услуги и контакты для каталога фирм.';
                if (lblEl) lblEl.innerHTML = 'Название компании / фирмы <span class="vb-req">*</span>';
                if (inpEl) inpEl.placeholder = 'Например: ООО Автосервис Норд или Салон красоты Элит';
            } else {
                if (titleEl) titleEl.innerText = '📢 Подать объявление на <?php echo esc_js( get_bloginfo( 'name' ) ); ?>';
                if (subEl) subEl.innerText = 'Быстрая публикация без лишней бюрократии. Фотографии автоматически сжимаются для максимальной скорости загрузки на телефонах.';
                if (lblEl) lblEl.innerHTML = 'Заголовок объявления <span class="vb-req">*</span>';
                if (inpEl) inpEl.placeholder = 'Например: Аренда 2+кк в центре или Ремонт техники';
            }
        }
        </script>

        <!-- Basic Info -->
        <div class="vb-form-section">
            <h3 class="vb-form-sec-title">2. Основная информация</h3>
            <div class="vb-form-grid">
                <div class="vb-form-group vb-form-full">
                    <label class="vb-label" id="vb-title-label"><?php echo $val_type === 'company' ? 'Название компании / фирмы' : 'Заголовок объявления'; ?> <span class="vb-req">*</span></label>
                    <input type="text" name="title" id="vb-title-input" value="<?php echo esc_attr( $val_title ); ?>" required maxlength="100" placeholder="<?php echo $val_type === 'company' ? 'Например: ООО Автосервис Норд или Салон красоты Элит' : 'Например: Аренда 2+кк в центре или Ремонт техники'; ?>" class="vb-form-input">
                    <div style="font-size: 11px; color: #64748b; text-align: right; margin-top: 3px; font-weight: 500;">
                        <span id="vb-title-char-count"><?php echo mb_strlen( $val_title, 'UTF-8' ); ?></span> / 100 символов (макс. 100)
                    </div>
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Рубрика / Категория <span class="vb-req">*</span></label>
                    <select name="category_id" required class="vb-form-select">
                        <option value="">-- Выберите категорию --</option>
                        <?php if ( ! empty( $parents ) && ! is_wp_error( $parents ) ) : ?>
                            <?php foreach ( $parents as $parent ) : 
                                $clean_p_name = trim( preg_replace( '/^[◉\s\-\•\·]+/u', '', $parent->name ) );
                                $children = get_terms( [
                                    'taxonomy'   => 'vb_category',
                                    'hide_empty' => false,
                                    'parent'     => $parent->term_id,
                                    'orderby'    => 'name',
                                    'order'      => 'ASC',
                                ] );
                            ?>
                                <optgroup label="◉ <?php echo esc_attr( $clean_p_name ); ?>">
                                    <option value="<?php echo esc_attr( $parent->term_id ); ?>" <?php selected( $val_cat_id, $parent->term_id ); ?>>Все в: <?php echo esc_html( $clean_p_name ); ?></option>
                                    <?php if ( ! empty( $children ) && ! is_wp_error( $children ) ) : ?>
                                        <?php foreach ( $children as $child ) : ?>
                                            <option value="<?php echo esc_attr( $child->term_id ); ?>" <?php selected( $val_cat_id, $child->term_id ); ?>>&nbsp;&nbsp;&bull; <?php echo esc_html( $child->name ); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Город / Регион <span class="vb-req">*</span></label>
                    <select name="location_id" id="vb-location-select" class="vb-form-select">
                        <option value="">-- Выберите город / регион --</option>
                        <?php
                        if ( class_exists( 'VB_CPT' ) && method_exists( 'VB_CPT', 'render_location_options' ) ) {
                            VB_CPT::render_location_options( $val_loc_slug );
                        }
                        ?>
                    </select>

                    <?php
                    $quick_pills = [];
                    $q_host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( $_SERVER['HTTP_HOST'] ) : '';
                    if ( strpos( $q_host, '-de' ) !== false || strpos( $q_host, '.de' ) !== false ) {
                        $quick_pills = [
                            [ 'slug' => 'berlin', 'name' => '📍 Берлин' ],
                            [ 'slug' => 'germany', 'name' => 'Вся Германия' ],
                            [ 'slug' => 'internet', 'name' => '🌐 OnLine' ],
                            [ 'slug' => 'evropa', 'name' => 'Европа' ],
                            [ 'slug' => 'russia', 'name' => 'Россия' ],
                            [ 'slug' => 'ukraine', 'name' => 'Украина' ],
                        ];
                    } elseif ( strpos( $q_host, '.cz' ) !== false || strpos( $q_host, '-cz' ) !== false ) {
                        $quick_pills = [
                            [ 'slug' => 'praha', 'name' => '📍 Прага' ],
                            [ 'slug' => 'cheh', 'name' => 'Вся Чехия' ],
                            [ 'slug' => 'internet', 'name' => '🌐 OnLine' ],
                            [ 'slug' => 'evropa', 'name' => 'Европа' ],
                            [ 'slug' => 'russia', 'name' => 'Россия' ],
                            [ 'slug' => 'ukraine', 'name' => 'Украина' ],
                        ];
                    } elseif ( strpos( $q_host, '-at' ) !== false || strpos( $q_host, '.at' ) !== false ) {
                        $quick_pills = [
                            [ 'slug' => 'wien', 'name' => '📍 Вена' ],
                            [ 'slug' => 'austria', 'name' => 'Вся Австрия' ],
                            [ 'slug' => 'internet', 'name' => '🌐 OnLine' ],
                            [ 'slug' => 'evropa', 'name' => 'Европа' ],
                            [ 'slug' => 'russia', 'name' => 'Россия' ],
                            [ 'slug' => 'ukraine', 'name' => 'Украина' ],
                        ];
                    } else {
                        $quick_pills = [
                            [ 'slug' => 'internet', 'name' => '🌐 OnLine' ],
                            [ 'slug' => 'evropa', 'name' => 'Европа' ],
                            [ 'slug' => 'russia', 'name' => 'Россия' ],
                            [ 'slug' => 'ukraine', 'name' => 'Украина' ],
                        ];
                    }
                    ?>
                    <div class="vb-quick-locations" id="vb-quick-locations">
                        <span class="vb-quick-loc-label">Быстрый выбор:</span>
                        <?php foreach ( $quick_pills as $qp ) : ?>
                            <button type="button" class="vb-loc-pill" data-slug="<?php echo esc_attr( $qp['slug'] ); ?>"><?php echo esc_html( $qp['name'] ); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Цена (<?php echo esc_html( VB_CPT::get_currency_symbol() ); ?>)</label>
                    <input type="number" name="price" value="<?php echo esc_attr( $val_price ); ?>" placeholder="Например: 1500 (0 - договорная)" class="vb-form-input">
                    <small class="vb-hint">Оставьте пустым или 0, если цена договорная</small>
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Точный адрес или район (необязательно)</label>
                    <input type="text" name="address" value="<?php echo esc_attr( $val_address ); ?>" placeholder="Район, улица или ориентир..." class="vb-form-input">
                </div>
            </div>
        </div>

        <!-- Photos Upload Area (Up to 15 Photos with Client-side WebP Compression) -->
        <div class="vb-form-section">
            <h3 class="vb-form-sec-title">3. Фотографии (до 15 фото)</h3>
            <p class="vb-hint" style="margin-bottom:12px;">Снимки автоматически оптимизируются в быстрый формат WebP прямо в браузере. Первое фото будет главной обложкой.</p>

            <div class="vb-uploader-box" id="vb-uploader-dropzone" onclick="document.getElementById('vb-file-input').click();">
                <div class="vb-uploader-icon">📷</div>
                <div class="vb-uploader-label">Нажмите или перетащите сюда фотографии</div>
                <div class="vb-uploader-sub">Поддерживаются JPG, PNG, WEBP &bull; До 15 фото &bull; Быстрое сжатие до 1024px</div>
                <input type="file" id="vb-file-input" accept="image/*" multiple style="display:none;" onchange="vbHandleFiles(this.files);">
            </div>

            <!-- Preview Grid -->
            <div class="vb-photos-preview-grid" id="vb-preview-grid"></div>
            <!-- Container for base64 compressed strings -->
            <div id="vb-compressed-container"></div>
        </div>

        <!-- Description -->
        <div class="vb-form-section">
            <h3 class="vb-form-sec-title">4. Подробное описание</h3>
            <div class="vb-form-group vb-form-full">
                <textarea name="description" rows="6" required placeholder="Опишите ваше предложение, характеристики, условия сделки или подробности об услуге..." class="vb-form-textarea"><?php echo esc_textarea( $val_content ); ?></textarea>
            </div>
        </div>

        <!-- Contacts -->
        <div class="vb-form-section">
            <h3 class="vb-form-sec-title">5. Контакты продавца</h3>
            <div class="vb-form-grid">
                <div class="vb-form-group">
                    <label class="vb-label">Контактный телефон <span class="vb-req">*</span></label>
                    <input type="text" name="phone" value="<?php echo esc_attr( $val_phone ); ?>" required placeholder="+420..." class="vb-form-input">
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">WhatsApp (номер или ссылка)</label>
                    <input type="text" name="whatsapp" value="<?php echo esc_attr( $val_wa ); ?>" placeholder="+420..." class="vb-form-input">
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Telegram (@username)</label>
                    <input type="text" name="telegram" value="<?php echo esc_attr( $val_tg ); ?>" placeholder="@username" class="vb-form-input">
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Email для связи</label>
                    <input type="email" name="email" value="<?php echo esc_attr( $val_email ); ?>" placeholder="vash-email@gmail.com" class="vb-form-input">
                </div>
            </div>
        </div>

        <!-- Cloudflare Turnstile & Submit -->
        <div class="vb-form-submit-section">
            <?php if ( function_exists( 'cfturnstile_field_show' ) ) : ?>
                <div class="vb-turnstile-wrap">
                    <?php cfturnstile_field_show( '#vb-submit-btn', '', 'new-listing', '-vb-listing' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $_GET['error'] ) ) : ?>
                <div class="vb-alert-danger" style="margin-bottom: 18px; text-align: center; font-weight: 600;">
                    ⚠️ Ошибка: <?php echo esc_html( sanitize_text_field( $_GET['error'] ) ); ?>
                </div>
            <?php endif; ?>

            <div class="vb-submit-action-row">
                <button type="submit" class="vb-btn-submit-listing" id="vb-submit-btn">
                    <?php echo $is_edit ? '💾 Сохранить изменения' : '🚀 Опубликовать объявление'; ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
var vbUploadedBlobs = [];

function vbHandleFiles(files) {
    if (!files || !files.length) return;
    var previewGrid = document.getElementById('vb-preview-grid');
    var container   = document.getElementById('vb-compressed-container');

    for (var i = 0; i < files.length; i++) {
        if (vbUploadedBlobs.length >= 15) {
            alert('Максимально можно загрузить 15 фотографий.');
            break;
        }

        var file = files[i];
        if (!file.type.match('image.*')) continue;

        (function(currentFile) {
            window.vbCompressImageToWebP(currentFile, 1024, 0.8, function(blob) {
                vbUploadedBlobs.push(blob);
                var idx = vbUploadedBlobs.length - 1;

                var reader = new FileReader();
                reader.onload = function(e) {
                    var card = document.createElement('div');
                    card.className = 'vb-preview-card';
                    card.id = 'vb-prev-' + idx;
                    card.innerHTML = '<img src="' + e.target.result + '" class="vb-prev-thumb">' +
                                     (idx === 0 ? '<span class="vb-badge-cover">Обложка</span>' : '') +
                                     '<button type="button" class="vb-prev-remove" onclick="vbRemovePhoto(' + idx + ');">&times;</button>';
                    previewGrid.appendChild(card);

                    // Add hidden input with base64 for reliable submission
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'vb_photos_webp[]';
                    input.id = 'vb-input-' + idx;
                    input.value = e.target.result;
                    container.appendChild(input);
                };
                reader.readAsDataURL(blob);
            });
        })(file);
    }
}

function vbRemovePhoto(idx) {
    var card = document.getElementById('vb-prev-' + idx);
    if (card) card.remove();
    var inp = document.getElementById('vb-input-' + idx);
    if (inp) inp.remove();
}

document.addEventListener('DOMContentLoaded', function() {
    var tInp = document.getElementById('vb-title-input');
    var tCnt = document.getElementById('vb-title-char-count');
    if (tInp && tCnt) {
        function updateTitleCount() {
            var len = tInp.value.length;
            tCnt.textContent = len;
            if (len >= 95) {
                tCnt.style.color = '#ef4444';
            } else if (len >= 80) {
                tCnt.style.color = '#f59e0b';
            } else {
                tCnt.style.color = '#64748b';
            }
        }
        tInp.addEventListener('input', updateTitleCount);
        updateTitleCount();
    }

    // Quick Location Pills Interactive Synchronization
    var locSelect = document.getElementById('vb-location-select');
    var locPills  = document.querySelectorAll('.vb-loc-pill');
    if (locSelect && locPills.length) {
        function syncActiveLocPill() {
            var curVal = (locSelect.value || '').toLowerCase();
            var curText = (locSelect.options[locSelect.selectedIndex] ? locSelect.options[locSelect.selectedIndex].text : '').toLowerCase();
            locPills.forEach(function(pill) {
                var pSlug = (pill.getAttribute('data-slug') || '').toLowerCase();
                if (pSlug && (curVal.indexOf(pSlug) !== -1 || curText.indexOf(pSlug) !== -1)) {
                    pill.classList.add('active');
                } else {
                    pill.classList.remove('active');
                }
            });
        }

        locPills.forEach(function(pill) {
            pill.addEventListener('click', function(e) {
                e.preventDefault();
                var pSlug = (this.getAttribute('data-slug') || '').toLowerCase();
                for (var i = 0; i < locSelect.options.length; i++) {
                    var oVal  = locSelect.options[i].value.toLowerCase();
                    var oText = locSelect.options[i].text.toLowerCase();
                    if (oVal === pSlug || (pSlug && oVal.indexOf(pSlug) !== -1) || (pSlug && oText.indexOf(pSlug) !== -1)) {
                        locSelect.selectedIndex = i;
                        locSelect.dispatchEvent(new Event('change'));
                        break;
                    }
                }
                syncActiveLocPill();
            });
        });

        locSelect.addEventListener('change', syncActiveLocPill);
        syncActiveLocPill();
    }
});
</script>

<style>
.vb-quick-locations {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
}
.vb-quick-loc-label {
    font-size: 11.5px;
    color: #64748b;
    font-weight: 600;
}
.vb-loc-pill {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 3px 10px;
    font-size: 12px;
    color: #334155;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
    line-height: 1.4;
}
.vb-loc-pill:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
    color: #0f172a;
    transform: translateY(-1px);
}
.vb-loc-pill.active {
    background: #7c3aed;
    border-color: #7c3aed;
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(124, 58, 237, 0.35);
}
</style>
