<?php
	$loadedLObjects = [];

	loadMappingFileLObjects(__DIR__ . '/../../api-data/links.csv', $loadedLObjects);

	function loadMappingFileLObjects($file, &$mapping) {
		$idIdentifier = null;
		$idTitle = null;
		$idLID = null;
		$idPID = null;
		$idSID = null;

		$lines = explode("\n", file_get_contents($file));
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
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');
				$mapping[] = [
					$arr[$idLID] ?: '',
					$arr[$idPID] ?: '',
					$arr[$idIdentifier] ?: '',
					$arr[$idTitle] ?: '',
					$arr[$idSID] ?: ''
				];
			}
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
		foreach($loadedLObjects as $lObjects) {
			$usedLIDs[] = linkGetLID($lObjects);
		}
		$usedLIDs = array_filter($usedLIDs);

		do {
			$lid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
		} while(in_array($lid, $usedLIDs));

		return $lid;
	}

	function findLObject($pid, $identifier) {
		global $loadedLObjects;

		foreach($loadedLObjects as $lObjects) {
			if (($pid === linkGetPID($lObjects)) && ($identifier === linkGetIdentifier($lObjects))) {
				return $lObjects;
			}
		}

		return null;
	}

	function linkGetLID($lObject) {
		return $lObject[0];
	}
	function linkGetPID($lObject) {
		return $lObject[1];
	}
	function linkGetIdentifier($lObject) {
		return $lObject[2];
	}
	function linkGetTitle($lObject) {
		return $lObject[3];
	}
	function linkGetSID($lObject) {
		return $lObject[4];
	}
?>