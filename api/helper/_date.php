<?php
	function getDateParameter() {
		$parameterDate = htmlspecialchars($_GET['date']);

		if ($parameterDate == '') {
			return null;
		} else if ($parameterDate == 'today') {
			$parameterDate = date('Y-m-d');
		} else if ($parameterDate == 'yesterday') {
			$parameterDate = date('Y-m-d', strtotime('-1 days'));
		}

		$dateArray = explode('-', $parameterDate);
		if (count($dateArray) != 3) {
			return null;
		}

		$year = $dateArray[0];
		if (strlen($year) != 4) {
			return null;
		}
		$year = intval($year);
		if (($year < 2000) || (2100 < $year)) {
			return null;
		}

		$month = $dateArray[1];
		if (strlen($month) != 2) {
			return null;
		}
		$month = intval($month);
		if (($month < 1) || (12 < $month)) {
			return null;
		}

		$day = $dateArray[2];
		if (strlen($day) != 2) {
			return null;
		}
		$day = intval($day);
		if (($day < 1) || (31 < $day)) {
			return null;
		}

		return $parameterDate;
	}
?>