<?php

require_once('includes/api_headers.php');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
    exit;
}

require_once('includes/connect_to_db.php');

// Подключение файлов JWT.
require_once "config/Core.php";
require_once "libs/php-jwt/src/BeforeValidException.php";
require_once "libs/php-jwt/src/ExpiredException.php";
require_once "libs/php-jwt/src/SignatureInvalidException.php";
require_once "libs/php-jwt/src/JWT.php";
use \Firebase\JWT\JWT;

// Получаем данные от клиента.
$data = json_decode(file_get_contents("php://input"));
if (!isset($data->email) || !isset($data->password)) {
    http_response_code(422);
    echo json_encode(array("message" => "Недостаточно параметров."));
    exit;
}

$user->email = $data->email;
$email_exists = $user->emailExists();

if ($email_exists && password_verify($data->password, $user->password)) {
    $token = array(
        "iss" => $iss,
        "aud" => $aud,
        "iat" => $iat,
        "nbf" => $nbf,
        "exp" => $exp,
        "data" => array(
            "id" => $user->id,
            "email" => $user->email
        )
    );

    $jwt = JWT::encode($token, $key, 'HS256');

    http_response_code(200);
    echo json_encode(
        array(
            "message" => "Успешный вход в систему.",
            "jwt" => $jwt
        )
    );
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Ошибка входа"));
}