<?php

include_once "../includes.php";

convertGetToPost();


//// Проверка валидности токена + расшифровка
//$tokenData = $id_auth->decodeToken($_POST['token'], 1)['response'];
//if ($tokenData['sessionValidity'] == 0) {
//	printError(4, "Invalid token", 1);
//}
//$userID = $tokenData['data']['userID'];

$response = Auth::getToken($_POST['login'], $_POST['password']);
echoJSON($response);

// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>