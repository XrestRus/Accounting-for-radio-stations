# Accounting-for-radio-stations
Accounting for radio stations

# Изображения

<details >
    <summary>Первая версия стилей</summary>
    
  ![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/1.img-before-style.png)
</details> 

<details >
    <summary>Вторая версия стилей</summary>
   
  ![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/2.img-after-style.png)
</details> 

<details >
    <summary>Структура верстки</summary>
    
  ![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/4.code.png)
</details> 

<details >
    <summary>Список устройств</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/5.list-device.png)
</details> 

<details >
    <summary>Форма добавление устройства</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/7.add-device.png)
</details> 

<details >
    <summary>Форма редактирование устройства</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/6.edit-device.png)
</details> 

<details >
    <summary>Удаление устройства</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/8.remove-device.png)
</details> 

<details >
    <summary>Возврат устройства</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/9.device-return.png)
</details> 

<details >
    <summary>Выдача устройства</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/10.device-issue.png)
</details> 

<details >
    <summary>Список устройств</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/11.devices.png)
</details> 

<details >
    <summary>Список сотрудников</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/14.employees.png)
</details> 

<details >
    <summary>Журнал учета</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/13.journal.png)
</details> 

<details >
    <summary>Авторизация</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/12.login.png)
</details> 

<details >
    <summary>Отчеты</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/15.reports.png)
</details> 

<details >
    <summary>Управление пользователями</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/16.users.png)
</details> 

<details >
    <summary>Добавление пользователя</summary>

![](https://github.com/XrestRus/Accounting-for-radio-stations/blob/master/context/17.user-add.png)
</details> 

# Лог эксперимента

- день 1 (1ч): подготовка ТЗ через deepseek
- день 2 (2ч): прототипирование интерфейсов html и css, 10шт в v0.dev
- день 3 (1.5ч): приведение html-кода к чистовому варианту, с едиными стилями, ссылками и открывающимися модалками с репозиторием в github, с помощью cursor и модели sonnet 3.7 и 3.5
- день 4 (1.2ч): сделали дизайн в стиле РЖД, подключили symfony, нарезали верстку на шаблоны и контроллеры
- день 5 (1.1ч): написали план спринта, установили докер, локально окружение разработки
- день 6 (1.2ч): первичные модели в БД, страница списка устройств и формы добавления и редактирования устройств
- день 7 (1.3ч): доработка списка устройств, формы возврата и выдачи устройств
- день 8 (1.2ч): список сотрудников и журнал учета, форма входа, добавление и редактирование сотрудников, размещение на продакшене
- день 9 (1.2ч): экран отчетов, экран управления пользователями, главный экран (Dashboard)

# Запуск проекта с использованием Docker

## Требования к системе

- Docker и Docker Compose (актуальные версии)
- Git
- Минимум 2 ГБ свободной оперативной памяти
- Минимум 1 ГБ свободного места на диске

## Быстрый старт

1. Клонируйте репозиторий:
   ```
   git clone https://github.com/XrestRus/Accounting-for-radio-stations.git
   cd Accounting-for-radio-stations
   ```

2. Запустите Docker-контейнеры:
   ```
   docker-compose up -d --build
   ```

3. Установите зависимости и инициализируйте проект:
   ```
   docker-compose exec php composer install
   docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
   docker-compose exec php bin/console doctrine:fixtures:load --no-interaction
   ```

4. Приложение будет доступно по адресу:
   ```
   http://localhost:8080
   ```

5. Данные для входа (после загрузки фикстур):
   - Администратор: admin@example.com / password
   - Оператор: operator@example.com / password

6. Для доступа к Adminer (управление БД):
   ```
   http://localhost:8181
   ```
   - Сервер: database
   - Пользователь: app
   - Пароль: app_password
   - База данных: app

## Управление проектом

### Остановка проекта
```
docker-compose down
```

### Перезапуск проекта
```
docker-compose restart
```

### Пересборка контейнеров
Если вы внесли изменения в Docker-конфигурацию, выполните:
```
docker-compose down
docker-compose up -d --build
```

### Просмотр логов
```
docker-compose logs -f
docker-compose logs -f php  # логи только PHP-контейнера
```

### Доступ к контейнерам
```
docker-compose exec php bash  # доступ к PHP-контейнеру
docker-compose exec database mysql -uapp -papp_password app  # доступ к MySQL
```

## Конфигурация окружения

Настройки проекта хранятся в файлах `.env` и `.env.local`. Для продакшн-окружения рекомендуется:

1. Создать файл `.env.local` с переопределением настроек:
   ```
   APP_ENV=prod
   APP_SECRET=ваш_секретный_ключ
   DATABASE_URL=mysql://app:app_password@database:3306/app?serverVersion=8.0&charset=utf8mb4
   ```

2. Для продакшн-сервера необходимо выполнить:
   ```
   docker-compose exec php composer dump-env prod
   docker-compose exec php composer install --no-dev --optimize-autoloader
   ```

## Развертывание на продакшен-сервере

1. Клонируйте репозиторий на ваш продакшн-сервер:
   ```
   git clone https://github.com/XrestRus/Accounting-for-radio-stations.git
   cd Accounting-for-radio-stations
   ```

2. Создайте файл `.env.local` с настройками для продакшена:
   ```
   APP_ENV=prod
   APP_SECRET=ваш_уникальный_секретный_ключ
   DATABASE_URL=mysql://ваш_пользователь:ваш_пароль@localhost:3306/ваша_база?serverVersion=8.0&charset=utf8mb4
   ```

3. Запустите контейнеры в продакшн-режиме:
   ```
   docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
   ```

4. Инициализируйте проект:
   ```
   docker-compose exec php composer install --no-dev --optimize-autoloader
   docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
   docker-compose exec php bin/console assets:install
   ```

5. Настройте Nginx (или другой веб-сервер) для проксирования запросов в контейнер.

6. Настройте SSL с помощью Let's Encrypt для безопасного соединения.

7. Для обновления системы используйте:
   ```
   git pull
   docker-compose exec php composer install --no-dev --optimize-autoloader
   docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
   docker-compose exec php bin/console cache:clear
   docker-compose restart php
   ```

Более подробная информация о Docker-инфраструктуре проекта доступна в [docker/README.md](docker/README.md).

# Unit тестирование

В проекте реализованы unit-тесты для проверки корректности работы ключевых классов. Для запуска тестов следуйте инструкции:

## Запуск всех тестов

```bash
docker-compose exec php php ./bin/phpunit
```

## Запуск определенного теста или группы тестов

Для запуска конкретного теста используйте параметр `--filter`:

```bash
docker-compose exec php php ./bin/phpunit --filter=DeviceTest
```

## Создание новых тестов

1. Создайте файл теста в директории `tests/` с соответствующим namespace и суффиксом `Test`.
2. Для классов-сущностей (Entity) создавайте тесты в `tests/Entity/`.
3. Для контроллеров создавайте тесты в `tests/Controller/`.

## Покрытие кода тестами

Для генерации отчета о покрытии кода тестами выполните:

```bash
docker-compose exec php php ./bin/phpunit --coverage-html var/coverage
```

После выполнения команды отчет будет доступен в директории `var/coverage/`.
