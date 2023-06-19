<?php

include_once "../../inc/config.php";
include_once "../functions.php";
include_once "../../id/models.php"; // ID models

class id_example {

	public function test ($test) {
		openConnection('findcreek_id');
		global $connection_findcreekId;

		// Первоначальная проверка данных
		if ($test == '') {
			return formulateError(4, "Invalid test.");
		}
		

		
	}

	
}





