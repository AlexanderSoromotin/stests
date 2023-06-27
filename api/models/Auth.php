<?php

include_once "includes.php";


class Auth {

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
}





