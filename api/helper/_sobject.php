<?php
	$loadedSObjects = [];
//	$fileSObjects = __DIR__ . '/../../api-data/suppliers.csv';
//
//	loadMappingFileSObjects($loadedSObjects);
	$hashSObjects = md5(serialize($loadedSObjects));

	function loadMappingFileSObjects(&$mapping) {
		global $fileSObjects;

		$idIdentifier = null;
		$idLastSeen = null;
		$idTitle = null;
		$idLID = null;
		$idPID = null;
		$idSID = null;

		$lines = explode("\n", file_get_contents($fileSObjects));
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

				$sObject = [];
				$sObject['lid'] = $arr[$idLID] ?: '';
				$sObject['pid'] = $arr[$idPID] ?: '';
				$sObject['identifier'] = $arr[$idIdentifier] ?: '';
				$sObject['title'] = $arr[$idTitle] ?: '';
				$sObject['sid'] = $arr[$idSID] ?: '';
				$sObject['lastseen'] = $arr[$idLastSeen] ?: '';

				$mapping[] = $sObject;
			}
		}
	}

	function saveMappingFileSObjects() {
		global $loadedSObjects;
		global $hashSObjects;
		global $fileSObjects;

		$newHash = md5(serialize($loadedSObjects));

		if ($hashSObjects !== $newHash) {
			$header = [
				'lid',
				'pid',
				'identifier',
				'title',
				'sid',
				'lastseen'
			];

			$fp = fopen($fileSObjects, 'wb');
			fputcsv($fp, $header, ',');
			foreach ($loadedSObjects as $line) {
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

			$hashSObjects = $newHash;
		}
	}

	function createSID() {
		global $loadedSObjects;

		// https://www.rechner.club/kombinatorik/anzahl-variationen-geordnet-ohne-wiederholung-berechnen
		// objects  | 61    | 61      | 61         | 61
		// draws    | 2     | 3       | 4          | 5
		// variants | 3,660 | 215,940 | 12,524,520 | 713,897,640

		$ALLOWED_CHARS = '0123456789abcdefghijklmnopqrtuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$prefix = 's';
		$length = 4;

		$usedSIDs = [];
		foreach($loadedSObjects as $sObject) {
			$usedSIDs[] = $sObject['sid'];
		}
		$usedSIDs = array_filter($usedSIDs);

		do {
			$sid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
		} while(in_array($sid, $usedSIDs));

		return $sid;
	}

	function findSObject($pid, $identifier) {
		global $loadedSObjects;

		foreach($loadedSObjects as $sObject) {
			if (($pid === $sObject['pid']) && ($identifier === $sObject['identifier'])) {
				return $sObject;
			}
		}

		return null;
	}

	function updateSObject(&$obj) {
		global $loadedSObjects;

		foreach($loadedSObjects as &$sObject) {
			if (($obj['pid'] === $sObject['pid']) && ($obj['identifier'] === $sObject['identifier'])) {
				$sObject['lastseen'] = date('Y-m-d');
				return;
			}
		}

		$obj['lastseen'] = date('Y-m-d');

		$loadedSObjects[] = $obj;
	}
?>