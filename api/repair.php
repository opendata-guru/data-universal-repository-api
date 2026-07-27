<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET');
	header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include('helper/_database.php');
	include('helper/_lobject.php');

/*	function repaireLObjectsWithoutLID() {
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
	}*/

	function repaireLObjects() {
		global $loadedLObjects;

		$hashmap = [];
		foreach($loadedLObjects as $object) {
			$hash = $object['lid'];
			if (!array_key_exists($hash, $hashmap)) {
				$hashmap[$hash] = [];
			}
			$hashmap[$hash][] = $object;
		}

		$ret = [];
		foreach($hashmap as $item) {
			if (count($item) > 1) {
//				$ret[] = $item;
				$ret[] = $item[0]['lid'];
			}
		}

		return $ret;
	}

	function repairFileBackupToDatabase() {
		global $loadedLObjects;

		$startTime = microtime(true);
		$db = new GuruDatabase();

		echo "loadedLObjects: " . count($loadedLObjects) . "\n";

		foreach($loadedLObjects as $object) {
			$lid = $object['lid'];

			// strlen === 5? Yes, there is a current bug in saving data resulting in too long lid
			if (strlen($lid) === 5) {
				$pid = $object['pid'];
				$identifier = $object['identifier'];

				$lObjects = $db->getLObjectsByPIDIdentifier($pid, $identifier);

				if (0 === count($lObjects)) {
					$db->createLObject($lid, $pid, $identifier, $object['title'], $object['haspart'], $object['ispartof'], $object['sid'], $object['lastseen']);
				} else if (1 !== count($lObjects)) {
					var_dump('More than 1 lObject for ' . $identifier . ' in ' . $pid);
				} else {
					$dbObj = $lObjects[0];

					if ($dbObj->lid !== $lid) {
						var_dump('2 lids for ' . $identifier . ' in ' . $pid . ' (' . $dbObj->lid . ',' . $lid . ')');
//						var_dump($dbObj);
//						var_dump($object);
					}
				}
			}
		}

		$timeDiff = microtime(true) - $startTime;
		echo ('Duration: ' . (round($timeDiff * 1000) / 1000) . " seconds\n");
		$db->close();

		return 'repairFileBackupToDatabase';
	}

//	$test = repaireLObjects();
	$test = testDB();
	$test = repairFileBackupToDatabase();

	echo json_encode((object) array(
		'test' => $test,
	));
?>