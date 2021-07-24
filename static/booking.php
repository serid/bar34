<?php
function send_message($text) {
    file_put_contents("./lib/tg/message.txt", $text);
    $command = "cd lib/tg; ./a.out 415280808";
    $_ = shell_exec($command);
}

function localize_people($num) {
    if ($num == 1) {
        return "человек";
    } elseif ($num < 5) {
        return "человека";
    } elseif ($num < 10) {
        return "человек";
    } elseif ($num == 10) {
        return "человек";
    } elseif ($num > 10) {
        // unreachable
        return "человек";
    }
}

$body = file_get_contents('php://input');

send_message($body);

$data = json_decode(file_get_contents('php://input'), true);

$time = $data["time"];
$numberOfPeople = $data["numberOfPeople"];
$name = $data["name"];
$phone = $data["phone"];
$user_message = $data["user_message"];

$numberOfPeople = strval($numberOfPeople) . " " . localize_people($numberOfPeople);

$text = "Новое бронирование\n" .
    "$numberOfPeople в $time, $name ($phone)\n" .
    "Пожелания: $user_message";

send_message($text);
?>