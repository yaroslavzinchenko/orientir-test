<?php

require_once('includes/api_headers.php');

/*header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: multipart/form-data;");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");*/

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
    exit;
}

require_once('includes/connect_to_db.php');

// Получаем данные от клиента.
$data = json_decode(file_get_contents("php://input"));
/*$data = $_POST;
foreach ($_FILES as $key => $value) {
    if (pathinfo($value['name'], PATHINFO_EXTENSION) != 'jpeg' && pathinfo($value['name'], PATHINFO_EXTENSION) != 'jpg') {
        http_response_code(422);
        echo json_encode(array("message" => "Invalid file extension."));
        exit;
    }
    $avatar = file_get_contents($value['tmp_name']);
//    $avatar = base64_encode(file_get_contents($value['tmp_name']));
//    echo '<img src = "data:image/png;base64,' . base64_encode($avatar) . '"/>';exit;
}*/

if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(array("message" => "Incorrect email."));
    exit;
}
if (strlen($data->password) < 8) {
    http_response_code(422);
    echo json_encode(array("message" => "Password must be at least 8 characters long."));
    exit;
}
if (
    !isset($data->email)
    || !isset($data->password)
    || !isset($data->name)
    || !isset($data->surname)
    || !isset($data->secondname)
    || !isset($data->post)
    || !isset($data->birthday)
    || !isset($data->avatar)
) {
    http_response_code(422);
    echo json_encode(array("message" => "Incorrect parameters."));
    exit;
}

$user->email = $data->email;
$user->password = $data->password;
$user->name = $data->name;
$user->surname = $data->surname;
$user->secondname = $data->secondname;
$user->post = $data->post;
$user->birthday = $data->birthday;
$user->avatar = $data->avatar;
// Аватар в БД храним в сыром виде.
$user->avatar = base64_decode($user->avatar);

// Поверка на существование email в БД.
$email_exists = $user->emailExists();

// Функция для валидации введённой даты.
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

if (
    !empty($user->email)
    && !empty($user->password)
    && !empty($user->name)
    && !empty($user->surname)
    && !empty($user->secondname)
    && !empty($user->post)
    && !empty($user->avatar)
    && !empty($user->birthday)
    && validateDate($user->birthday)
    && !$email_exists
    && $user->create()) {
    http_response_code(200);
    echo json_encode(array("message" => "User was created."));
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user."));
}