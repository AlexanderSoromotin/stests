<?php

include_once "../includes.php";

convertGetToPost();


// Проверка валидности токена + расшифровка
$tokenData = explode(".", decrypt($_POST["token"], "key"));
$userID = (int) $tokenData[0];

$response = Test::addResult((int) $_POST["test_id"], $userID, $_POST["answers"], $_POST["time"]);
echoJSON($response);

// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>