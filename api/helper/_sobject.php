<?php
	$loadedSObjects = [];
	$fileSObjects = __DIR__ . '/../../api-data/suppliers.csv';

	loadMappingFileSObjects($loadedSObjects);
	$hashSObjects = md5(serialize($loadedSObjects));

	function loadMappingFileSObjects(&$mapping) {
		global $fileSObjects;

		$idPartOfWikidata = null;
		$idSameAsWikidata = null;
		$idPartOfRS = null;
		$idSameAsRS = null;
		$idTitleDE = null;
		$idTitleEN = null;
		$idType = null;
		$idSID = null;

		$lines = explode("\n", file_get_contents($fileSObjects));
		$mappingHeader = str_getcsv($lines[0], ',');

		for ($m = 0; $m < count($mappingHeader); ++$m) {
			if ($mappingHeader[$m] === 'sid') {
				$idSID = $m;
			} else if ($mappingHeader[$m] === 'title@EN') {
				$idTitleEN = $m;
			} else if ($mappingHeader[$m] === 'title@DE') {
				$idTitleDE = $m;
			} else if ($mappingHeader[$m] === 'type') {
				$idType = $m;
			} else if ($mappingHeader[$m] === 'sameAsWikidata') {
				$idSameAsWikidata = $m;
			} else if ($mappingHeader[$m] === 'sameAsRS') {
				$idSameAsRS = $m;
			} else if ($mappingHeader[$m] === 'partOfWikidata') {
				$idPartOfWikidata = $m;
			} else if ($mappingHeader[$m] === 'partOfRS') {
				$idPartOfRS = $m;
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');

				$sObject = [];
				$sObject['sid'] = $arr[$idSID] ?: '';
				$sObject['title@EN'] = $arr[$idTitleEN] ?: '';
				$sObject['title@DE'] = $arr[$idTitleDE] ?: '';
				$sObject['type'] = $arr[$idType] ?: '';
				$sObject['sameAsWikidata'] = $arr[$idSameAsWikidata] ?: '';
				$sObject['sameAsRS'] = $arr[$idSameAsRS] ?: '';
				$sObject['partOfWikidata'] = $arr[$idPartOfWikidata] ?: '';
				$sObject['partOfRS'] = $arr[$idPartOfRS] ?: '';

				$mapping[] = $sObject;
			}
		}
	}

/*	function saveMappingFileSObjects() {
		global $loadedSObjects;
		global $hashSObjects;
		global $fileSObjects;

		$newHash = md5(serialize($loadedSObjects));

		if ($hashSObjects !== $newHash) {
			$header = [
				'sid',
				'title@EN',
				'title@DE',
				'type',
				'sameAsWikidata',
				'sameAsRS',
				'partOfWikidata',
				'partOfRS'
			];

			$fp = fopen($fileSObjects, 'wb');
			fputcsv($fp, $header, ',');
			foreach ($loadedSObjects as $line) {
				fputcsv($fp, [
					$line['sid'],
					$line['title@EN'],
					$line['title@DE'],
					$line['type'],
					$line['sameAsWikidata'],
					$line['sameAsRS'],
					$line['partOfWikidata'],
					$line['partOfRS']
				], ',');
			}
			fclose($fp);

			$hashSObjects = $newHash;
		}
	}*/

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

/*	function findSObject($pid, $identifier) {
		global $loadedSObjects;

		foreach($loadedSObjects as $sObject) {
			if (($pid === $sObject['pid']) && ($identifier === $sObject['identifier'])) {
				return $sObject;
			}
		}

		return null;
	}*/

/*	function updateSObject(&$obj) {
		global $loadedSObjects;

		foreach($loadedSObjects as &$sObject) {
			if (($obj['pid'] === $sObject['pid']) && ($obj['identifier'] === $sObject['identifier'])) {
				$sObject['lastseen'] = date('Y-m-d');
				return;
			}
		}

		$obj['lastseen'] = date('Y-m-d');

		$loadedSObjects[] = $obj;
	}*/
?>