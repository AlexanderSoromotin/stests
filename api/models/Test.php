<?php

include_once "includes.php";


class Test {

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

		$ownTests = mysqli_query($connection, "
select t.*, r.name as room_name, (select count(*) from question where test_id = t.id) as questions_number
from test t
join room_owner ro on ro.id = t.room_id
join room r on r.id = ro.room_id
where ro.user_id = $userID and t.deleted_at is null order by t.id desc;
        ");

		$output = [
            "own_tests" => [],
            "available_tests" => []
        ];

        while ($item = mysqli_fetch_assoc($ownTests)) {
            $item["id"] = (int) $item["id"];
            $item["time_limit"] = (int) $item["time_limit"];
            $item["attempts"] = (int) $item["attempts"];
            $item["room_id"] = (int) $item["room_id"];

            $output["own_tests"][] = $item;
        }

        $availableTests = mysqli_query($connection, "
        select t.*, (select count(*) from result where test_id = t.id) AS attempts_spent, r.name as room_name, (select count(*) from question where test_id = t.id) as questions_number
        from test t
        join user_room ur on ur.id = t.room_id
        join room r on r.id = ur.room_id
        where ur.user_id = $userID and t.deleted_at is null order by t.id desc;
        ");

        while ($item = mysqli_fetch_assoc($availableTests)) {
            $item["id"] = (int) $item["id"];
            $item["textID"] = urlencode(simple_encrypt($item["id"]));
            $item["time_limit"] = (int) $item["time_limit"];
            $item["attempts"] = (int) $item["attempts"];
            $item["room_id"] = (int) $item["room_id"];
            $item["attempts_spent"] = (int) $item["attempts_spent"];

            $output["available_tests"][] = $item;
        }

		return formulateResponse($output);		
	}




    public static function getInfo (int $testID, $userID = 0) {
        openConnection('stests');
        global $connection;

        $result = mysqli_query($connection, "
        select t.*, r.name as room_name, (select count(*) from question where test_id = t.id) as questions_number
        from test t
        join room r on r.id = t.room_id
        where t.id = $testID and t.deleted_at is null;");

        if ($result->num_rows == 0) {
            return formulateResponse(0);
        }

        $result = mysqli_fetch_assoc($result);
        $result["id"] = (int) $result["id"];
        $result["attempts"] = (int) $result["attempts"];
        $result["time_limit"] = (int) $result["time_limit"];
        $result["room_id"] = (int) $result["room_id"];
        $result["questions_number"] = (int) $result["questions_number"];

        return formulateResponse($result);
    }










    public static function start (int $testID, $userID) {
        openConnection('stests');
        global $connection;

        $result = mysqli_query($connection, "
        select q.*
        from user u
        join user_room ur on ur.user_id = u.id
        join test t on ur.room_id = t.room_id
        join question q on q.test_id = t.id
        where u.id = $userID and t.id = $testID;");

        if ($result->num_rows == 0) {
            return formulateResponse(0);
        }

        $output = [];

        while ($item = mysqli_fetch_assoc($result)) {
            $item["id"] = (int) $item["id"];
            $item["test_id"] = (int) $item["test_id"];
            $item["answers"] = json_decode($item["answers"], 1);
            $output[] = $item;
        }

        return formulateResponse($output);
    }





    public static function getQuestions (int $testID, $userID) {
        openConnection('stests');
        global $connection;

        $result = mysqli_query($connection, "
        select q.*
        from test t
        join question q on q.test_id = t.id
        where t.id = $testID;");

        if ($result->num_rows == 0) {
            return formulateResponse(0);
        }

        $output = [];

        while ($item = mysqli_fetch_assoc($result)) {
            $item["id"] = (int) $item["id"];
            $item["test_id"] = (int) $item["test_id"];
            $item["answers"] = json_decode($item["answers"], 1);
            $output[] = $item;
        }

        return formulateResponse($output);
    }





    public static function addResult (int $testID, int $userID, $encodedAnswers, int $time) {
        openConnection('stests');
        global $connection;

        $encodedAnswers = json_decode($encodedAnswers);
        $answers = [];
        foreach ($encodedAnswers as $key => $value) {
            $value = explode("_", $value);
            $answers[(int) $value[0]] = $value[1];
        }
        $totalQuestions = 0;

        $result = mysqli_query($connection, "
        select q.*
        from user u
        join user_room ur on ur.user_id = u.id
        join test t on ur.room_id = t.room_id
        join question q on q.test_id = t.id
        where u.id = $userID and t.id = $testID;");

        if ($result->num_rows == 0) {
            return formulateResponse(0);
        }

        $output = [
            "right_answers" => 0,
            "score" => 0,
            "time_spent" => $time
        ];

        while ($item = mysqli_fetch_assoc($result)) {
            $totalQuestions++;
            $item["id"] = (int) $item["id"];
            $item["test_id"] = (int) $item["test_id"];
            $item["answers"] = json_decode($item["answers"], 1);

            if ($item["answers"]["right_answer_number"] == $answers[$item["id"]]) {
                $output["right_answers"]++;
            }
        }

        $output["score"] = floor((100 / $totalQuestions) * $output["right_answers"]);

        $json = json_encode($output, JSON_UNESCAPED_UNICODE);

        mysqli_query($connection, "
            insert into result
            (user_id, score, details, test_id)
            values 
            ($userID, $output[score], '$json', $testID)
        ");

        return formulateResponse($output);
    }






    public static function getResults (int $userID) {
        openConnection('stests');
        global $connection;

        $result = mysqli_query($connection, "
            select r.*, t.name as test_name, t.description as test_description, room.name as room_name, t.time_limit as test_time_limit            from result r
            join test t on r.test_id = t.id
            join user u on r.user_id = u.id
            join room on t.room_id = room.id
            where u.id = $userID order by r.id desc;
        ");

        if ($result->num_rows == 0) {
            return formulateResponse([]);
        }

        $output = [];
        while ($item = mysqli_fetch_assoc($result)) {
            $item["id"] = (int) $item["id"];
            $item["user_id"] = (int) $item["user_id"];
            $item["score"] = (int) $item["score"];
            $item["test_id"] = (int) $item["test_id"];
            $item["test_time_limit"] = (int) $item["test_time_limit"];
            $item["details"] = json_decode($item["details"], 1);

            $output[] = $item;
        }

        return formulateResponse($output);
    }








    public static function updateQuestion (int $questionID, $title, $details, $userID) {
        openConnection('stests');
        global $connection;

//        $details = json_decode($details, 1);

        $details["mix_answers"] = (int) $details["mix_answers"];
        $details["right_answer_number"] = (int) $details["right_answer_number"];

        $details = json_encode($details, JSON_UNESCAPED_UNICODE);

        mysqli_query($connection, "update question set title = '$title', answers = '$details' where id = $questionID");

        return formulateResponse($details);
    }



    public static function updateTest (int $testID, $name, $description, $timeLimit, $roomID, $userID) {
        openConnection('stests');
        global $connection;

        mysqli_query($connection, "update test set name = '$name', description = '$description', time_limit = $timeLimit, room_ID = $roomID where id = $testID");

        return formulateResponse(1);
    }



    public static function addQuestion (int $testID, $userID) {
        openConnection('stests');
        global $connection;

        $answers = [
            "answers" => [
                1 => "",
                2 => "",
                3 => "",
                4 => ""
            ],
            "right_answer_number" => rand(1, 4),
            "mix_answers" => 1,
        ];

        $answers = json_encode($answers, JSON_UNESCAPED_UNICODE);

        mysqli_query($connection, "
            insert into question 
            (test_id, title, answers)
            values
            ($testID, '', '$answers')");

        $id = (int) mysqli_fetch_assoc(mysqli_query($connection, "select id from question where test_id = $testID order by id desc limit 1"))["id"];

        return formulateResponse($id);
    }

}





