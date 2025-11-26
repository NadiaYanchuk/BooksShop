# Librarius - Прототип книжного магазина

## 1. Как запустить проект:
### База данных
```bash
mysql -u root -p < database/schema.sql
```
phpMyAdmin: 
```bash
cd /tmp/phpMyAdmin-5.2.1-all-languages && php -S localhost:8080
```
Открыть: **http://localhost:8080**

### Backend
```bash
cd backend
php -S localhost:8000
```
Открыть: **http://localhost:8000**

### Frontend
```bash
cd frontend
npm install
npm start
```
Открыть: **http://localhost:4200**


## 2. API Endpoints:

- `POST /api/register.php` - Регистрация

- `POST /api/auth.php` - Вход

- `GET /api/products.php` - Список продуктов

- `GET /api/reviews.php` - Отзывы

- `GET/POST/PUT/DELETE /api/admin/products.php` - Управление продуктами

- `GET/PUT/DELETE /api/admin/reviews.php` - Управление отзывами

- `GET/POST/PUT/DELETE /api/admin/administrators.php` - Управление администраторами


## 3. Технологии:

- **Хэширование паролей** - SHA-256 с уникальной солью для каждого пользователя
- **Frontend**: Angular 17, TailwindCSS 
- **Backend**: PHP 8.x, MySQL
- **AuthGuard** - защита роутов админ-панели на клиенте
- **Аутентификация**: Session-based, Google reCAPTCHA v2
- **Middleware** - проверка сессии на сервере для каждого защищенного запроса
- **API**: RESTful, OpenAPI 3.0
- **CORS** - настроенные политики доступа
