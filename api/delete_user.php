<?php

require_once('includes/api_headers.php');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
    exit;
}

require_once('includes/connect_to_db.php');

// Требуется для кодирования веб-токена JSON.
require_once "config/core.php";
require_once "libs/php-jwt/src/BeforeValidException.php";
require_once "libs/php-jwt/src/ExpiredException.php";
require_once "libs/php-jwt/src/SignatureInvalidException.php";
require_once "libs/php-jwt/src/JWT.php";
require_once "libs/php-jwt/src/Key.php";
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Получаем данные от клиента.
$data = json_decode(file_get_contents("php://input"));
if (!isset($data->email) || !isset($data->password) || !isset($data->jwt)) {
    http_response_code(422);
    echo json_encode(array("message" => "Недостаточно параметров."));
    exit;
}

$user->email = $data->email;
$email_exists = $user->emailExists();

if ($email_exists && password_verify($data->password, $user->password)) {
    $jwt = $data->jwt ?? '';
    if ($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

            // Проверка на то, что пользователь использует jwt-ключ от своего емейла.
            if ($decoded->data->email != $user->email) {
                http_response_code(401);
                echo json_encode(array("message" => "Несоответствие jwt-ключа и адреса электронной почты."));
                exit;
            }

            $user->id = $decoded->data->id;

            if ($user->delete()) {
                http_response_code(200);
                echo json_encode(
                    array(
                        "message" => "Пользователь был удалён."
                    )
                );
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Невозможно удалить пользователя."));
            }
        } catch (Exception $e) {
            // Если декодирование не удалось, значит, что JWT недействителен.
            http_response_code(401);
            echo json_encode(array(
                "message" => "Доступ запрещён.",
                "error" => $e->getMessage()
            ));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Доступ запрещён."));
    }
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Доступ запрещён."));
}