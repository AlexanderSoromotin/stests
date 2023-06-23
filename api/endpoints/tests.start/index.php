<?php

include_once "../includes.php";

convertGetToPost();


// Проверка валидности токена + расшифровка
$tokenData = explode(".", decrypt($_POST["token"], "key"));
$userID = (int) $tokenData[0];

$response = Test::start((int) $_POST["test_id"], $userID);
echoJSON($response);

// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>