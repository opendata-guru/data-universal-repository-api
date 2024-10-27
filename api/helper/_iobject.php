<?php
	$loadedIObjects = [];
	$fileIObjects = __DIR__ . '/../../api-data/insights.csv';

	loadMappingFileIObjects($loadedIObjects);
	$hashIObjects = md5(serialize($loadedIObjects));

	function getIObject() {
		global $loadedIObjects;

		$parameterIID = htmlspecialchars($_GET['iid']);
		$error = null;
		$iObject = null;

		if ($parameterIID == '') {
			$error = (object) array(
				'error' => 400,
				'header' => 'HTTP/1.0 400 Bad Request',
				'message' => 'Bad Request. Path parameter for \'iID\' is not set',
				'parameter' => $parameterIID,
			);
		}

		if (!$error) {
			foreach($loadedIObjects as $object) {
				if ($object->iid == $parameterIID) {
					$iObject = $object;
				}
			}

			if (is_null($iObject)) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'iID\' path parameter.',
					'parameter' => $parameterIID,
				);
			}
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterIID,
			'iObject' => $iObject,
		);
	}

	function loadMappingFileIObjects(&$mapping) {
		global $fileIObjects;

		$idSameAsWikidata = null;
		$idSameAsInspire = null;
		$idModified = null;
		$idTitleDE = null;
		$idTitleEN = null;
		$idType = null;
		$idURL = null;
		$idIID = null;

		$lines = explode("\n", file_get_contents($fileIObjects));
		$mappingHeader = str_getcsv($lines[0], ',');

		for ($m = 0; $m < count($mappingHeader); ++$m) {
			if ($mappingHeader[$m] === 'iid') {
				$idIID = $m;
			} else if ($mappingHeader[$m] === 'title@EN') {
				$idTitleEN = $m;
			} else if ($mappingHeader[$m] === 'title@DE') {
				$idTitleDE = $m;
			} else if ($mappingHeader[$m] === 'url') {
				$idURL = $m;
			} else if ($mappingHeader[$m] === 'type') {
				$idType = $m;
			} else if ($mappingHeader[$m] === 'sameAsInspire') {
				$idSameAsInspire = $m;
			} else if ($mappingHeader[$m] === 'sameAsWikidata') {
				$idSameAsWikidata = $m;
			} else if ($mappingHeader[$m] === 'modified') {
				$idModified = $m;
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');

				$mapping[] = (object) array(
					'iid' => $arr[$idIID] ?: '',
					'title' => array (
						'de' => $arr[$idTitleDE] ?: '',
						'en' => $arr[$idTitleEN] ?: '',
					),
					'url' => $arr[$idURL] ?: '',
					'type' => $arr[$idType] ?: '',
					'sameAs' => array (
						'inspire' => $arr[$idSameAsInspire] ?: '',
						'wikidata' => $arr[$idSameAsWikidata] ?: '',
					),
					'modified' => $arr[$idModified] ?: '',
				);
			}
		}
	}

	function saveMappingFileIObjects() {
		global $loadedIObjects;
		global $hashIObjects;
		global $fileIObjects;

		$newHash = md5(serialize($loadedIObjects));

		if ($hashIObjects !== $newHash) {
			$header = [
				'iid',
				'title@EN',
				'title@DE',
				'url',
				'type',
				'sameAsInspire'
				'sameAsWikidata',
				'modified',
			];

			$fp = fopen($fileIObjects, 'wb');
			fputcsv($fp, $header, ',');
			foreach ($loadedIObjects as $iObject) {
				fputcsv($fp, [
					$iObject->iid,
					$iObject->title['en'],
					$iObject->title['de'],
					$iObject->url,
					$iObject->type,
					$iObject->sameAs['inspire'],
					$iObject->sameAs['wikidata'],
					$iObject->modified,
				], ',');
			}
			fclose($fp);

			$hashIObjects = $newHash;
		}
	}

	function pushIObject($iid, $labelDE, $labelEN, $url, $type, $sameAsInspire, $sameAsWikidata) {
		global $loadedIObjects;

		$loadedIObjects[] = (object) array(
			'iid' => $iid,
			'title' => array (
				'de' => $labelDE,
				'en' => $labelEN,
			),
			'url' => $url,
			'type' => $type,
			'sameAs' => array (
				'inspire' => $sameAsInspire,
				'wikidata' => $sameAsWikidata,
			),
			'modified' => date('Y-m-d H:i:s'),
		);

		return end($loadedIObjects);
	}

	function updateIObject($iid, $labelDE, $labelEN, $url, $type, $sameAsInspire, $sameAsWikidata) {
		global $loadedIObjects;

		foreach($loadedIObjects as &$iObject) {
			if ($iid === $iObject->iid) {
				$iObject->title = array (
					'de' => $labelDE,
					'en' => $labelEN,
				);
				$sObject->url = $url;
				$sObject->type = $type;
				$iObject->sameAs = array (
					'inspire' => $sameAsInspire,
					'wikidata' => $sameAsWikidata,
				);
				$sObject->modified = date('Y-m-d H:i:s');
				return $iObject;
			}
		}

		return null;
	}

	function createIID() {
		global $loadedIObjects;

		// https://www.rechner.club/kombinatorik/anzahl-variationen-geordnet-ohne-wiederholung-berechnen
		// objects  | 61    | 61      | 61         | 61
		// draws    | 2     | 3       | 4          | 5
		// variants | 3,660 | 215,940 | 12,524,520 | 713,897,640

		$ALLOWED_CHARS = '0123456789abcdefghjklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$prefix = 'i';
		$length = 5;

		$usedIIDs = [];
		foreach($loadedIObjects as $iObject) {
			$usedIIDs[] = $iObject->iid;
		}
		$usedIIDs = array_filter($usedIIDs);

		do {
			$iid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
		} while(in_array($iid, $usedIIDs));

		return $iid;
	}

	function findIObject($iid) {
		global $loadedIObjects;

		if ($iid == '') {
			return null;
		}

		foreach($loadedIObjects as $iObject) {
			if ($iid == $iObject->iid) {
				return $iObject;
			}
		}

		return null;
	}

	function findIObjectByURL($url) {
		global $loadedIObjects;

		if ($url == '') {
			return null;
		}

		foreach($loadedIObjects as $iObject) {
			if ($iid == $iObject->url) {
				return $iObject;
			}
		}

		return null;
	}
?>