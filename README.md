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

# Лог эксперимента

- день 1 (1ч): подготовка ТЗ через deepseek
- день 2 (2ч): прототипирование интерфейсов html и css, 10шт в v0.dev
- день 3 (1.5ч): приведение html-кода к чистовому варианту, с едиными стилями, ссылками и открывающимися модалками с репозиторием в github, с помощью cursor и модели sonnet 3.7 и 3.5
- день 4 (1.2ч): сделали дизайн в стиле РЖД, подключили symfony, нарезали верстку на шаблоны и контроллеры
- день 5 (1.1ч): написали план спринта, установили докер, локально окружение разработки

# Запуск проекта с использованием Docker

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

3. Приложение будет доступно по адресу:
   ```
   http://localhost:8080
   ```

4. Для доступа к Adminer (управление БД):
   ```
   http://localhost:8181
   ```
   - Сервер: database
   - Пользователь: app
   - Пароль: app_password
   - База данных: app

## Остановка проекта

```
docker-compose down
```

## Пересборка контейнеров

Если вы внесли изменения в Docker-конфигурацию, выполните:
```
./docker-rebuild.sh
```

Более подробная информация о Docker-инфраструктуре проекта доступна в [docker/README.md](docker/README.md).
