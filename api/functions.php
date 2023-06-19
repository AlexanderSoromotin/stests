<?php
$apiVersions = [1.01];

$findcreekUserAgentOptions = [
	'http' => [
    'method' => "GET",
    'header' => "Accept-language: en\r\n" .
              	"User-Agent: FindcreekBot (like TelegramBot)\r\n"
    ]
];
function simple_encrypt($string) {
    $encrypted = base64_encode(substr($string, 0, 10));
    return $encrypted;
}

function simple_decrypt($encrypted) {
    $decrypted = substr(base64_decode($encrypted), 0, 10);
    return $decrypted;
}

function encrypt ($data, $key) {
    $cipher = "AES-256-CBC";
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt ($data, $key) {
    $cipher = "AES-256-CBC";
    $data = base64_decode($data);
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    return openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);
}

$findcreekUserAgentContext = stream_context_create($findcreekUserAgentOptions);

// Проверка версии API
function checkApiVersion ($version, $mode) {
	// mode 1: Возврат true/false
	// mode 2: Если ошибка - вывод текста, иначе - true
	// mode 3: Если ошибка - вывод текста и завершение скрипта, иначе - true

	if ($version == "") {
		switch ($mode) {
			case 1:
				return false;
				break;
			
			case 2:
				returnError(4, "Invalid API version", 0);
				break;

			case 3:
				returnError(4, "Invalid API version", 1);
				break;
		}
	}

	global $apiVersions;

	if (!in_array($version, $apiVersions) and !in_array((double) $version, $apiVersions)) {
		switch ($mode) {
			case 1:
				return false;
				break;
			
			case 2:
				returnError(4, "Invalid API version", 0);
				break;

			case 3:
				returnError(4, "Invalid API version", 1);
				break;
		}
	}

	return true;
}

// Вывод текста, массивов, объектов и т.п
function echoJSON ($var, $exit = 0) {
	$var = json_encode($var, JSON_UNESCAPED_UNICODE);
	echo $var;

	if ($exit) {
		exit();
	}
}

function printError ($error_code, $error_msg, $exit, $error_subcode = 0) {
	$output = [];
	$output['error'] = [];
	$output['error']['error_code'] = $error_code;
	$output['error']['error_msg'] = $error_msg;
	$output['error']['error_subcode'] = $error_subcode;
	echoJSON($output);

	if ($exit) {
		exit();
	}
}

function refactorDate ($date) {
    global $serverTimeZone;

    // input 2023-04-23 05:45:59

    // "date": {
    //    "date": "26.03.2023 06:39:14",
    //    "unixTime": 1679801954,
    //    "timeZone": "+3"
    // },
    $timestamp = strtotime($date);
    $new_date_format = date('d.m.Y H:i:s', $timestamp);

    $output = [
        'date' => $new_date_format,
        'unixTime' => $timestamp,
        'timeZone' => $serverTimeZone
    ];

    return $output;
}

function refactorLimit ($limit) {
    if ($limit == '') {
        $limit = [0, 20];
    }
    if (gettype($limit) == 'string') {
        $limit = convertStringToArray($limit, ',', 1);
    }
    if (count($limit) != 2) {
        return formulateError(4, "Invalid limit", 100);
    }
    return implode(',', $limit);
}

function printResponse ($msg) {
	$output = array();
	$output['response'] = $msg;
	echoJSON($output);
}

function formulateError($error_code, $error_msg, $error_subcode = 0) {
	$output = [
		"error" => [
			"error_code" => $error_code,
			"error_msg" => $error_msg,
			"error_subcode" => $error_subcode
		]
	];
	
	return $output;
}

function formulateResponse($response) {
	$output = [
		"response" => $response
	];
	
	return $output;
}

function sendGetRequest ($url, $params) {
	// Отправка запросов 3.0

	global $findcreekUserAgentContext;
	$params_array = array();

	foreach ($params as $key => $value) {
		if (strpos($value, "://")) {
			$value = urlencode($value);
		}
		$params_array[] = $key . "=" . $value; 
	}

	return json_decode(file_get_contents($url . "?" . implode("&", $params_array), 0, $findcreekUserAgentContext), 1);
	// return ($url . "?" . implode("&", $params_array));
}

function convertGetToPost () {
	if ($_POST == []) {
		$_POST = $_GET;

	} else {
		foreach ($_GET as $key => $value) {
			if ($_POST[$key] == "") {
				$_POST[$key] = $value;
			}
		}
	}
}

$validCharacters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '_', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

function translitText ($str, $translitTo = 'eng') {

	if ($translitTo == 'eng') {
		$rus = array('кс', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    	$lat = array('x', 'A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', '', 'e', 'yu', 'ya');
		return str_replace($rus, $lat, $str);
	}

	$gost = [
		'A' => 'А', 'B' => 'Б', 'C' => 'С', 'D' => 'Д', 'E' => 'Е', 
		'F' => 'Ф', 'G' => 'ДЖ', 'H' => 'Х', 'I' => 'И', 'J' => 'Ж', 
		'K' => 'К', 'L' => 'Л', 'M' => 'М', 'N' => 'Н', 'O' => 'О', 
		'P' => 'P', 'Q' => 'К', 'R' => 'Р', 'S' => 'С', 'T' => 'Т', 
		'U' => 'Ю', 'V' => 'В', 'W' => 'В', 'X' => 'КС', 'Y' => 'Й', 
		'Z' => 'З',
		'a' => 'а', 'b' => 'б', 'c' => 'с', 'd' => 'д', 'e' => 'е', 
		'f' => 'ф', 'g' => 'дж', 'h' => 'х', 'i' => 'и', 'j' => 'ж', 
		'k' => 'к', 'l' => 'л', 'm' => 'м', 'n' => 'н', 'o' => 'о', 
		'p' => 'п', 'q' => 'к', 'r' => 'р', 's' => 'с', 't' => 'т', 
		'u' => 'ю', 'v' => 'в', 'w' => 'в', 'x' => 'кс', 'y' => 'й',
		'z' => 'з'
	];
	return strtr($str, $gost);
	
    
}

$trustedUserAgents = ["FINDCREEK Bot (Security system)", "FINDCREEK Bot (Parsing system)", "FindcreekBot (like TelegramBot)"];

function convertStringToArray ($string, $delemiter, $deleteSpaces, $toInt = 0) {
	if ($deleteSpaces) {
		$string = str_replace(' ', '', $string);
	}

    if ($string == '') {
        return [];
    }
	
	$fieldsArray = explode(',', $string);

	foreach ($fieldsArray as $key => $value) {
		if ($value == "") {
			unset($fieldsArray[$key]);
		}
		if ($toInt) {
			$fieldsArray[$key] = (int) $value;	
		}
	}
	return $fieldsArray;
}

function addElementToArray ($element, &$array) {
	if (!in_array($element, $array)) {
		$array[] = $element;
	}
}

function removeElementFromArray ($element, &$array) {
	foreach ($array as $key => $value) {
		if ($element == $value) {
			unset($array[$key]);
		}
	}
	$array = array_values($array);
}