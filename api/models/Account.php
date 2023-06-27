<?php

include_once "includes.php";


class Account {

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





