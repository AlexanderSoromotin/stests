<?php

include_once "../includes.php";

convertGetToPost();


// Проверка валидности токена + расшифровка
$tokenData = explode(".", decrypt($_POST["token"], "key"));
$userID = (int) $tokenData[0];

$response = Room::setInfo((int) $_POST["room_id"], $_POST["name"]);
echoJSON($response);

// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>