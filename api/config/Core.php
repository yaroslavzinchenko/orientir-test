<?php

// Показ сообщений об ошибках.
error_reporting(E_ALL);

date_default_timezone_set("Europe/Moscow");

// Переменные, используемые для JWT.
$key = "key secret"; // Здесь может быть уникальная случайно сгенерированная строка.
$iss = "http://localhost";
$aud = "http://localhost";
$iat = time();
$nbf = time();
$exp = $iat + 3600;