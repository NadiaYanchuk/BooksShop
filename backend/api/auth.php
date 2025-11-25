<?php
/**
 * API для аутентификации (админы и пользователи)
 */
require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/Administrator.php';
require_once '../models/User.php';
require_once '../models/Session.php';

$database = new Database();
$db = $database->getConnection();

$admin = new Administrator($db);
$user = new User($db);
$session = new Session($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        // Вход в систему
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->username) && !empty($data->password)) {
            $authenticated = false;
            $user_id = null;
            $username = null;
            $email = null;
            $is_admin = false;
            
            // Сначала проверяем - это админ?
            if ($admin->authenticate($data->username, $data->password)) {
                $authenticated = true;
                $is_admin = true;
                $user_id = $admin->id;
                $username = $admin->username;
                $email = $admin->email;
                $session->admin_id = $admin->id;
                $session->user_id = null;
                $session->user_type = 'admin';
            } else {
                // Если не админ, проверяем обычного пользователя
                if ($user->authenticate($data->username, $data->password)) {
                    $authenticated = true;
                    $is_admin = false;
                    $user_id = $user->id;
                    $username = $user->username;
                    $email = $user->email;
                    $session->admin_id = null;
                    $session->user_id = $user->id;
                    $session->user_type = 'user';
                }
            }
            
            if ($authenticated) {
                // Создание сессии
                $session->ip_address = $_SERVER['REMOTE_ADDR'];
                $session->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                // Сессия действительна 24 часа
                $expires = new DateTime();
                $expires->add(new DateInterval('PT24H'));
                $session->expires_at = $expires->format('Y-m-d H:i:s');
                
                $session_id = $session->create();
                
                if ($session_id) {
                    http_response_code(200);
                    
                    // Возвращаем разные структуры для админа и пользователя
                    if ($is_admin) {
                        echo json_encode([
                            "message" => "Успешная аутентификация",
                            "session_id" => $session_id,
                            "admin" => [
                                "id" => $user_id,
                                "username" => $username,
                                "email" => $email
                            ]
                        ]);
                    } else {
                        echo json_encode([
                            "message" => "Успешная аутентификация",
                            "session_id" => $session_id,
                            "user" => [
                                "id" => $user_id,
                                "username" => $username,
                                "email" => $email
                            ]
                        ]);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Ошибка создания сессии"]);
                }
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Неверные учетные данные"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Необходимо указать username и password"]);
        }
        break;

    case 'DELETE':
        // Выход из системы
        $headers = getallheaders();
        $session_id = $headers['Authorization'] ?? '';
        
        if (!empty($session_id)) {
            $session->id = str_replace('Bearer ', '', $session_id);
            
            if ($session->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Успешный выход"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка выхода"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Не указан токен сессии"]);
        }
        break;

    case 'GET':
        // Проверка сессии
        $headers = getallheaders();
        $session_id = $headers['Authorization'] ?? '';
        
        if (!empty($session_id)) {
            $session_id = str_replace('Bearer ', '', $session_id);
            
            if ($session->validate($session_id)) {
                http_response_code(200);
                echo json_encode([
                    "valid" => true,
                    "admin_id" => $session->admin_id
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["valid" => false]);
            }
        } else {
            http_response_code(401);
            echo json_encode(["valid" => false]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Метод не поддерживается"]);
        break;
}
