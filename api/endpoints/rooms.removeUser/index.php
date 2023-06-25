<?php

include_once "../includes.php";

convertGetToPost();


// Проверка валидности токена + расшифровка
$tokenData = explode(".", decrypt($_POST["token"], "key"));
$userID = (int) $tokenData[0];

$response = Room::removeUser($userID, (int) $_POST["room_id"], $_POST["user_id"]);
echoJSON($response);

// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>