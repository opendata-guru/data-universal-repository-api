<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET');
	header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include('helper/_lobject.php');

	function repaireLObjects() {
		global $loadedLObjects;

		$ret = [];
		foreach($loadedLObjects as $object) {
			if (strlen($object['lid']) !== 5) {
				$ret[] = $object['lid'];
			} else if ($object['lid'][0] !== 'l') {
				$ret[] = $object['lid'];
			}
		}

		return $ret;
	}

	$test = repaireLObjects();

	echo json_encode((object) array(
		'test' => $test,
	));
?>