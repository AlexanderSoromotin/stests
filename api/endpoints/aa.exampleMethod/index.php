<?php 
include_once "../../inc/checkHeaders.php";
include_once "../../inc/config.php";
// include_once "../../inc/main.php";

include_once "../functions.php";

include_once "../models.php"; 			// Mate models
include_once "../../id/models.php"; 	// ID models

// openConnection('findcreek_id');
// openConnection('findcreek_mate');

convertGetToPost();


// $id_auth = new id_auth;
// $id_account = new id_account;
// $mate_account = new mate_account;


// Проверка валидности токена + расшифровка
// $tokenData = $id_auth->decodeToken($_POST['token'], 1)['response'];
// if ($tokenData['sessionValidity'] == 0) {
// 	printError(4, "Invalid token", 1);
// }
// $userID = $tokenData['data']['userID'];


// Сухая проверка валидности токена
// if (!$id_auth->checkToken($_POST['token'])['response']) {
// 	printError(4, "Invalid token", 1);
// }


$response = $mate_account -> getInfo($userID, $_POST['fields']);
echoJSON($response);


// closeConnection('findcreek_id');
// closeConnection('findcreek_mate');
?>