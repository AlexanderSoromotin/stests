<?php

include_once "includes.php";


class Auth {

	public static function test ($test) {
		openConnection('stests');

		global $connection;
		
		if ($test == "") {
			return formulateError(4, "Invalid test");
		}

		return formulateResponse(1);		
	}







    public static function register ($name, $surname, $patronymic, $login, $password) {
        openConnection('stests');

        global $connection;

        if ($name == "") {
            return formulateError(4, "Invalid name", 100);
        }
        if ($surname == "") {
            return formulateError(4, "Invalid surname", 100);
        }
        if ($patronymic == "") {
            return formulateError(4, "Invalid patronymic", 100);
        }
        if ($login == "") {
            return formulateError(4, "Invalid login", 100);
        }
        if ($password == "") {
            return formulateError(4, "Invalid password", 100);
        }

        $checkLogin = mysqli_query($connection, "select id from user where login = '$login'");

        if ($checkLogin->num_rows != 0) {
            return formulateError(4, "login occupied", 100);
        }

        $password = md5($password . "key");
        $token = "findcreek_is_the_best";

        $result = mysqli_query($connection, "insert into user 
            (name, surname, patronymic, login, password, token, role_id) 
            values 
            ('$name', '$surname', '$patronymic', '$login', '$password', '$token', 1)");

        if ($result) {
            $newUserID = (int) mysqli_fetch_assoc(mysqli_query($connection, "select id from user order by id desc limit 1"))["id"];
            $token = encrypt($newUserID . "_student", "key");

            mysqli_query($connection, "update user set token = '$token' where id = $newUserID");

            return formulateResponse($token);
        } else {
            return formulateResponse(0);
        }




    }









	public static function getToken ($login, $password) {
		openConnection("stests");

		global $connection;

        $password = md5($password . "key");

        $result = mysqli_query($connection, "select * from user where login = '$login' and password = '$password'");

        if ($result->num_rows == 0) {
            return formulateError(4, "Incorrect login data", 100);
        }

        $result = mysqli_fetch_assoc($result);
        if ($result["login"] != $login or $result["password"] != $password) {
            return formulateError(3, "Прочь, SQL инъекция!");
        }

		return formulateResponse($result["token"]);
	}












	public function getInfo ($userID, $fields) {
		openConnection('findcreek_mate');
		openConnection('findcreek_id');

		global $connection_mate;
		global $connection_findcreekId;
		$id_account = new id_account;

		if ($userID == "") {
			return formulateError(4, "Invalid userID");
		}

		if ($fields == "") {
			$fieldsFromMate = $this->userDataFields;

		} else {
			$fields = convertStringToArray($fields, ",", 1);
			$fieldsFromMate = array_intersect($fields, $this->userDataFields);

			if (count($fieldsFromMate) == 0) {
				return formulateError(4, "Invalid fields", 100);
			}
		}

		// return formulateResponse("SELECT " . implode(',', $fieldsFromMate) . " FROM user WHERE findcreekID = $userID");


		$result = mysqli_query($connection_mate, "SELECT " . implode(',', $fieldsFromMate) . " FROM user WHERE findcreekID = $userID");

		if ($result->num_rows == 0) {
			// return formulateError(4, "Invalid fields");
			return formulateResponse([]);
		}

		$output = array();
		$result = mysqli_fetch_assoc($result);

		$jsonFields = ['address', 'online', 'roles', 'contacts', 'specialties', 'subscriptions', 'subscribers', 'projectsManagement'];
		$intFields = ['findcreekID', 'subscriptionsNumber', 'subscribersNumber'];

		foreach ($fieldsFromMate as $fieldName) {
			if (in_array($fieldName, $jsonFields)) {
				$output[$fieldName] = json_decode($result[$fieldName], 1);
				continue;
			}
			if (in_array($fieldName, $intFields)) {
				$output[$fieldName] = (int) $result[$fieldName];
				continue;
			}
			$output[$fieldName] = $result[$fieldName];
		}

		if (in_array("contacts", $fieldsFromMate)) {
			foreach ($output['contacts']['socialNetworks'] as $key => $socialNetwork) {
				$output['contacts']['socialNetworks'][$key]['logo'] = '';
				if ($socialNetwork['user'] != '') {
					$output['contacts']['socialNetworks'][$key]['logo'] = 'https://findcreek.com/assets/img/social_networks/' . $socialNetwork['service'] . '.png';
				}
			}
		}

		if (in_array("specialties", $fieldsFromMate)) {

            $outputSpecialties = [];
            if (count($output['specialties']) != 0) {
                $specialtiesData = mysqli_query($connection_mate, "SELECT * FROM specialty WHERE id IN (" . implode(',', $output['specialties']) . ")");

                while ($item = mysqli_fetch_assoc($specialtiesData)) {
                    $outputSpecialties[] = [
                        "id" => (int) $item['id'],
                        "rusName" => $item['rusName'],
                        "engName" => $item['engName']
                    ];
                }
            }

			$output['specialties'] = $outputSpecialties;
		}

		if (in_array("address", $fieldsFromMate)) {

			$countryID = $output['address']['countryID'];
			$regionID = $output['address']['regionID'];
			$cityID = $output['address']['cityID'];

			$address = [
				"countryID" => $countryID,
				"countryRusName" => "",
				"countryEngName" => "",
				"regionID" => $regionID,
				"regionRusName" => "",
				"regionEngName" => "",
				"cityID" => $cityID,
				"cityRusName" => "",
				"cityEngName" => "",
			];

			if ($countryID != 0) {
				$countryData = mysqli_fetch_assoc(mysqli_query($connection_findcreekId, "SELECT * FROM country WHERE id = $countryID"));
				$address['countryRusName'] = $countryData['rusName'];
				$address['countryEngName'] = $countryData['engName'];
				// $address['countryEngName'] = translitText($countryData['rusName']);
			}

			if ($regionID != 0) {
				$regionData = mysqli_fetch_assoc(mysqli_query($connection_findcreekId, "SELECT * FROM region WHERE id = $regionID;"));
				$address['regionRusName'] = $regionData['rusName'];
				$address['regionEngName'] = translitText($regionData['rusName']);
				// $address['countryEngName'] = translitText($countryData['rusName']);
			}

			if ($cityID != 0) {
				$cityData = mysqli_fetch_assoc(mysqli_query($connection_findcreekId, "SELECT * FROM city WHERE id = $cityID"));
				$address['cityRusName'] = $cityData['rusName'];
				$address['cityEngName'] = translitText($cityData['rusName']);
				// $address['countryEngName'] = translitText($countryData['rusName']);
			}

			$output['address'] = $address;
		}

		return formulateResponse($output);		
	}












}





