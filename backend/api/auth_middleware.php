<?php
/**
 * Middleware для проверки аутентификации
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Session.php';

function checkAuth() {
    $database = new Database();
    $db = $database->getConnection();
    $session = new Session($db);
    
    $headers = getallheaders();
    $session_id = $headers['Authorization'] ?? '';
    
    if (empty($session_id)) {
        http_response_code(401);
        echo json_encode(["message" => "Требуется аутентификация"]);
        exit();
    }
    
    $session_id = str_replace('Bearer ', '', $session_id);
    
    if (!$session->validate($session_id)) {
        http_response_code(401);
        echo json_encode(["message" => "Недействительная или истекшая сессия"]);
        exit();
    }
    
    return $session->admin_id;
}
