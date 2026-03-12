# com_sitereport — Website Analysis component for Joomla 5

Краткая инструкция и справка по компоненту `com_sitereport` (Joomla 5.x).

## Краткое описание
Компонент выполняет технический и SEO-анализ сайта: HTTP/headers, SSL, HTML (title, meta), H1/H2, изображения, ссылки, robots/sitemap, безопасность (HSTS/CSP/X-Frame), GZIP и прочие проверки. Имеет админ-панель для списка отчётов и фронтенд для быстрого анализа.

## Установка
- Упакуйте ZIP с корнем содержащим: `com_sitereport.xml`, папки `administrator` и `components`.
- Установите через Менеджер расширений Joomla.
- Если обновляете, сначала удалите старую версию (если требуется пересоздать таблицу).

## SEF / Чистые URL
Чтобы получить URL вида `/report`:
1. Включите в Joomla `SEF` и `Use URL rewriting`.
2. На сервере переименуйте `htaccess.txt` → `.htaccess`.
3. Создайте пункт меню с типом `Отчет` (alias `report`).

## Быстрый старт (frontend)
- Откройте пункт меню `Отчет`.
- Введите домен (напр. `example.com`) и нажмите "Анализировать".
- Результат появится на той же странице карточками (UIKit).

## Админ-панель
- Расширения → Компоненты → `com_sitereport` → `Список отчетов`.
- В списке отображается домен, IP, HTTP-код, время ответа, SEO-score и SSL.
- Откройте детальный отчёт для полного набора проверок.

## Файловая структура (ключевые файлы)
- `com_sitereport.xml` — манифест
- `administrator/components/com_sitereport/services/provider.php` — регистрация компонента и сервисов
- `administrator/components/com_sitereport/src/` — контроллеры, модели, таблица, view
- `administrator/components/com_sitereport/tmpl/` — админ-шаблоны
- `components/com_sitereport/src/` — фронтенд модели/контроллеры/views
- `components/com_sitereport/tmpl/` — фронтенд-шаблоны
- `administrator/components/com_sitereport/sql/*.sql` — install/uninstall

## Технические требования
- Joomla 5.4.x
- PHP 8.1+ (рекомендуется 8.2), расширения: `curl`, `openssl`, `mbstring`, `dom`.

## Реализованные проверки
- HTTP: IP, HTTP code, response time, headers, gzip
- SSL: валидность, издатель, expires, days left
- HTML: title, meta description, H1/H2, word count, encoding
- Images: total, without alt
- Links: total / internal / external
- SEO: Open Graph, robots.txt, sitemap.xml, cumulative SEO score
- Security: HSTS, CSP, X-Frame-Options, X-Content-Type-Options

## Разработка и заметки
- Все namespaced файлы должны иметь `namespace` сразу после `declare(strict_types=1);` (PHP 8.2 требование). Это уже исправлено в проекте.
- Шаблоны лежат в `tmpl/{view}` (Joomla ищет их там).
- Анализ происходит в `components/com_sitereport/src/Model/ReportModel.php` (использует cURL и DOMDocument).
- Для расширения проверок добавляйте функции в `ReportModel::analyzeDomain()` и/или выделяйте сервисы.

## Известные моменты / рекомендации
- При упаковке ZIP убедитесь, что в архиве нет лишних вложенных папок (в корне ZIP должен быть `com_sitereport.xml`).
- На shared-хостингах отключённая проверка SSL (verify_peer=false) может быть временным решением — лучше включить проверку при доступности CA.
- Если SEF ведёт на `index.php/index.php`, используйте `action="index.php"` и добавьте `Itemid` в форму (это уже реализовано).

## Как помочь/развивать
- Добавить асинхронную очередь для длительных проверок (RabbitMQ / cron)
- Кэширование результатов и повторный анализ по расписанию
- Расширение UI: фильтры, экспорт CSV/PDF
 