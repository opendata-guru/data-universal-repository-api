<?php
	$loadedLObjects = [];
	$fileLObjects = __DIR__ . '/../../api-data/links.csv';

	loadMappingFileLObjects($loadedLObjects);
	$hashLObjects = md5(serialize($loadedLObjects));

	function getLObject() {
		global $loadedLObjects;

		$parameterLID = htmlspecialchars($_GET['lid']);
		$error = null;
		$lObject = null;

		if ($parameterLID == '') {
			$error = (object) array(
				'error' => 400,
				'header' => 'HTTP/1.0 400 Bad Request',
				'message' => 'Bad Request. Path parameter for \'lID\' is not set',
				'parameter' => $parameterLID,
			);
		}

		if (!$error) {
			foreach($loadedLObjects as $object) {
				if ($object['lid'] == $parameterLID) {
					$lObject = $object;
				}
			}

			if (is_null($lObject)) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'lID\' path parameter.',
					'parameter' => $parameterLID,
				);
			}
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterLID,
			'lObject' => $lObject,
		);
	}

	function postLID() {
		$parameterLID = htmlspecialchars($_GET['lid']);
		$error = null;

		if ($parameterLID == '') {
			$error = (object) array(
				'error' => 400,
				'header' => 'HTTP/1.0 400 Bad Request',
				'message' => 'Bad Request. Path parameter \'lID\' is not set',
				'parameter' => $parameterLID,
			);
		}

		if (!$error) {
			$lObject = findLObjectByLID($parameterLID);

			if (is_null($lObject)) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'lID\' path parameter.',
					'parameter' => $parameterLID,
				);
			}
		}

		$parameterSID = htmlspecialchars($_GET['sid']);
		if ($parameterSID == '') {
			$parameterSID = htmlspecialchars($_GET['sID']);
		}
		if ($parameterSID != '') {
			$sObject = findSObject($parameterSID);

			if (is_null($sObject)) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'sID\' path parameter.',
					'parameter' => $parameterSID,
				);
			} else {
				updateLObjectSID($lObject, $parameterSID);
				$lObject = findLObjectByLID($parameterLID);

				saveMappingFileLObjects();
			}
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterLID,
			'lObject' => $lObject,
		);
	}

	function loadMappingFileLObjects(&$mapping) {
		global $fileLObjects;

		$idIdentifier = null;
		$idLastSeen = null;
		$idTitle = null;
		$idLID = null;
		$idPID = null;
		$idSID = null;

		$lines = explode("\n", file_get_contents($fileLObjects));
		$mappingHeader = str_getcsv($lines[0], ',');

		for ($m = 0; $m < count($mappingHeader); ++$m) {
			if ($mappingHeader[$m] === 'lid') {
				$idLID = $m;
			} else if ($mappingHeader[$m] === 'pid') {
				$idPID = $m;
			} else if ($mappingHeader[$m] === 'identifier') {
				$idIdentifier = $m;
			} else if ($mappingHeader[$m] === 'title') {
				$idTitle = $m;
			} else if ($mappingHeader[$m] === 'sid') {
				$idSID = $m;
			} else if ($mappingHeader[$m] === 'lastseen') {
				$idLastSeen = $m;
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');

				$lObject = [];
				$lObject['lid'] = $arr[$idLID] ?: '';
				$lObject['pid'] = $arr[$idPID] ?: '';
				$lObject['identifier'] = $arr[$idIdentifier] ?: '';
				$lObject['title'] = $arr[$idTitle] ?: '';
				$lObject['sid'] = $arr[$idSID] ?: '';
				$lObject['lastseen'] = $arr[$idLastSeen] ?: '';

				$mapping[] = $lObject;
			}
		}
	}

	function saveMappingFileLObjects() {
		global $loadedLObjects;
		global $hashLObjects;
		global $fileLObjects;

		$newHash = md5(serialize($loadedLObjects));

		if ($hashLObjects !== $newHash) {
			$header = [
				'lid',
				'pid',
				'identifier',
				'title',
				'sid',
				'lastseen'
			];

			$fp = fopen($fileLObjects, 'wb');
			fputcsv($fp, $header, ',');
			foreach ($loadedLObjects as $line) {
				fputcsv($fp, [
					$line['lid'],
					$line['pid'],
					$line['identifier'],
					$line['title'],
					$line['sid'],
					$line['lastseen']
				], ',');
			}
			fclose($fp);

			$hashLObjects = $newHash;
		}
	}

	function createLID() {
		global $loadedLObjects;

		// https://www.rechner.club/kombinatorik/anzahl-variationen-geordnet-ohne-wiederholung-berechnen
		// objects  | 61    | 61      | 61         | 61
		// draws    | 2     | 3       | 4          | 5
		// variants | 3,660 | 215,940 | 12,524,520 | 713,897,640

		$ALLOWED_CHARS = '0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$prefix = 'l';
		$length = 4;

		$usedLIDs = [];
		foreach($loadedLObjects as $lObject) {
			$usedLIDs[] = $lObject['lid'];
		}
		$usedLIDs = array_filter($usedLIDs);

		do {
			$lid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
		} while(in_array($lid, $usedLIDs));

		return $lid;
	}

	function findLObject($pid, $identifier) {
		global $loadedLObjects;

		foreach($loadedLObjects as $lObject) {
			if (($pid === $lObject['pid']) && ($identifier === $lObject['identifier'])) {
				return $lObject;
			}
		}

		return null;
	}

	function findLObjectByLID($lid) {
		global $loadedLObjects;

		foreach($loadedLObjects as $lObject) {
			if ($lObject['lid'] === $lid) {
				return $lObject;
			}
		}

		return null;
	}

	function updateLObject(&$obj) {
		global $loadedLObjects;

		foreach($loadedLObjects as &$lObject) {
			if (($obj['pid'] === $lObject['pid']) && ($obj['identifier'] === $lObject['identifier'])) {
				$lObject['lastseen'] = date('Y-m-d');
				return;
			}
		}

		$obj['lastseen'] = date('Y-m-d');

		$loadedLObjects[] = $obj;
	}

	function updateLObjectSID(&$obj, $sid) {
		global $loadedLObjects;

		foreach($loadedLObjects as &$lObject) {
			if ($obj['lid'] === $lObject['lid']) {
				$lObject['sid'] = $sid;
				$obj['sid'] = $sid;
				return;
			}
		}
	}

	function getLObjectChildren($pid) {
		global $loadedLObjects;

		$lObjects = [];

		foreach($loadedLObjects as $lObject) {
			if ($pid === $lObject['pid']) {
				$lObjects[] = $lObject;
			}
		}

		return $lObjects;
	}
?>