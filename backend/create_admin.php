<?php
/**
 * Скрипт для создания первого администратора
 * Run: php create_admin.php
 */

require_once 'config/database.php';
require_once 'models/Administrator.php';

// Получение данных
echo "Введите имя пользователя (по умолчанию: admin): ";
$username = trim(fgets(STDIN));
if (empty($username)) {
    $username = 'admin';
}

echo "Введите email (по умолчанию: admin@example.com): ";
$email = trim(fgets(STDIN));
if (empty($email)) {
    $email = 'admin@example.com';
}

echo "Введите пароль (по умолчанию: admin123): ";
$password = trim(fgets(STDIN));
if (empty($password)) {
    $password = 'admin123';
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Ошибка подключения к базе данных\n");
}

$admin = new Administrator($db);
$admin->username = $username;
$admin->email = $email;
$admin->password_hash = $password;

if ($admin->exists()) {
    echo "\n Пользователь с таким username или email уже существует!\n";
    exit(1);
}

if ($admin->create()) {
    echo "\n Администратор успешно создан!\n";
    echo "Имя пользователя: $username\n";
    echo "Email: $email\n";
    echo "Пароль: $password\n";
} else {
    echo "\n Ошибка при создании администратора\n";
    exit(1);
}
