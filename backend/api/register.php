<?php
/**
 * API для регистрации пользователей
 */
session_start();
require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/User.php';

// Загружаем конфигурацию reCAPTCHA
$recaptchaConfig = require_once '../config/recaptcha.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    // Валидация данных
    if (empty($data->username) || empty($data->email) || empty($data->password) || empty($data->recaptcha)) {
        http_response_code(400);
        echo json_encode(["message" => "Все поля обязательны для заполнения"]);
        exit;
    }
    
    // Проверка Google reCAPTCHA v2
    $recaptchaResponse = $data->recaptcha;
    $secretKey = $recaptchaConfig['secret_key'];
    
    // Отправляем запрос на проверку в Google
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $postData = http_build_query([
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    $ch = curl_init($verifyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $verifyResponse = curl_exec($ch);
    curl_close($ch);
    
    $responseData = json_decode($verifyResponse);
    
    if (!$responseData->success) {
        http_response_code(400);
        echo json_encode(["message" => "Проверка reCAPTCHA не пройдена. Попробуйте еще раз"]);
        exit;
    }
    
    // Валидация username
    if (strlen($data->username) < 3 || strlen($data->username) > 50) {
        http_response_code(400);
        echo json_encode(["message" => "Имя пользователя должно быть от 3 до 50 символов"]);
        exit;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $data->username)) {
        http_response_code(400);
        echo json_encode(["message" => "Имя пользователя может содержать только буквы, цифры и подчеркивание"]);
        exit;
    }
    
    // Валидация email
    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Некорректный email адрес"]);
        exit;
    }
    
    // Валидация пароля
    if (strlen($data->password) < 6) {
        http_response_code(400);
        echo json_encode(["message" => "Пароль должен быть не менее 6 символов"]);
        exit;
    }
    
    // Установка данных пользователя
    $user->username = $data->username;
    $user->email = $data->email;
    $user->password_hash = $data->password;
    
    // Проверка существования
    if ($user->exists()) {
        http_response_code(409);
        echo json_encode(["message" => "Пользователь с таким именем или email уже существует"]);
        exit;
    }
    
    // Создание пользователя
    if ($user->create()) {
        http_response_code(201);
        echo json_encode([
            "message" => "Регистрация успешна! Теперь вы можете войти в систему",
            "user" => [
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Ошибка при создании пользователя"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Метод не поддерживается"]);
}
?>
