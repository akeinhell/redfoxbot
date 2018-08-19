# Официальный репозиторий телеграмм-бота [Лиса](https://redfoxbot.ru)

[![codecov](https://codecov.io/gh/akeinhell/redfoxbot/branch/master/graph/badge.svg)](https://codecov.io/gh/akeinhell/redfoxbot)

BuildStatus: [![CircleCI](https://circleci.com/gh/akeinhell/redfoxbot/tree/master.svg?style=svg)](https://circleci.com/gh/akeinhell/redfoxbot/tree/master)

Сейчас поддерживаются проекты:

- redfox
- encounter
- Dozor.lite
- экипаж
- ~~lampagame~~
- ~~dozor classic~~

## Установка

### Пререквизиты

Для сборки и запуска проекта необходимы:

- PHP 7.1+ (c PHP-FPM и composer)
- NodeJS 8+ (c NPM/Yarn)
- MySQL или PostgreSQL
- Redis
- Nginx

### Клонируем репозиторий и устанавливаем зависимости

Для разворачивания проекта необходимы `composer, npm`

```bash
git clone https://github.com/akeinhell/redfoxbot.git
cd redfoxbot
composer install
npm install # или yarn install (но под yarn у меня не хотела выкачиваться часть зависимостей)
```

### Настраиваем Nginx

```bash
cp nginx.conf /etc/nginx/sites-available/redfoxbot.conf
ln -s /etc/nginx/sites-available/redfoxbot.conf /etc/nginx/sites-enabled/redfoxbot.conf
service nginx restart
```

### Настраиваем проект

```bash
cp .env.example .env
```

В Файле `.env` необходимо указать следующие параметры:

```
TELEGRAM_KEY=$TELEGRAM_KEY
```

Ключ для бота получаем [тут](https://core.telegram.org/bots#6-botfather)

Конфиги для запуска приложения правим в `.env` файле

- БД

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

- Cache

```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

- Other

```
BOT_NAME ="Redfox Telegram bot"
```

Накатываем миграции для создания необходимых таблиц в БД

`./artisan migrate`

### Запускаем бота

`./artisan bot:start`

## Разработка и тонкости проекта

- Бот работает на hook'ах, соответственно для локальной разработки сейчас нет метода для инструмента
- Все ваши Pull-Request'ы будут слиты и вылиты на production сервера в течении нескольких дней после Сode-Review
- Я всегда готов ответить на все вопросы про бота и его логику, и тонкости реализации той или иной фичи
- В проекте необходимо поддерживать единый стиль кода. [#code-style](Читать подробнее тут)
