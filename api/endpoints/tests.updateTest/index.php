<?php

include_once "../includes.php";

convertGetToPost();


// Проверка валидности токена + расшифровка
$tokenData = explode(".", decrypt($_POST["token"], "key"));
$userID = (int) $tokenData[0];

$response = Test::updateTest((int) $_POST["test_id"], $_POST["name"], $_POST["description"], (int) $_POST["time_limit"], (int) $_POST["attempts"], $_POST["date"], (int) $_POST["room_id"], $userID);
echoJSON($response);


// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>