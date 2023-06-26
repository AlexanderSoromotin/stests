<?php

include_once "includes.php";

require_once '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
where ro.user_id = $userID order by t.id asc;
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
        select t.*, (select count(*) from result where test_id = t.id and user_id = $userID) AS attempts_spent, r.name as room_name, (select count(*) from question where test_id = t.id) as questions_number
        from test t
        join user_room ur on ur.room_id = t.room_id
        join room r on r.id = ur.room_id
        where ur.user_id = $userID and DATE(t.available_date) = CURDATE() order by t.id asc;
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
            select r.*, t.name as test_name, t.description as test_description, room.name as room_name, t.time_limit as test_time_limit            
            from result r
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



    public static function updateTest (int $testID, $name, $description, $timeLimit, $attempts, $date, $roomID, $userID) {
        openConnection('stests');
        global $connection;

        $date .= " 00:00:00";

        mysqli_query($connection, "
        update test
        set name = '$name',
            description = '$description',
            time_limit = $timeLimit,
            attempts = $attempts,
            available_date = '$date',
            room_id = $roomID
        
        where id = $testID");

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



    public static function getColumnIndex ($index)
    {
        $base = ord('A');
        $column = '';

        while ($index > 0) {
            $index--;
            $column = chr($index % 26 + $base) . $column;
            $index = intval($index / 26);
        }

        return $column;
    }

    public static function makeReport ($testsIDs, $userID) {
        openConnection('stests');
        global $connection, $link;

        $testsIDs = convertStringToArray($testsIDs, ",", 1);

        if (count($testsIDs) == 0) {
            return formulateResponse(0);
        }

        $testID = $testsIDs[0];

        $roomID = (int) mysqli_fetch_assoc(mysqli_query($connection, "
        select r.id
        from test t
        join room r on t.room_id = r.id
        where t.id = $testID
        "))["id"];

        $users = mysqli_query($connection, "
        select u.id, u.name, u.surname, u.patronymic, u.login
        from user_room ur
        join user u on ur.user_id = u.id
        where ur.room_id = $roomID
        ");

        $output = [
            "users" => []
        ];

        while ($item = mysqli_fetch_assoc($users)) {
            $item["id"] = (int) $item["id"];
            $item["results"] = [];
            $output["users"][$item["id"]] = $item;
        }

        $tests = mysqli_query($connection, "
        select * 
        from test where id IN (" . implode(",", $testsIDs) . ")
        ");

        while ($item = mysqli_fetch_assoc($tests)) {
            $item["id"] = (int) $item["id"];
            foreach ($output["users"] as $key => $value) {
                $output["users"][$key]["results"][$item["id"]] = [
                    "test_id" => $item["id"],
                    "name" => $item["name"],
                    "description" => $item["description"],
                    "attempts" => (int) $item["attempts"],
                    "attempts_spent" => 0,
                    "time_limit" => (int) $item["time_limit"],
                    "time_spent" => 0,
                    "room_id" => (int) $item["room_id"],
                    "available_date" => $item["available_date"],
                    "score" => 0
                ];
            }
        }

        $results = mysqli_query($connection, "
        select *
        from result
        where test_id in (" . implode(",", $testsIDs) . ")
        ");

        while ($item = mysqli_fetch_assoc($results)) {
            $item["id"] = (int) $item["id"];
            $item["user_id"] = (int) $item["user_id"];
            $item["score"] = (int) $item["score"];
            $item["test_id"] = (int) $item["test_id"];
            $item["details"] = json_decode($item["details"], 1);

            $attempts = mysqli_query($connection, "
            select id
            from result
            where test_id = $item[test_id] and user_id = $item[user_id]
            ");

            $output["users"][$item["user_id"]]["results"][$item["test_id"]]["score"] += $item["score"];
            $output["users"][$item["user_id"]]["results"][$item["test_id"]]["time_spent"] = $item["details"]["time_spent"];
            $output["users"][$item["user_id"]]["results"][$item["test_id"]]["attempts_spent"] = $attempts->num_rows;
        }

//        return formulateResponse($output);




        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ФИО
        $sheet->mergeCells("A2:D2");
        $sheet->setCellValue("A2", "ФИО");

        for ($i = 0; $i < count($output["users"]); $i++) {
            $t = $i + 3;
            $sheet->mergeCells("A$t:D$t");
        }

        $col = 6;
        foreach (end($output["users"])["results"] as $key => $value) {
            // Название теста
            $sheet->mergeCells(self::getColumnIndex($col) . "1:" . self::getColumnIndex($col + 5) . "1");

            // Время прохождения
            $sheet->mergeCells(self::getColumnIndex($col) . "2:" . self::getColumnIndex($col + 1) . "2");

            // Превышение времени
//            $sheet->mergeCells(self::getColumnIndex($col + 4) . "2:" . self::getColumnIndex($col + 5) . "2");

            $sheet->setCellValue(self::getColumnIndex($col) . "1", $value["name"]);
            $sheet->setCellValue(self::getColumnIndex($col) . "2", "Время прохождения");
            $sheet->setCellValue(self::getColumnIndex($col + 2) . "2", "Оценка");
            $sheet->setCellValue(self::getColumnIndex($col + 3) . "2", "Попыток");
//            $sheet->setCellValue(self::getColumnIndex($col + 4) . "2", "Превышение времени");

            $col += 7;
        }


        $row = 3;
        foreach ($output["users"] as $key => $value) {
            // ФИО
            $sheet->setCellValue("A$row", "$value[surname] $value[name] $value[patronymic]");
            $sheet->mergeCells("A$row:D$row");

            $col = 6;
            foreach ($value["results"] as $resultID => $result) {
                if ($result["attempts_spent"] != 0) {
                    $result["score"] = floor($result["score"] / $result["attempts_spent"]);
                }
                $timeSpentSecs = $result["time_spent"] & 60;
                $timeSpentMins = floor($result["time_spent"] / 60);

                if ($timeSpentSecs <= 9) {
                    $timeSpentSecs = "0" . $timeSpentSecs;
                }
                if ($timeSpentMins <= 9) {
                    $timeSpentMins = "0" . $timeSpentMins;
                }

                $outTimeSpent = "$timeSpentMins:$timeSpentSecs";
                $outAttempts = "$result[attempts_spent] из $result[attempts]";
                $outScore = $result["score"];

                if ($result["attempts_spent"] == 0) {
                    $outTimeSpent = "не пройден";
                }

                if ($result["time_limit"] == 0) {
                    $outTimeSpent = "без таймера";
                }

                // Время прохождения
                $sheet->mergeCells(self::getColumnIndex($col) . "$row:" . self::getColumnIndex($col + 1) . "$row");
                $sheet->setCellValue(self::getColumnIndex($col) . "$row", $outTimeSpent);

                $sheet->setCellValue(self::getColumnIndex($col + 2) . "$row", $outScore);
                $sheet->setCellValue(self::getColumnIndex($col + 3) . "$row", $outAttempts);

                // Превышение времени
//                $sheet->mergeCells(self::getColumnIndex($col + 4) . "$row:" . self::getColumnIndex($col + 5) . "$row");
//                $sheet->setCellValue(self::getColumnIndex($col + 4) . "$row", "на 00:12");

                $col += 7;
            }
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
//        $fileName = $output["room_name"] . " - " . $output["test_name"] . ' - ' . date("d.m.Y H-i-s") . '.xlsx';
        $fileName = date("d.m.Y H-i-s") . ".xlsx";
        $writer->save('../../../storage/' . $fileName);

//        return formulateResponse($output);
        return formulateResponse("$link/storage/" . $fileName);
    }








    public static function create (int $userID) {
        openConnection('stests');
        global $connection;

        $roomID = (int) mysqli_fetch_assoc(mysqli_query($connection, "
        select r.id
        from user u
        join room_owner ro on ro.user_id = u.id
        join room r on r.id = ro.room_id
        where u.id = $userID order by id desc
        limit 1
        "))["id"];

        if ($roomID == 0) {
            return formulateResponse(0);
        }

        mysqli_query($connection, "
            insert into test 
            (name, description, attempts, time_limit, room_id)
            values
            ('Новый тест', 'Описание теста', 1, 0, $roomID)");

        return formulateResponse(1);
    }

}





