# 🌐 Мастер-план масштабирования и миграции сети досок объявлений (11 сайтов)
> **Репозиторий:** [GinCz/WordPress](https://github.com/GinCz/WordPress)  
> **Автор:** Владимир Буланцев (@GinCz) + AI  
> **Версия архитектуры:** v2026-11__1.08  
> **Сервер:** `DE-222` (`152.53.182.222`)  

---

## 📌 1. Реестр досок объявлений сети

| № | Домен | Страна / Локация | Путь на DE-222 | Статус |
|---|---|---|---|---|
| 1 | `doska-cz.ru` | Чехия (CZ) | `/var/www/doski/data/www/doska-cz.ru/` | ✅ **Эталон внедрен (v1.08)** |
| 2 | `doska-de.ru` | Германия (DE) | `/var/www/doski/data/www/doska-de.ru/` | ⏳ Очередь внедрения |
| 3 | `doska-pl.ru` | Польша (PL) | `/var/www/doski/data/www/doska-pl.ru/` | ⏳ Очередь внедрения |
| 4 | `doska-fr.ru` | Франция (FR) | `/var/www/doski/data/www/doska-fr.ru/` | ⏳ Очередь внедрения |
| 5 | `doska-esp.ru` | Испания (ES) | `/var/www/doski/data/www/doska-esp.ru/` | ⏳ Очередь внедрения |
| 6 | `doska-it.ru` | Италия (IT) | `/var/www/doski/data/www/doska-it.ru/` | ⏳ Очередь внедрения |
| 7 | `doska-gr.ru` | Греция (GR) | `/var/www/doski/data/www/doska-gr.ru/` | ⏳ Очередь внедрения |
| 8 | `doska-mld.ru` | Молдова (MD) | `/var/www/doski/data/www/doska-mld.ru/` | ⏳ Очередь внедрения |
| 9 | `doska-hun.ru` | Венгрия (HU) | `/var/www/doski/data/www/doska-hun.ru/` | ⏳ Очередь внедрения |
| 10 | `doska-ua.ru` | Украина (UA) | `/var/www/doski/data/www/doska-ua.ru/` | ⏳ Очередь внедрения |
| 11 | `doska-isl.ru` | Израиль (IL) | `/var/www/doski/data/www/doska-isl.ru/` | ⏳ Очередь внедрения |

---

## 🏗️ 2. Что внедрено на эталоне (doska-cz.ru)

1. **Единый сверхбыстрый плагин `WP Board & Directory`:**
   - Полная замена двух тяжелых конфликтующих монстров (`Advanced Classifieds and Directory Pro (ACADP)` и `Directorist`).
   - Единый CPT `vb_listing` со статусами объявлений (`classified`) и компаний (`company`).
   - Иерархические рубрики `vb_category` и города/регионы `vb_location`.
   - Чешские / локальные URL: `/inzerat/<slug>/`, `/inzeraty/`, `/kategorie/<slug>/`, `/mesto/<slug>/`.
   - 3-колоночный адаптивный каталог категорий с сохранением всех ключевых SEO-слов и иерархии (H2/H3).
   - Лимит: максимум 10 объявлений от одного аккаунта/email в одну рубрику.
   - Подача объявлений: сжатие до 15 фото на клиенте в WebP (макс 1024px) без нагрузки на сервер, Cloudflare Turnstile капча.
   - Единый личный кабинет: `/kabinet/` (`[vb_dashboard]`) с вкладками «Мой профиль», «Мои объявления», «Мои компании», продлением на 1/3/6/12 мес. и массовым удалением.
   - Автоматическое и безвозвратное удаление всех фото с диска при удалении объявления или компании.
   - Таблица в админке `edit.php?post_type=vb_listing`: превью фото, сортеруемые телефон и email, бейджи типов, фильтрация по категориям и городам.
   - Фильтрация на фронтенде внутри рубрик: по городам, ключевым словам и цене.

2. **Автономный плагин `WP Media Cleaner`:**
   - Сканирование и очистка неиспользуемых мусорных картинок на диске и в БД.
   - Полный иммунитет логотипов тем (включая Flatsome `site_logo`, `cz.gif`, `de.gif` и др.), фавиконов, галерей и товаров.

---

## 📋 3. Пошаговый протокол миграции каждого сайта

Для перехода очередного сайта (например, `doska-de.ru`) выполняется автоматизированный чеклист:

### Шаг 1: Контрольный бэкап базы и медиатеки
```bash
wp --path=/var/www/doski/data/www/<domain> db export /root/backup_<domain>_$(date +%Y%m%d).sql --allow-root
```

### Шаг 2: Установка актуальных пакетов плагинов
```bash
# Копирование свежих версий wp-board-directory и wp-media-cleaner
tar -cz -C /AI/GitHub/WordPress/plugins wp-board-directory wp-media-cleaner | ssh root@152.53.182.222 "tar -xz -C /var/www/doski/data/www/<domain>/wp-content/plugins/"
```

### Шаг 3: Активация новых плагинов и создание таблиц
```bash
wp --path=/var/www/doski/data/www/<domain> plugin activate wp-board-directory wp-media-cleaner --allow-root
```

### Шаг 4: 1-Click Миграция старых данных (ACADP + Directorist)
```bash
# Импорт категорий и объявлений из ACADP в vb_listing и vb_category
wp --path=/var/www/doski/data/www/<domain> vb import-acadp --allow-root

# Импорт компаний и категорий из Directorist
wp --path=/var/www/doski/data/www/<domain> vb import-directorist --allow-root
```

### Шаг 5: Деактивация и удаление старых плагинов
```bash
wp --path=/var/www/doski/data/www/<domain> plugin deactivate advanced-classifieds-and-directory-pro directorist --allow-root
wp --path=/var/www/doski/data/www/<domain> plugin delete advanced-classifieds-and-directory-pro directorist --allow-root
```

### Шаг 6: Настройка страниц и шорткодов
Проверить или обновить содержимое страниц в WP:
- **Главная (`/`):** содержит шорткод `[vb_categories]`.
- **Личный кабинет (`/kabinet/`):** содержит `[vb_dashboard]`. Редиректы со старых `/dashboard/`, `/my-ads/` ведут на `/kabinet/`.
- **Подача объявления (`/new/`):** содержит `[vb_submit_listing]`.
- **Каталог компаний (`/company/`):** содержит `[vb_companies_catalog]`.

### Шаг 7: Сброс правил постоянных ссылок (Permalinks)
```bash
wp --path=/var/www/doski/data/www/<domain> rewrite flush --allow-root
```

### Шаг 8: Контроль целостности логотипа и шапки
- Убедиться, что `theme_mods_flatsome['site_logo']` указывает на валидный флаг страны (`de.gif`, `pl.gif`, `fr.gif` и т.д.).
- Проверить HTTP 200 главной, архива `/inzeraty/`, категорий и кабинета `/kabinet/`.

### Шаг 9: Запуск Media Cleaner
```bash
# Проверка неиспользуемых файлов после удаления старых плагинов
wp --path=/var/www/doski/data/www/<domain> media-cleaner scan --allow-root
```
