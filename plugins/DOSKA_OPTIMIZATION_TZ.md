# Техническое задание (ТЗ): Комплексная оптимизация сети досок объявлений doska-*.ru (Server 222)

**Дата составления:** 17.11.2026  
**Автор:** Vladimir Bulantsev (`@GinCz`) & AI  
**Целевой сервер:** DE-222 (`152.53.182.222`)  
**Окружение:** FastPanel, Nginx + PHP 8.4-FPM, MariaDB / MySQL, Flatsome 3.18.1  

---

## 🎯 1. ЦЕЛЕВАЯ СЕТЬ САЙТОВ (11 досок объявлений)

Все сайты размещены на сервере DE-222 в каталоге пользователя `doski`: `/var/www/doski/data/www/`

| № | Домен | Каталог сайта | База данных | Статус оптимизации |
|---|---|---|---|---|
| 1 | **doska-cz.ru** | `/var/www/doski/data/www/doska-cz.ru` | `doska-cz` | ✅ **Выполнено на 100% (Эталон)** |
| 2 | **doska-de.ru** | `/var/www/doski/data/www/doska-de.ru` | `doska_de` | ✅ **Выполнено на 100%** |
| 3 | **doska-esp.ru** | `/var/www/doski/data/www/doska-esp.ru` | `doska-esp` | ✅ **Выполнено на 100%** |
| 4 | **doska-fr.ru** | `/var/www/doski/data/www/doska-fr.ru` | `doska-fr` | ✅ **Выполнено на 100%** |
| 5 | **doska-gr.ru** | `/var/www/doski/data/www/doska-gr.ru` | `doska-gr` | ✅ **Выполнено на 100%** |
| 6 | **doska-hun.ru** | `/var/www/doski/data/www/doska-hun.ru` | `doska-hun` | ✅ **Выполнено на 100%** |
| 7 | **doska-isl.ru** | `/var/www/doski/data/www/doska-isl.ru` | `doska-isl` | ✅ **Выполнено на 100%** |
| 8 | **doska-it.ru** | `/var/www/doski/data/www/doska-it.ru` | `doska-it` | ✅ **Выполнено на 100%** |
| 9 | **doska-mld.ru** | `/var/www/doski/data/www/doska-mld.ru` | `doska-mld` | ✅ **Выполнено на 100%** |
| 10 | **doska-pl.ru** | `/var/www/doski/data/www/doska-pl.ru` | `doska-pl` | ✅ **Выполнено на 100%** |
| 11 | **doska-ua.ru** | `/var/www/doski/data/www/doska-ua.ru` | `doska-ua` | ✅ **Выполнено на 100%** |

---

## 📌 2. СУТЬ И ЦЕЛИ ОПТИМИЗАЦИИ

1. **Ликвидация плагинов-пустышек:** Удаление `wpforms-lite` (на всех досках контакты ведут в Telegram/WhatsApp, веб-формы отсутствуют).
2. **Замена тяжелого плагина даты/времени на авторский микро-плагин:**
   * Замена `wp-date-and-time-shortcode` (версии 2.7.1) на легковесный авторский `wp-date-time-micro` (версия `2026-11__1.00`).
   * Полное сохранение работоспособности шорткодов Flatsome:  
     `░ 📅 [wpdts-date] ░ 🕓 [wpdts-time] ░`  
     с выводом даты и времени с учетом часового пояса сайта (`wp_date()`).
3. **Глубокая очистка базы данных от хвостов удаленных плагинов:**
   * Удаление таблиц `wpDiscuz` (`wp_wc_*`).
   * Удаление таблиц `WPForms` (`wp_wpforms_*`).
   * Удаление осиротевших таблиц старых плагинов (`wp_seopress_content_analysis`, `wp_hcaptcha_events`).
   * Очистка тяжелых логов тестовой отправки почты (`wp_test_email_logs`).
   * Очистка сотен устаревших транзиентов в `wp_options` (`wp transient delete --expired`).
4. **Очистка файловой системы:**
   * Удаление мусорных каталогов в `/wp-content/uploads/wpdiscuz/` и `/wp-content/uploads/wpforms/`.

---

## 🛠️ 3. ПОШАГОВЫЙ РЕГЛАМЕНТ ДЛЯ КАЖДОГО САЙТА

### Шаг 1. Создание резервной копии БД
```bash
wp db export /root/backups/${DOMAIN}_before_opt_$(date +%Y%m%d).sql --path=/var/www/doski/data/www/${DOMAIN} --allow-root
```

