<?php

// Файлы, необходимые для подключения к базе данных.
include_once "./config/Database.php";
include_once "./models/User.php";

$database = new Database();
$user = new User($database->getConnection());