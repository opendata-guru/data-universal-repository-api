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
		$countDBCreate = 0;
		$countDBUpdate = 0;
		$countDBUpdateSID = 0;
		$db = new GuruDatabase();

		echo "loadedLObjects: " . count($loadedLObjects) . "\n";

		foreach($loadedLObjects as $object) {
			$lid = $object['lid'];

			// strlen === 5? Yes, there is a current bug in saving data resulting in too long lid
			if (strlen($lid) === 5) {
				$pid = $object['pid'];
				$sid = $object['sid'];
				$lastseen = $object['lastseen'];
				$identifier = $object['identifier'];

				// save time factor 2.7
//				$lObjects = $db->getLObjectsByPIDIdentifier($pid, $identifier);
				$lObjects = $db->getLObjectsByPIDIdentifier_cache($pid, $identifier);

				if (0 === count($lObjects)) {
					$db->createLObject($lid, $pid, $identifier, $object['title'], $object['haspart'], $object['ispartof'], $sid, $lastseen);
					++$countDBCreate;
				} else if (1 !== count($lObjects)) {
					echo 'More than 1 lObject for ' . $identifier . ' in ' . $pid . "\n";
				} else {
					$dbObj = $lObjects[0];

					if ($dbObj->lid !== $lid) {
						echo '2 lid\'s for ' . $identifier . ' in ' . $pid . ' (' . $dbObj->lid . ',' . $lid . ")\n";
//						var_dump($dbObj);
//						var_dump($object);
					} else {
						if ($dbObj->lastseen < $lastseen) {
							$db->updateLObject($lid, $object['title'], $object['haspart'], $object['ispartof'], $lastseen);
							++$countDBUpdate;
						}
						if ($dbObj->sid !== $sid) {
							if ('' === $dbObj->sid) {
								$db->updateLObjectSID($lid, $sid);
								++$countDBUpdateSID;
							} else {
								echo '2 sid\'s for ' . $identifier . ' in ' . $pid . ' (' . $dbObj->sid . ',' . $sid . ")\n";
							}
						}
					}
				}
			}
		}

		$timeDiff = microtime(true) - $startTime;
		echo 'Duration:         ' . (round($timeDiff * 1000) / 1000) . " seconds\n";
		$db->close();

		echo 'New lObjects:     ' . $countDBCreate . "\n";
		echo 'Updated lObjects: ' . $countDBUpdate . "\n";
		echo 'Updated sID\'s:    ' . $countDBUpdateSID . "\n";

		return 'repairFileBackupToDatabase';
	}

//	$test = repaireLObjects();
	$test = testDB();
	$test = repairFileBackupToDatabase();

	echo json_encode((object) array(
		'test' => $test,
	));
?>