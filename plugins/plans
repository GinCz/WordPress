# Техническое задание (ТЗ): Замена оригинальных плагинов WordPress на DE-222 и RU-109

**Дата составления:** 20.09.2026  
**Назначение:** Инструкция и техзадание для чат-сессии удаления старых плагинов из WordPress Store и установки авторских микро-плагинов `(VladiMIR+AI✅)`.

---

> [!CAUTION]
> ## 🚨 КАТЕГОРИЧЕСКОЕ ОГРАНИЧЕНИЕ (ПРАВИЛО №1): САЙТЫ С WOOCOMMERCE НЕ ТРОГАТЬ!
> 
> **Строго запрещено** производить любые манипуляции, удаление плагинов или модификацию базы данных на сайтах, где установлен **WooCommerce** (всего 16 сайтов)!
> Все они **полностью исключены** из текущего плана замены.

### Список ИСКЛЮЧЕННЫХ сайтов с WooCommerce (16 шт.):

#### Сервер DE-222 (6 шт.):
- `bio-zahrada.eu` (`/var/www/tan-adrian/data/www/bio-zahrada.eu`)
- `lybawa.com` (`/var/www/gadanie-tel/data/www/lybawa.com`)
- `ru-tv.eu` (`/var/www/gincz/data/www/ru-tv.eu`)
- `svetaform.eu` (`/var/www/spa/data/www/svetaform.eu`)
- `timan-kuchyne.cz` (`/var/www/nata_popkova/data/www/timan-kuchyne.cz`)
- `wowflow.cz` (`/var/www/wowflow/data/www/wowflow.cz`)

#### Сервер RU-109 (10 шт.):
- `4ton-96.ru` (`/var/www/foton/data/www/4ton-96.ru`)
- `nail-space-ekb.ru` (`/var/www/valeriia/data/www/nail-space-ekb.ru`)
- `shapkioptom.ru` (`/var/www/palantins/data/www/shapkioptom.ru`)
- `stanok-ural.ru` (`/var/www/stanok/data/www/stanok-ural.ru`)
- `stassinhouse.ru` (`/var/www/anastasia_bul/data/www/stassinhouse.ru`)
- `stuba-dom.ru` (`/var/www/vobs/data/www/stuba-dom.ru`)
- `tatra-ural.ru` (`/var/www/tatra/data/www/tatra-ural.ru`)
- `tri-sure.ru` (`/var/www/kirill-tri-sure/data/www/tri-sure.ru`)
- `ugfp.ru` (`/var/www/ugfp/data/www/ugfp.ru`)
- `ver7.ru` (`/var/www/foton/data/www/ver7.ru`)

---

## 🎯 ЦЕЛЕВЫЕ САЙТЫ ДЛЯ ОБРАБОТКИ (46 шт. БЕЗ WOOCOMMERCE)

Работы производятся **исключительно** на следующих **46 сайтах**:

### Сервер DE-222 (34 сайтов):
1. `abl-metal.com` (База данных: `abl_metal`)
2. `alejandrofashion.cz` (База данных: `alejandrofas`)
3. `autoservis-praha.eu` (База данных: `arslan_wp`)
4. `balance-b2b.eu` (База данных: `balance_b2b_`)
5. `car-bus-autoservice.cz` (База данных: `car_bus_auto`)
6. `car-chip.eu` (База данных: `car_chip_eu`)
7. `czechtoday.eu` (База данных: `czechtoday_new`)
8. `detailing-alex.eu` (База данных: `detailing_al`)
9. `diamond-odtah.cz` (База данных: `diamond_odta`)
10. `doska-cz.ru` (База данных: `doska-cz`)
11. `doska-de.ru` (База данных: `doska_de`)
12. `doska-esp.ru` (База данных: `doska-esp`)
13. `doska-fr.ru` (База данных: `doska-fr`)
14. `doska-gr.ru` (База данных: `doska-gr`)
15. `doska-hun.ru` (База данных: `doska-hun`)
16. `doska-isl.ru` (База данных: `doska-isl`)
17. `doska-it.ru` (База данных: `doska-it`)
18. `doska-mld.ru` (База данных: `doska-mld`)
19. `doska-pl.ru` (База данных: `doska-pl`)
20. `doska-ua.ru` (База данных: `doska-ua`)
21. `eco-seo.cz` (База данных: `eco_seo_cz_db`)
22. `ekaterinburg-sro.eu` (База данных: `ekaterinburg_sro`)
23. `eurasia-translog.cz` (База данных: `eurasia_tran`)
24. `hulk-jobs.cz` (База данных: `hulk_jobs_cz`)
25. `kadernictvi-salon.eu` (База данных: `kadernictvi_wp`)
26. `kadernik-olga.eu` (База данных: `kadernik_olga`)
27. `kk-med.eu` (База данных: `kk_med_eu`)
28. `megan-consult.cz` (База данных: `megan_consult`)
29. `praha-autoservis.eu` (База данных: `praha_autos_eu`)
30. `rail-east.uk` (База данных: `rail-east-uk`)
31. `stm-services-group.cz` (База данных: `stm_serv`)
32. `stopservis-vestec.cz` (База данных: `stopservis_v`)
33. `tstwist.cz` (База данных: `tstwist__wp`)
34. `vymena-motoroveho-oleje.cz` (База данных: `vymena_motor`)

