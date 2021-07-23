<?php
function send_message($text) {
    file_put_contents("./lib/tg/message.txt", $text);
    $command = "cd lib/tg; ./a.out 415280808";
    $_ = shell_exec($command);
}

$body = file_get_contents('php://input');

send_message($body);

$data = json_decode(file_get_contents('php://input'), true);

$time = $data["time"];
$name = $data["name"];
$phone = $data["phone"];
$user_message = $data["user_message"];

$text = "Новое бронирование\n" .
    "В $time, $name ($phone)\n" .
    "Пожелания: $user_message";

send_message($text);
?>