<?php
/**
 * API для управления администраторами (защищенный)
 */
require_once '../../config/cors.php';
require_once '../auth_middleware.php';
require_once '../../config/database.php';
require_once '../../models/Administrator.php';

// Проверка аутентификации
$admin_id = checkAuth();

$database = new Database();
$db = $database->getConnection();

$admin = new Administrator($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Получить всех администраторов
        $stmt = $admin->readAll();
        $admins_arr = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $admin_item = [
                "id" => $row['id'],
                "username" => $row['username'],
                "email" => $row['email'],
                "created_at" => $row['created_at'],
                "last_login" => $row['last_login']
            ];

            array_push($admins_arr, $admin_item);
        }

        http_response_code(200);
        echo json_encode($admins_arr);
        break;

    case 'POST':
        // Создать нового администратора
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->username) &&
            !empty($data->email) &&
            !empty($data->password)
        ) {
            $admin->username = $data->username;
            $admin->email = $data->email;
            $admin->password_hash = $data->password;

            // Проверка существования
            if ($admin->exists()) {
                http_response_code(400);
                echo json_encode(["message" => "Пользователь с таким username или email уже существует"]);
            } else if ($admin->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Администратор успешно создан"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка при создании администратора"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Необходимо заполнить все поля"]);
        }
        break;

    case 'DELETE':
        // Удалить администратора
        if (isset($_GET['id'])) {
            $admin->id = $_GET['id'];
            
            // Нельзя удалить самого себя
            if ($admin->id == $admin_id) {
                http_response_code(400);
                echo json_encode(["message" => "Нельзя удалить свою учетную запись"]);
            } else if ($admin->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Администратор удален"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка при удалении администратора"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Не указан ID администратора"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Метод не поддерживается"]);
        break;
}
