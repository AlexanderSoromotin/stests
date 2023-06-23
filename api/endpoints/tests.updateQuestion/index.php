<?php

include_once "../includes.php";

convertGetToPost();


// Проверка валидности токена + расшифровка
$tokenData = explode(".", decrypt($_POST["token"], "key"));
$userID = (int) $tokenData[0];

$response = Test::updateQuestion((int) $_POST["question_id"], $_POST["title"], $_POST["details"], $userID);
echoJSON($response);

// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>