### Сервер RU-109 (12 сайтов):
1. `andrey-maiorov.ru` (База данных: `andrey_maiorov`)
2. `comfort-eng.ru` (База данных: `comfort_eng_`)
3. `geodesia-ekb.ru` (База данных: `geodesia-ekb`)
4. `lvo-endo.ru` (База данных: `lvo_endo_ru`)
5. `mtek-expert.ru` (База данных: `mtek_wp`)
6. `natal-karta.ru` (База данных: `natal_karta_`)
7. `ne-son.ru` (База данных: `ne_son_ru`)
8. `news-port.ru` (База данных: `news_port`)
9. `novorr-art.ru` (База данных: `novorr_art_r`)
10. `prodvig-saita.ru` (База данных: `prodvig_saita_db`)
11. `stomatolog-belchikov.ru` (База данных: `stomat-bel-wp`)
12. `study-italy.eu` (База данных: `study_italy_`)

---

## 📋 СПИСОК ОРИГИНАЛЬНЫХ ПЛАГИНОВ ДЛЯ УДАЛЕНИЯ

Ниже приведен список оригинальных плагинов из магазина WordPress, которые подлежат удалению из `wp-content/plugins/`:

| № | Название плагина (WordPress Store) | Slug папки оригинала | Наш авторский плагин-заменитель | Slug нового плагина |
|---|------------------------------------|----------------------|----------------------------------|---------------------|
| 1 | **WP Test Email** | `wp-test-email` | `WP Test Email Micro (VladiMIR+AI✅)` | `wp-test-email-micro` |
| 2 | **Head Meta Data** / Clean Head | `head-meta-data` | `Clean Head Meta & Anti-Fingerprint (VladiMIR+AI✅)` | `clean-head-meta` |
| 3 | **SEOPress / WP SEO** | `wp-seopress` | `WP SEO Micro (VladiMIR+AI✅)` | `wp-seo-micro` |
| 4 | **Classic Editor** | `classic-editor` | `Classic Editor - TinyMCE (VladiMIR+AI✅)` | `classic-editor-tinymce` |
| 5 | **Simple Custom Post Order** | `simple-custom-post-order` | `WP Simple Post & Category Order (VladiMIR+AI✅)` | `wp-simple-post-order` |
| 6 | **Cyr2Lat / Cyrillic to Latin** | `cyr3lat`, `cyr2lat`, `cyrillic-to-latin` | `Cyrillic & European to Latin SEO Transliteration (VladiMIR+AI✅)` | `translit-cyr-lat` |
| 7 | **Allow HTML in Category Descriptions** | `allow-html-in-category-descriptions` | `Allow HTML in Category & Taxonomy Descriptions (VladiMIR+AI✅)` | `wp-allow-html-cats` |
| 8 | **Online Users Counter** | `online-users-counter` | `Live Active Users & Visitors Counter (VladiMIR+AI✅)` | `wp-online-counter` |
| 9 | **Disable Auto-Update Emails** | `disable-auto-update-email-notifications` | `Disable Auto-Update Notification E-mails (VladiMIR+AI)` | `disable-update-emails` |
| 10| **404 to Homepage / Redirects** | `404-to-homepage` | `404-410-301 (SEO 404/410 + Auto-Redirect) (VladiMIR+AI✅)` | `404-410-301` |

---

## 🗄️ ТРЕБОВАНИЯ К ОЧИСТКЕ БАЗЫ ДАННЫХ (MariaDB / MySQL)

1. **Резервная копия**: Перед выполнением операций сделать `mysqldump` всех 46 баз данных.
2. **Удаление лишних таблиц**:
   - `DROP TABLE IF EXISTS wp_seopress_content_analysis;` (где ранее устанавливался SEOPress).
   - `DROP TABLE IF EXISTS wp_test_email_logs;` (где устанавливался WP Test Email).
3. **Очистка записей в `wp_options`**:
   - `DELETE FROM wp_options WHERE option_name LIKE 'seopress_%';`
   - `DELETE FROM wp_options WHERE option_name LIKE 'wp_test_email_%';`
   - `DELETE FROM wp_options WHERE option_name = 'scporder_objects';`

---

## 🚀 АЛГОРИТМ ВНЕДРЕНИЯ НАШИХ ПЛАГИНОВ (VladiMIR+AI✅)

1.Скопировать дистрибутивы авторских микро-плагинов из центрального репозитория DE-222 в целевые папки сайтов (`/var/www/.../wp-content/plugins/`).
2. Активировать плагины в `active_plugins` через WP-CLI (`wp plugin activate <slug> --allow-root`) или прямо в MySQL.
3. Проверить работоспособность сайтов и отклик HTTP 200 OK.
