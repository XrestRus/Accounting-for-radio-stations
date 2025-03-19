# Инструкция по Docker-инфраструктуре

## Запуск проекта

1. Сборка и запуск контейнеров:
   ```
   docker-compose up -d --build
   ```

2. Установка зависимостей Composer (если необходимо):
   ```
   docker-compose exec php composer install
   ```

3. Применение миграций базы данных:
   ```
   docker-compose exec php bin/console doctrine:migrations:migrate
   ```

4. Загрузка фикстур (тестовых данных):
   ```
   docker-compose exec php bin/console doctrine:fixtures:load
   ```

5. Приложение доступно по адресу:
   ```
   http://localhost:8080
   ```

## Полезные команды

- Просмотр логов:
  ```
  docker-compose logs -f
  ```

- Остановка контейнеров:
  ```
  docker-compose down
  ```

- Доступ к PHP-контейнеру:
  ```
  docker-compose exec php bash
  ```

- Доступ к базе данных:
  ```
  docker-compose exec database mysql -uapp -papp_password app
  ```

## Параметры подключения к базе данных

- **Host**: database
- **Port**: 3306
- **Database**: app
- **Username**: app
- **Password**: app_password

Эти параметры настроены в файле `.env.local` и docker-compose.yml. 