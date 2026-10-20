# 🌐 WordPress Ecosystem & Ultra-Light Plugins Suite (VladiMIR+AI)

> **Repository:** [GinCz/WordPress ↗](https://github.com/GinCz/WordPress)  
> **Author & Architect:** Ing. Vladimir Bulantsev ([GinCz ↗](https://github.com/GinCz))  
> **Brand Tag:** `(VladiMIR+AI✅)`  
> **Production Nodes:** DE-222 (`152.53.182.222`), RU-109 (`212.109.223.109`), ORACLE-157 (`130.61.101.157`)  

---

## 📌 Обзор репозитория

Публичный репозиторий архитектурных решений, конфигураций и авторского пакета легковесных микро-плагинов **WordPress** и **WooCommerce**, разработанных **Владимиром Буланцевым (GinCz)** совместно с AI.

Все плагины поставляются **со 100% открытыми исходными кодами**, полностью очищены от сторонней рекламы, трекеров, скрытых внешних скриптов и тяжелых запросов к базе данных, многократно превосходя тяжелые аналоги из каталога WordPress.org.

---

## ⚡ Каталог авторских микро-плагинов (`plugins/`)

Все плагины и их полные исходники расположены в каталоге [`plugins/` ↗](https://github.com/GinCz/WordPress/tree/main/plugins):

| № | Плагин | Версия | Назначение и ключевые возможности | Исходники |
|---|---|---|---|---|
| 1 | **404-410-301** | `1.31` | Мгновенная отдача HTTP 410 Gone для удаленного мусора и 301 редиректы без нагрузки на БД | [404-410-301/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/404-410-301) |
| 2 | **classic-editor-tinymce** | `1.31` | Классический редактор WordPress без лишних скриптов Gutenberg | [classic-editor-tinymce/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/classic-editor-tinymce) |
| 3 | **clean-head-meta** | `1.31` | Очистка `<head>` от мусора: RSD, WLW, короткие ссылки, emoji, generator | [clean-head-meta/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/clean-head-meta) |
| 4 | **disable-update-emails** | `1.31` | Отключение спам-уведомлений на email о фоновых автообновлениях | [disable-update-emails/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/disable-update-emails) |
| 5 | **image-resizer** | `1.31` | Автоматическая оптимизация и сжатие загружаемых изображений на лету | [image-resizer/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/image-resizer) |
| 6 | **translit-cyr-lat** | `1.31` | Правильная транслитерация кириллических URL (ЧПУ) и имен медиафайлов | [translit-cyr-lat/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/translit-cyr-lat) |
| 7 | **wp-allow-html-cats** | `1.31` | Безопасная поддержка чистого HTML в описаниях рубрик и категорий | [wp-allow-html-cats/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-allow-html-cats) |
| 8 | **wp-auto-sku** | `1.31` | Автоматическая генерация артикулов (SKU) товаров WooCommerce | [wp-auto-sku/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-auto-sku) |
| 9 | **wp-bulk-delete-clean** | `1.31` | Пакетная очистка ревизий, спам-комментариев и устаревших транзиентов | [wp-bulk-delete-clean/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-bulk-delete-clean) |
| 10 | **wp-online-counter** | `1.31` | Легковесный счётчик активных онлайн-посетителей без внешних счетчиков | [wp-online-counter/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-online-counter) |
| 11 | **wp-seo-micro** | `1.31` | Ультра-быстрый SEO-модуль (OpenGraph, meta title/desc, canonical) без раздутых таблиц | [wp-seo-micro/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-seo-micro) |
| 12 | **wp-simple-post-order** | `1.31` | Drag & Drop сортировка постов, страниц и кастомных записей | [wp-simple-post-order/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-simple-post-order) |
| 13 | **wp-test-email-micro** | `1.37` | Диагностика SMTP, SPF, DKIM (2048-bit), DMARC и мгновенная отправка тестовых писем | [wp-test-email-micro/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-test-email-micro) |

---

## 🔄 Нативная система автообновления

Каждый плагин оснащен встроенным клиентом `vladimir-ai-updater.php`, который интегрируется в нативный интерфейс обновления WordPress Core:

1. **Header Declaration:** `Update URI: https://vladimir-ai.updates/<slug>`.
2. **Централизованный манифест:** [`plugins/updates.json` ↗](https://github.com/GinCz/WordPress/blob/main/plugins/updates.json).
3. **Готовые ZIP-пакеты:** [`plugins/zips/` ↗](https://github.com/GinCz/WordPress/tree/main/plugins/zips).

---

## 📁 Структура репозитория

```
WordPress/
├── README.md                      # Корневой манифест репозитория
└── plugins/                       # Каталог авторских плагинов с исходниками
    ├── 404-410-301/               # Плагин управления битыми ссылками и 410 Gone
    ├── classic-editor-tinymce/    # Classic Editor + расширенные кнопки TinyMCE
    ├── clean-head-meta/           # Очистка заголовков HTML
    ├── disable-update-emails/     # Подавление системного спама
    ├── image-resizer/             # Автоматическое сжатие картинок
    ├── translit-cyr-lat/          # ЧПУ транслитерация
    ├── wp-allow-html-cats/        # HTML в категориях
    ├── wp-auto-sku/               # SKU генератор
    ├── wp-bulk-delete-clean/      # Клинер БД
    ├── wp-online-counter/         # Онлайн счетчик
    ├── wp-seo-micro/              # Микро SEO комбайн
    ├── wp-simple-post-order/      # Сортировка записей
    ├── wp-test-email-micro/       # Почтовый комплаенс и тестер
    ├── _shared/                   # Общие модули i18n и updater
    ├── zips/                      # Готовые релизные архивы
    ├── updates.json               # Манифест версий для нативного апдейтера
    ├── PLANS.md                   # Дорожная карта развития плагинов
    ├── MIGRATION_TZ.md            # ТЗ миграции боевых сайтов
    └── RULES.md                   # Регламенты качества кода и безопасности
```

<br/>

---

<br/>

<div align="center">

<h3>= Rooted by VladiMIR + AI =</h3>

<br/>

<a href="https://github.com/GinCz" target="_blank" rel="noopener noreferrer"><img src="https://img.shields.io/badge/GitHub-181717?style=flat&logo=github&logoColor=white" alt="GitHub" /></a>
&nbsp;&nbsp;<b>|||</b>&nbsp;&nbsp;
<a href="https://www.linkedin.com/in/gincz/" target="_blank" rel="noopener noreferrer"><img src="https://img.shields.io/badge/LinkedIn-0A66C2?style=flat&logo=linkedin&logoColor=white" alt="LinkedIn" /></a>
&nbsp;&nbsp;<b>|||</b>&nbsp;&nbsp;
<a href="https://uslugi.yandex.ru/profile/VladimirBulantsev-1756893" target="_blank" rel="noopener noreferrer"><img src="https://img.shields.io/badge/Яндекс.Услуги-FC3F1D?style=flat&logo=yandex&logoColor=white" alt="Яндекс.Услуги" /></a>

<br/><br/>

<sub>WordPress &copy; 2026. VladiMIR + AI</sub>

</div>
