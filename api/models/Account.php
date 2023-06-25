<?php

include_once "includes.php";


class Account {
	// Поля, которые может получить ползователь о своём аккаунте
	public $dbFields = [
        'findcreekID',
        'roles',
        'address',
        'online',
        'contacts',
        'bio',
        'profileCover',
        'specialties',
        'skills',
        'subscriptions',
        'subscriptionsNumber',
        'subscribers',
        'subscribersNumber',
        'projectsManagement'
    ];






	public static function test ($test) {
		openConnection('stests');

		global $connection;
		
		if ($test == "") {
			return formulateError(4, "Invalid test");
		}

		return formulateResponse(1);
	}






    public static function getRooms ($userID) {
        openConnection('stests');

        global $connection;

        $ownRooms = mysqli_query($connection, "
            select ro.*, r.name, (SELECT COUNT(*) FROM user_room WHERE room_id = ro.room_id) AS users 
            from room_owner ro
            join room r on r.id = ro.room_id
            where user_id = $userID order by id desc;");

        $userName = mysqli_fetch_assoc(mysqli_query($connection, "select name, surname, patronymic from user where id = $userID"));

        $output = [
            "own" => [],
            "joined" => [],
            "user_name" => $userName
        ];

        while ($item = mysqli_fetch_assoc($ownRooms)) {
            $item["id"] = (int) $item["id"];
            $item["user_id"] = (int) $item["user_id"];
            $item["room_id"] = (int) $item["room_id"];

            $output["own"][] = $item;
        }

        $joinedRooms = mysqli_query($connection, "
            SELECT ur.*, r.name as room_name, u.name as room_admin_name, u.surname as room_admin_surname, u.patronymic as room_admin_patronymic,
                   (SELECT COUNT(*) FROM user_room WHERE room_id = ur.room_id) AS users
            FROM user_room ur
            JOIN room r ON r.id = ur.room_id
            JOIN room_owner ro ON r.id = ro.room_id
            JOIN user u ON u.id = ro.user_id
            WHERE ur.user_id = $userID;
");

        while ($item = mysqli_fetch_assoc($joinedRooms)) {
            $item["id"] = (int) $item["id"];
            $item["user_id"] = (int) $item["user_id"];
            $item["room_id"] = (int) $item["room_id"];

            $output["joined"][] = $item;
        }

        return formulateResponse($output);
    }









	public static function setInfo ($userID, $data) {
		openConnection('findcreek_mate');

		global $connection;
		global $cloudUrl;

		$id_account = new id_account;
		$mate_account = new mate_account;
		$id_auth = new id_auth;
		
		if ($userID == "") {
			return formulateError(4, "Invalid userID");
		}

		$address = $data['address'];
		$email = $data['email'];
		$social = $data['social'];
		$bio = addslashes($data['bio']);
		$profileCover = $data['profileCover'];
		$skills = addslashes($data['skills']);

		$userToken = $data['token'];

		$userData = $mate_account->getInfo($userID, 'address,bio,profileCover,contacts')['response'];

		if (!$userData) {
			return formulateError(5, "Something is wrong");
			// recordLog(1, 0, array(
			// 	"action" => "change_profile_info",
			// 	"textDescription" => "Failed. Failed to get user data? but token conofirmed.",
			// 	"jsonDescription" => array(),
			// 	"method" => "account.setInfo"
			// ), 3);
			// exit();
		}

		if ($address != "" and $address != $userData['address']['address']) {
			if (gettype($address) == "string") {
				$address = json_decode($address, 1);
			}
			$parsedAddress = json_encode([
				"countryID" => (int) $address['countryID'],
				"regionID" => (int) $address['regionID'],
				"cityID" => (int) $address['cityID']
			]);

			mysqli_query($connection, "UPDATE user SET address = '$parsedAddress' WHERE findcreekID = $userID");
		}

		if ($email != "" and $email != $userData['contacts']['email']) {
			
			$userData['contacts']['email'] = $email;
			$encodedContacts = json_encode($userData['contacts'], JSON_UNESCAPED_UNICODE);

			mysqli_query($connection, "UPDATE user SET contacts = '$encodedContacts' WHERE findcreekID = $userID");
		}

		if ($social != "" and $social != $userData['contacts']['social']) {
			$newSocial = [];
			foreach ($social as $key => $value) {
				if (gettype($key) != "string" or gettype($value) != "string") {
					continue;
				}
				if (strlen($key) >= 35 or strlen($value) >= 35) {
					continue;
				}
				$newSocial[$key] = $value;
			}

			if ($newSocial != []) {
				$userData['contacts']['social'] = $newSocial;

				$encodedContacts = json_encode($userData['contacts'], JSON_UNESCAPED_UNICODE);

				mysqli_query($connection, "UPDATE user SET contacts = '$encodedContacts' WHERE findcreekID = $userID");
			}
		}

		if ($bio != "" and $bio != $userData['bio']) {
			if ($bio == " ") {
				$bio = "";
			}
			mysqli_query($connection, "UPDATE user SET bio = '$bio' WHERE findcreekID = $userID");
		}

		if ($profileCover != "") {
			$profileCover = (int) $profileCover;

			$fileInfo = sendGetRequest($cloudUrl . "/methods/cloud.getFilesInfo/", ["token" => urlencode($userToken), "filesIDs" => $profileCover]);

			// return formulateError(5, $fileInfo);

			if (!$fileInfo['response']) {
				return formulateError(4, "Invalid avatarImage. File not found");
				// recordLog(1, $userID, array(
				// 	"action" => "change_profile_info",
				// 	"textDescription" => "Success. Try to change avatar, but avatar image just not found.",
				// 	"jsonDescription" => array("firstName" => $firstName, "lastName" => $lastName, "patronymic" => $patronymic, "textID" => $textID, "avatarImage" => $avatarImage),
				// 	"method" => "account.setInfo"
				// ), 1);
				// exit();
			}
			$fileInfo = $fileInfo['response'][0];

			if ((int) $fileInfo['ownerID'] != $userID) {
				return formulateError(4, "Invalid avatarImage. User is not the file owner");
				// recordLog(1, $userID, array(
				// 	"action" => "change_profile_info",
				// 	"textDescription" => "Success. Try to change avatar, but user is not file owner.",
				// 	"jsonDescription" => array("firstName" => $firstName, "lastName" => $lastName, "patronymic" => $patronymic, "textID" => $textID, "avatarImage" => $avatarImage),
				// 	"method" => "account.setInfo"
				// ), 1);
				// exit();
			}

			$profileCover = $fileInfo['additionalData']['urlToFile'];

			mysqli_query($connection, "UPDATE user SET profileCover = '$profileCover' WHERE findcreekID = $userID");
		}

		if ($skills != "" and $skills != $userData['skills']) {
			if ($skills == " ") {
				$skills = "";
			}

			mysqli_query($connection, "UPDATE user SET skills = '$skills' WHERE findcreekID = $userID");
		}

		// recordLog(1, $userID, array(
		// 	"action" => "change_profile_info",
		// 	"textDescription" => "Success",
		// 	"jsonDescription" => array("firstName" => $firstName, "lastName" => $lastName, "patronymic" => $patronymic, "textID" => $textID, "avatarImage" => $avatarImage),
		// 	"method" => "account.setInfo"
		// ), 1);

		return formulateResponse(1);		
	}












	public static function getInfo ($userID) {
		openConnection('stests');

		global $connection;


		$result = mysqli_query($connection, "
            select u.* , r.name as role_name
            from user u
            join role r on u.role_id = r.id
            where u.id = $userID");

		if ($result->num_rows == 0) {
			return formulateResponse([]);
		}

		$output = [];
		$result = mysqli_fetch_assoc($result);

        $result["id"] = (int) $result["id"];
        $result["role_id"] = (int) $result["role_id"];
        $output[] = $result;

		return formulateResponse($output);
	}







}





