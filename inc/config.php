<?php

include_once 'secret.php';

// Список переменных в сикрете
// $db_password, $localSite, $JWTKey, $passwordSalt

$link = "http://s-tests.findcreek.com";
$localSiteLink = "http://s-tests.com";


// Версия основного css-файла 
$cssVer = '?v=8';

$icons = "https://findcreek.com/assets/img/icons/";

// Подключения
$connection;

// Данные для подключений
$dbConnectParams = [
	"stests" => [
		"db_url" => "localhost",
		"db_username" => "findcreek",
		"db_password" => $db_password,
		"db_name" => "stests"
	]
];

if ($localSite) {
	$dbConnectParams['stests']['db_url'] = "127.0.0.1";
	$dbConnectParams['stests']['db_username'] = "root";
	$dbConnectParams['stests']['db_password'] = "";


	$link = $localSiteLink;	
}

function openConnection ($systemName) {
	global $connection, $dbConnectParams;

	if ($systemName == 'stests') {
		if ($connection->server_info != "") {
			return;
		}
		$connection = mysqli_connect(
			$dbConnectParams[$systemName]['db_url'], 
			$dbConnectParams[$systemName]['db_username'], 
			$dbConnectParams[$systemName]['db_password'], 
			$dbConnectParams[$systemName]['db_name']
		);

		$connection->set_charset("utf8mb4");
	}
}

function closeConnection ($systemName) {
	global $connection;

	if ($systemName == 'stests') {
		mysqli_close($connection);
	}
}
