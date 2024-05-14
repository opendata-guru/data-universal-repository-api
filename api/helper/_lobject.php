<?php
	$loadedLObjects = [];
	$fileLObjects = __DIR__ . '/../../api-data/links.csv';

	loadMappingFileLObjects($loadedLObjects);
	$hashLObjects = md5(serialize($loadedLObjects));

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
?>