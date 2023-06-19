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




    public static function getInfo ($testID) {
        openConnection('stests');
        global $connection;

        $result = mysqli_query($connection, "select t.*, s.name from test t join subject s on t.subject_id = s.id where t.id = $testID");

        $output = [];
        while ($item = mysqli_fetch_assoc($result)) {
            $item["id"] = (int) $item["id"];
            $item["time_limit"] = (int) $item["time_limit"];
            $item["subject_id"] = (int) $item["subject_id"];

            $output[] = $item;
        }

        return formulateResponse($output);
    }












}





