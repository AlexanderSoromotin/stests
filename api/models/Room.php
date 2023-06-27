<?php

include_once "includes.php";


class Room {

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
            where ur.room_id = $roomID;
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

    public static function setInfo ($roomID, $name) {
        openConnection('stests');
        global $connection;

        mysqli_query($connection, "update room set name = '$name' where id = $roomID");

        return formulateResponse(1);
    }

    public static function removeUser ($userID, $roomID, $primaryUserID) {
        openConnection('stests');
        global $connection;

        mysqli_query($connection, "delete from user_room where user_id = $primaryUserID and room_id = $roomID");

        return formulateResponse(1);
    }

    public static function getInfoByInvitation ($userID, $invitationID) {
        openConnection('stests');
        global $connection;

        $invitationData = explode("_", simple_decrypt($invitationID));
        $roomID = (int) $invitationData[1];

        $roomData = mysqli_query($connection, "
            select r.*, u.name as room_admin_name, u.surname as room_admin_surname, u.patronymic as room_admin_patronymic, u.id as room_admin_id
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
            where ur.room_id = $roomID;
        ");

        $roomData["invitation_code"] = simple_encrypt("room_" . $roomData["id"]);

        $output = [
            "room_data" => $roomData,
            "users" => [],
            "users_number" => 0,
            "can_join" => 1
        ];

        if ((int) $output["room_data"]["room_admin_id"] == $userID) {
            $output["can_join"] = 0;
        }

        while ($item = mysqli_fetch_assoc($users)) {
            $item["user_id"] = (int) $item["user_id"];

            if ($item["user_id"] == $userID) {
                $output["can_join"] = 0;
            }

            $output["users"][] = $item;
            $output["users_number"]++;
        }

        return formulateResponse($output);
    }

    public static function join ($userID, $invitationID) {
        openConnection('stests');
        global $connection;

        $invitationData = explode("_", simple_decrypt($invitationID));
        $roomID = (int) $invitationData[1];

        $roomData = mysqli_query($connection, "
            select r.*, u.id as room_admin_id
            from room r
            join room_owner ro on ro.room_id = r.id
            join user u on ro.user_id = u.id
            where r.id = $roomID;
        ");

        if ($roomData->num_rows == 0) {
            return formulateResponse(0);
        }

        $roomData = mysqli_fetch_assoc($roomData);
        $roomData["id"] = (int) $roomData["id"];

        $users = mysqli_query($connection, "
            select ur.room_id, u.id as user_id
            from user_room ur
            join user u on ur.user_id = u.id
            where ur.room_id = $roomID;
        ");

        $canJoin = 1;
        $output = [
            "room_data" => $roomData,
        ];

        if ((int) $output["room_data"]["room_admin_id"] == $userID) {
            $canJoin = 0;
        }

        while ($item = mysqli_fetch_assoc($users)) {
            $item["user_id"] = (int) $item["user_id"];

            if ($item["user_id"] == $userID) {
                $canJoin = 0;
            }
        }

        if ($canJoin) {
            mysqli_query($connection, "insert into user_room (user_id, room_id) values ($userID, $roomID)");
        }

        return formulateResponse($canJoin);
    }

    public static function create ($userID) {
        openConnection('stests');
        global $connection;

        mysqli_query($connection, "
        insert into room
        values (null, 'Новая комната')
        ");

        $id = (int) mysqli_fetch_assoc(mysqli_query($connection, "select id from room where name = 'Новая комната' order by id desc limit 1"))["id"];

        if ($id != 0) {
            mysqli_query($connection, "
            insert into room_owner
            (user_id, room_id)
            values 
            ($userID, $id)
            ");
        }

        return formulateResponse(1);
    }
}





