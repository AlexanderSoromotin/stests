<?php

include_once "../includes.php";

convertGetToPost();



$response = Auth::register($_POST["name"], $_POST["surname"], $_POST["patronymic"], $_POST["login"], $_POST["password"]);
echoJSON($response);

// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>