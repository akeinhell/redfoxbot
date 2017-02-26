### Официальный репозиторий телеграмм-бота [Лиса](https://redfoxbot.ru)

[Чат для разработчиков](https://t.me/joinchat/AAAAAAz_1f35EIrwXDSQ1g)

Сейчас поддерживаются проекты:
- redfox
- encounter
- Dozor.lite
- экипаж
- lampagame
- dozor classic


## Установка
# Клонируем репозиторий и устанавливаем зависимости
Для разворачивания проекта необходимы `composer, npm, gulp, bower`

```bash
git clone https://github.com/akeinhell/redfoxbot.git
cd redfoxbot
composer install
npm install # или yarn install
gulp build
```

# Настраиваем Nginx
```bash
cp nginx.conf /etc/nginx/sites-available/redfoxbot.conf
ln -s /etc/nginx/sites-available/redfoxbot.conf /etc/nginx/sites-enabled/redfoxbot.conf
service nginx restart
```

#Настраиваем проект
```bash
cp .env.example .env
```

В Файле `.env` необходимо указать слеующие параметры
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

Необходимо накатить миграции
`./artisan migrate`

## Разработка и тонкости проекта
- Бот работает на hook'ах, соответственно для локальной разработки сейчас нет метода для инструмента
- Все ваши Pull-Request'ы будут слиты и вылиты на production сервера в течении нескольких дней после Сode-Review
- Я всегда готов ответить на все вопросы про бота и его логику, и тонкости реализации той или иной фичи
- в проекте необходимо поддерживать единый стиль кода. [#code-style](Читать подробнее тут)