### Шаг 2. Развертывание и активация wp-date-time-micro
```bash
# Копирование авторского микро-плагина из репозитория
cp -r /AI/GitHub/WordPress/plugins/wp-date-time-micro /var/www/doski/data/www/${DOMAIN}/wp-content/plugins/
chown -R doski:doski /var/www/doski/data/www/${DOMAIN}/wp-content/plugins/wp-date-time-micro

# Активация микро-плагина
wp plugin activate wp-date-time-micro --path=/var/www/doski/data/www/${DOMAIN} --allow-root

# Деактивация и удаление старого стороннего плагина
wp plugin deactivate wp-date-and-time-shortcode --path=/var/www/doski/data/www/${DOMAIN} --allow-root 2>/dev/null
wp plugin delete wp-date-and-time-shortcode --path=/var/www/doski/data/www/${DOMAIN} --allow-root 2>/dev/null
```

### Шаг 3. Деактивация и удаление WPForms Lite
```bash
wp plugin deactivate wpforms-lite --path=/var/www/doski/data/www/${DOMAIN} --allow-root 2>/dev/null
wp plugin delete wpforms-lite --path=/var/www/doski/data/www/${DOMAIN} --allow-root 2>/dev/null
```

### Шаг 4. Дроп мусорных таблиц из базы данных
```sql
DROP TABLE IF EXISTS wp_wc_comments_subscription, wp_wc_feedback_forms, wp_wc_follow_users, wp_wc_phrases, wp_wc_users_rated, wp_wc_users_voted;
DROP TABLE IF EXISTS wp_wpforms_analytics_forms, wp_wpforms_analytics_snapshots, wp_wpforms_logs, wp_wpforms_payment_meta, wp_wpforms_payments, wp_wpforms_product_events_queue, wp_wpforms_tasks_meta;
DROP TABLE IF EXISTS wp_seopress_content_analysis;
DROP TABLE IF EXISTS wp_hcaptcha_events;
TRUNCATE TABLE wp_test_email_logs;
```

### Шаг 5. Очистка просроченных транзиентов в wp_options
```bash
wp transient delete --expired --path=/var/www/doski/data/www/${DOMAIN} --allow-root
```

### Шаг 6. Удаление остаточных папок в wp-content/uploads
```bash
rm -rf /var/www/doski/data/www/${DOMAIN}/wp-content/uploads/wpdiscuz
rm -rf /var/www/doski/data/www/${DOMAIN}/wp-content/uploads/wpforms
```

### Шаг 7. Верификация
```bash
# Проверка HTTP ответа (должен быть 200 OK)
curl -sI https://${DOMAIN}/ | head -n 1

# Проверка рендеринга шорткода времени в шапке
curl -s https://${DOMAIN}/ | grep -o '░ 📅 [^<]*'
```

---

## ⚡ 4. ГОТОВЫЙ СКРИПТ АВТОМАТИЗАЦИИ ТИРАЖИРОВАНИЯ (1-CLICK)

Для применения на остальные 10 досок создан скрипт:
`/AI/GitHub/WordPress/plugins/apply_doska_network_optimization.sh`

Скрипт проходит в цикле по массиву оставшихся доменов:
`doska-de.ru doska-esp.ru doska-fr.ru doska-gr.ru doska-hun.ru doska-isl.ru doska-it.ru doska-mld.ru doska-pl.ru doska-ua.ru`
и последовательно выполняет шаги 1–7 с контролем возвращаемых кодов.

---

## 🚀 5. СТРАТЕГИЧЕСКИЙ ROADMAP: СОБСТВЕННЫЙ ДВИЖОК ДОСКИ И КАТАЛОГА

В будущем вместо двух тяжелых сторонних монстров (`ACADP` + `Directorist`):
1. **Проектирование единого решения `WP Classifieds & Companies (VladiMIR+AI)`**:
   * Единый кастомный тип записи для объявлений (`board_listing`) и каталога компаний (`company_listing`).
   * Легковесный личный кабинет пользователя на AJAX без громоздких библиотек.
   * Прямая интеграция с Flatsome UX Builder (нативные шорткоды сетки объявлений и поиска).
   * Автоматическая модерация, защита через Cloudflare Turnstile, генерация Clean Head Meta и автотранслитерация.
   * Снижение числа SQL-запросов и времени генерации страницы (TTFB) до рекордных < 100 мс.
