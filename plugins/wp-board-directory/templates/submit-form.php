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

$default_phone   = $uid ? ( get_user_meta( $uid, 'phone', true ) ?: get_user_meta( $uid, 'acadp_phone', true ) ) : '';
$default_city    = $uid ? ( get_user_meta( $uid, 'address', true ) ?: get_user_meta( $uid, 'user_city', true ) ) : '';
$default_email   = $uid ? $user->user_email : '';
$default_company = $uid ? ( get_user_meta( $uid, 'company_name', true ) ?: get_user_meta( $uid, 'user_company', true ) ) : '';

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

<div class="vb-submit-wrapper">
    <div class="vb-submit-header">
        <h2 class="vb-submit-title">📢 Подать объявление в Чехии</h2>
        <p class="vb-submit-subtitle">Быстрая публикация без лишней бюрократии. Фотографии автоматически сжимаются для максимальной скорости загрузки на телефонах.</p>
    </div>

    <?php if ( ! empty( $_GET['error'] ) ) : ?>
        <div class="vb-alert-danger">
            ⚠️ Ошибка при сохранении: <?php echo esc_html( sanitize_text_field( $_GET['error'] ) ); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo $action_url; ?>" class="vb-submit-form" id="vb-submit-form" enctype="multipart/form-data">
        <?php wp_nonce_field( 'vb_submit_listing_action', 'vb_submit_nonce' ); ?>
        <input type="hidden" name="action" value="vb_handle_submit_listing">

        <!-- Type Selector -->
        <div class="vb-form-section">
            <h3 class="vb-form-sec-title">1. Тип публикации</h3>
            <div class="vb-type-toggle-group">
                <label class="vb-type-radio-card active">
                    <input type="radio" name="listing_type" value="classified" checked onclick="document.querySelectorAll('.vb-type-radio-card').forEach(function(c){c.classList.remove('active');}); this.closest('.vb-type-radio-card').classList.add('active');">
                    <span class="vb-radio-icon">📢</span>
                    <span class="vb-radio-text">
                        <strong>Частное объявление</strong>
                        <small>Продажа, покупка, аренда, услуги, работа</small>
                    </span>
                </label>
                <label class="vb-type-radio-card">
                    <input type="radio" name="listing_type" value="company" onclick="document.querySelectorAll('.vb-type-radio-card').forEach(function(c){c.classList.remove('active');}); this.closest('.vb-type-radio-card').classList.add('active');">
                    <span class="vb-radio-icon">🏢</span>
                    <span class="vb-radio-text">
                        <strong>Компания / Каталог фирм</strong>
                        <small>Карточка бизнеса, s.r.o., OSVČ, сервис</small>
                    </span>
                </label>
            </div>
        </div>

        <!-- Basic Info -->
        <div class="vb-form-section">
            <h3 class="vb-form-sec-title">2. Основная информация</h3>
            <div class="vb-form-grid">
                <div class="vb-form-group vb-form-full">
                    <label class="vb-label">Заголовок объявления <span class="vb-req">*</span></label>
                    <input type="text" name="title" required placeholder="Например: Аренда 2+кк Прага 3 Жижков или Ремонт ноутбуков" class="vb-form-input">
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
                                    <option value="<?php echo esc_attr( $parent->term_id ); ?>">Все в: <?php echo esc_html( $clean_p_name ); ?></option>
                                    <?php if ( ! empty( $children ) && ! is_wp_error( $children ) ) : ?>
                                        <?php foreach ( $children as $child ) : ?>
                                            <option value="<?php echo esc_attr( $child->term_id ); ?>">&nbsp;&nbsp;&bull; <?php echo esc_html( $child->name ); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Город / Регион в Чехии <span class="vb-req">*</span></label>
                    <select name="location_id" class="vb-form-select">
                        <option value="">-- Выберите город / край --</option>
                        <option value="praha" selected>📍 Прага (Praha)</option>
                        <option value="brno">📍 Брно / Южноморавский край</option>
                        <option value="plzen">📍 Пльзень / Пльзеньский край</option>
                        <option value="stredocesky">📍 Среднечешский край</option>
                        <option value="ostrava">📍 Острава / Моравско-Силезский край</option>
                        <option value="karlovy-vary">📍 Карловы Вары</option>
                        <option value="liberec">📍 Либерец</option>
                        <option value="ceske-budejovice">📍 Ческе-Будеёвице</option>
                        <option value="hradec-kralove">📍 Градец-Кралове</option>
                        <option value="pardubice">📍 Пардубице</option>
                        <option value="olomouc">📍 Оломоуц</option>
                        <option value="zlin">📍 Злин</option>
                        <option value="usti">📍 Усти-над-Лабем</option>
                        <option value="all-cz">🇨🇿 Вся Чехия / Онлайн</option>
                    </select>
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Цена (Kč)</label>
                    <input type="number" name="price" placeholder="Например: 1500 (0 - договорная)" class="vb-form-input">
                    <small class="vb-hint">Оставьте пустым или 0, если цена договорная</small>
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Точный адрес или район (необязательно)</label>
                    <input type="text" name="address" value="<?php echo esc_attr( $default_city ); ?>" placeholder="Прага 4, Панкрац..." class="vb-form-input">
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
                <textarea name="description" rows="6" required placeholder="Опишите ваше предложение, характеристики, условия сделки или подробности об услуге..." class="vb-form-textarea"></textarea>
            </div>
        </div>

        <!-- Contacts -->
        <div class="vb-form-section">
            <h3 class="vb-form-sec-title">5. Контакты продавца</h3>
            <div class="vb-form-grid">
                <div class="vb-form-group">
                    <label class="vb-label">Контактный телефон <span class="vb-req">*</span></label>
                    <input type="text" name="phone" value="<?php echo esc_attr( $default_phone ); ?>" required placeholder="+420..." class="vb-form-input">
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">WhatsApp (номер или ссылка)</label>
                    <input type="text" name="whatsapp" placeholder="+420..." class="vb-form-input">
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Telegram (@username)</label>
                    <input type="text" name="telegram" placeholder="@username" class="vb-form-input">
                </div>

                <div class="vb-form-group">
                    <label class="vb-label">Email для связи</label>
                    <input type="email" name="email" value="<?php echo esc_attr( $default_email ); ?>" placeholder="vash-email@gmail.com" class="vb-form-input">
                </div>
            </div>
        </div>

        <!-- Cloudflare Turnstile & Submit -->
        <div class="vb-form-submit-section">
            <?php if ( function_exists( 'cfturnstile_field_show' ) ) : ?>
                <div class="vb-turnstile-wrap">
                    <?php cfturnstile_field_show(); ?>
                </div>
            <?php endif; ?>

            <div class="vb-submit-action-row">
                <button type="submit" class="vb-btn-submit-listing" id="vb-submit-btn">
                    🚀 Опубликовать объявление
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
</script>
