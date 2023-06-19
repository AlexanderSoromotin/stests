<?php

include_once "includes.php";


class Room {

	public function test ($test) {
		openConnection('findcreek_mate');

		global $connection_mate;
		
		if ($test == "") {
			return formulateError(4, "Invalid test");
		}

		return formulateResponse(1);		
	}













	public static function getAll ($userID) {
		openConnection('stests');
		global $connection;

		$result = mysqli_query($connection, "
        select t.*, s.name 
        from test t 
        join subject s on t.subject_id = s.id;");

		$output = [];
        $testsIDs = [];
        while ($item = mysqli_fetch_assoc($result)) {
            $item["id"] = (int) $item["id"];
            $item["time_limit"] = (int) $item["time_limit"];
            $item["subject_id"] = (int) $item["subject_id"];
            $item["completed"] = 0;

            $testsIDs[] = $item["id"];
            $output[] = $item;
        }

        $results = mysqli_query($connection, "select * from result where test_id in (" . implode(",", $testsIDs) . ") and user_id = $userID");
        while ($item = mysqli_fetch_assoc($results)) {
            foreach ($output as $key => $value) {
                if ($value["id" == $item["test_id"]]) {
                    $item["completed"] = 1;
                }
            }

            $testsIDs[] = $item["id"];
            $output[] = $item;
        }

		return formulateResponse($output);		
	}




    public static function getInfo ($roomID) {
        openConnection('stests');
        global $connection;

        $roomData = mysqli_query($connection, "
            select r.*, u.name as room_admin_name, u.surname as room_admin_surname, u.patronymic as room_admin_patronymic
            from room r
            join room_owner ro on ro.room_id = r.id
            join user u on ro.user_id = u.id
            where r.id = $roomID;
        ");

        if ($roomData->num_rows == 0) {
            return formulateResponse([]);
        }

        $roomData = mysqli_fetch_assoc($roomData);
        $roomData["id"] = (int) $roomData["id"];

        $users = mysqli_query($connection, "
            select ur.room_id, u.login as user_login, u.id as user_id, u.name as user_name, u.surname as user_surname, u.patronymic as user_patronymic
            from user_room ur
            join user u on ur.user_id = u.id
            where ur.room_id = 1;
        ");

        $roomData["invitation_code"] = simple_encrypt("room_" . $roomData["id"]);

        $output = [
            "room_data" => $roomData,
            "users" => [],
            "users_number" => 0
        ];
        while ($item = mysqli_fetch_assoc($users)) {
            $item["user_id"] = (int) $item["user_id"];

            $output["users"][] = $item;
            $output["users_number"]++;
        }

        return formulateResponse($output);
    }












}





