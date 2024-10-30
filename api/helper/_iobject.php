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

	function postIID() {
		include('live-insights/live-insights-get.php');

		$parameterIID = htmlspecialchars($_GET['iid']);
		$error = null;
		$url = '';

		if ($parameterIID == '') {
			$error = (object) array(
				'error' => 400,
				'header' => 'HTTP/1.0 400 Bad Request',
				'message' => 'Bad Request. Path parameter \'iID\' is not set',
				'parameter' => $parameterIID,
			);
		}

		if (!$error) {
			$iObject = findIObject($parameterIID);

			if (is_null($iObject)) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'iID\' path parameter.',
					'parameter' => $parameterIID,
				);
			}
		}

		if (!$error) {
			$insights = null;

			if ('' != $iObject->url) {
				$insights = getInsights($iObject->url);
			}

			if (!is_null($insights)) {
				$contentType = '';
				$interpreter = [];

				$pass = end($insights->passes);

				if ($pass) {
					if ($pass->file && $pass->file->metadata) {
						$contentType = $pass->file->metadata->contentType;
					}
					if ($pass->content) {
						$contentType = $pass->content->contentType;
					}
					if ($pass->interpreter && $pass->interpreter->assets) {
						$interpreter = $pass->interpreter->assets;
					}
				}

				$iObject->contentType = $contentType;
				$iObject->insights = $interpreter;
				$iObject->pass = $pass;

				$iObject = updateIObject($iObject->iid, $iObject->url);

//				saveMappingFileIObjects();
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

		$idModified = null;
		$idURL = null;
		$idIID = null;

		if (!file_exists($fileIObjects)) {
			return;
		}

		$lines = explode("\n", file_get_contents($fileIObjects));
		$mappingHeader = str_getcsv($lines[0], ',');

		for ($m = 0; $m < count($mappingHeader); ++$m) {
			if ($mappingHeader[$m] === 'iid') {
				$idIID = $m;
			} else if ($mappingHeader[$m] === 'url') {
				$idURL = $m;
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
					'url' => $arr[$idURL] ?: '',
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
				'url',
				'modified',
			];

			$fp = fopen($fileIObjects, 'wb');
			fputcsv($fp, $header, ',');
			foreach ($loadedIObjects as $iObject) {
				fputcsv($fp, [
					$iObject->iid,
					$iObject->url,
					$iObject->modified,
				], ',');
			}
			fclose($fp);

			$hashIObjects = $newHash;
		}
	}

	function pushIObject($iid, $url) {
		global $loadedIObjects;

		$loadedIObjects[] = (object) array(
			'iid' => $iid,
			'url' => $url,
			'modified' => date('Y-m-d'),
		);

		return end($loadedIObjects);
	}

	function updateIObject($iid, $url) {
		global $loadedIObjects;

		foreach($loadedIObjects as &$iObject) {
			if ($iid === $iObject->iid) {
				$iObject->url = $url;
				$iObject->modified = date('Y-m-d');
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
		if ($loadedIObjects) {
			foreach($loadedIObjects as $iObject) {
				$usedIIDs[] = $iObject->iid;
			}
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

		if ($loadedIObjects) {
			foreach($loadedIObjects as $iObject) {
				if ($url == $iObject->url) {
					return $iObject;
				}
			}
		}

		return null;
	}
?>