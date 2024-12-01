<?php
	// COMMENT THIS LINES
//	ini_set('display_errors', 1);
//	ini_set('display_startup_errors', 1);
//	error_reporting(E_ALL);

	$loadedIObjects = [];
	$indexIObjectsByURL = [];
	$lostIObjects = [];
	$fileIObjects = __DIR__ . '/' . (file_exists(__DIR__ . '/' . 'live-insights/live-insights-get.php') ? '' : '../') . '../api-data/insights.csv';

	loadMappingFileIObjects($loadedIObjects, $indexIObjectsByURL);
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
			$iObject = $loadedIObjects[$parameterIID];

			if (is_null($iObject)) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'iID\' path parameter.',
					'parameter' => $parameterIID,
				);
			} else {
				$iObject = loadIObject($iObject);
			}
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterIID,
			'iObject' => $iObject,
		);
	}

	function postIID() {
		include_once((file_exists('live-insights/live-insights-get.php') ? '' : '../') . 'live-insights/live-insights-get.php');

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
			$iObject = updateIObject($iObject->iid, $iObject->url);
			$iObject = updateIObjectFile($iObject);

			saveIObject($iObject);
			saveMappingFileIObjects();
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterIID,
			'iObject' => $iObject,
		);
	}

	function postIObject() {
		$parameterURL = trim($_GET['url']);

		if ($parameterURL == '') {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Parameter \'url\' is not set',
			));
			exit;
		}
		$url = $parameterURL;

		$iObject = findIObjectByURL($url);
		if ($iObject) {
			$iObject = loadIObject($iObject);
			return $iObject;
		}

		$iObject = pushIObject(createIID(), $url);
		saveMappingFileIObjects();

		return $iObject;
	}

	function updateIObjectFile($iObject) {
		if (!$iObject) {
			return $iObject;
		}

		include_once((file_exists('live-insights/live-insights-get.php') ? '' : '../') . 'live-insights/live-insights-get.php');

		$insights = null;
		$contentType = '';
		$assets = [];
		$error = null;

		if ('' != $iObject->url) {
			$insights = getInsights($iObject->url);
		}

		if (!is_null($insights)) {
			$pass = end($insights->passes);

			if ($pass) {
				if ($pass->file && $pass->file->metadata) {
					$contentType = $pass->file->metadata->contentType;
					if ($pass->file->metadata->httpCode >= 400) {
						$error = 'HTTP response status code: ' . $pass->file->metadata->httpCode;
					}
				}
				if ($pass->content) {
					$contentType = $pass->content->contentType;
					if ($pass->content->error) {
						$error = $pass->content->error->descriptions;
					}
				}
				if ($pass->interpreter && $pass->interpreter->assets) {
					$assets = $pass->interpreter->assets;
				}
			}
		}

		$iObject->audited = date('Y-m-d');
		$iObject->insights = (object) array(
			'contentType' => $contentType,
			'error' => $error,
			'assets' => $assets,
		);

		return $iObject;
	}

	function loadMappingFileIObjects(&$mapping, &$indexByURL) {
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

				if ($arr[$idIID]) {
					$mapping[$arr[$idIID]] = (object) array(
						'iid' => $arr[$idIID],
						'url' => $arr[$idURL] ?: '',
						'modified' => $arr[$idModified] ?: '',
					);
				}

				if ($arr[$idURL]) {
					$indexByURL[$arr[$idURL]] = $arr[$idIID] ?: '';
				}
			}
		}
	}

	function loadIObject($iObject) {
		if (!$iObject) {
			return $iObject;
		}

		$filePath = (file_exists('live-insights/live-insights-get.php') ? '' : '../') . '../api-data/assets-iid/' . strtolower(substr($iObject->iid, 0, 2)) . '/' . $iObject->iid . '.json';

		if (!file_exists($filePath)) {
			return $iObject;
		}

		$data = file_get_contents($filePath);
		if (false === $data) {
			return $iObject;
		}

		return json_decode($data);
	}

	function saveIObject($iObject) {
		if (!$iObject) {
			return $iObject;
		}

		$filePath = (file_exists('live-insights/live-insights-get.php') ? '' : '../') . '../api-data/assets-iid/' . strtolower(substr($iObject->iid, 0, 2)) . '/' . $iObject->iid . '.json';

		$dir = dirname($filePath);
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}

		file_put_contents($filePath, json_encode($iObject));
	}

	function deleteIObject($iObject) {
		global $loadedIObjects;

		if (!$iObject) {
			return;
		}

		$filePath = (file_exists('live-insights/live-insights-get.php') ? '' : '../') . '../api-data/assets-iid/' . strtolower(substr($iObject->iid, 0, 2)) . '/' . $iObject->iid . '.json';

		if (!file_exists($filePath)) {
			return;
		}

		unlink($filePath);
		unset($loadedIObjects[$iObject->iid]);
		saveMappingFileIObjects();
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
			foreach ($loadedIObjects as $key => $iObject) {
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

	function pushSimpleIObject($iid, $url) {
		global $loadedIObjects;

		if (!$iid) {
			return null;
		}

		$loadedIObjects[$iid] = (object) array(
			'iid' => $iid,
			'url' => $url,
			'modified' => date('Y-m-d'),
		);

		$iObject = $loadedIObjects[$iid];

		return $iObject;
	}

	function pushIObject($iid, $url) {
		$iObject = pushSimpleIObject($iid, $url);

		$iObject = updateIObjectFile($iObject);
		saveIObject($iObject);

		return $iObject;
	}

	function persistIObject($iObject) {
		global $loadedIObjects;

		if (!$iObject) {
			return null;
		}

		$loadedIObjects[$iObject->iid] = $iObject;
	}

	function updateIObject($iid, $url) {
		global $loadedIObjects;

		$iObject = $loadedIObjects[$iid];

		if ($iObject) {
			$iObject->url = $url;
			$iObject->modified = date('Y-m-d');
			return $iObject;
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

		do {
			$iid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
		} while($loadedIObjects[$iid]);

		return $iid;
	}

	function findIObject($iid) {
		global $loadedIObjects;

		if ($iid == '') {
			return null;
		}

		return isset($loadedIObjects[$iid]) ? $loadedIObjects[$iid] : null;
	}

	function findIObjectByURL($url) {
		global $loadedIObjects;
		global $indexIObjectsByURL;

		if ($url == '') {
			return null;
		}

		$iid = $indexIObjectsByURL[$url];
		if ($iid == '') {
			return null;
		}

		return isset($loadedIObjects[$iid]) ? $loadedIObjects[$iid] : null;
	}

	function scanIObjectDirRecursive($dir) {
		$result = [];

		foreach (scandir($dir) as $filename) {
			if ($filename[0] === '.') continue;

			$filepath = $dir . '/' . $filename;
			if (is_dir($filepath)) {
				$result = array_merge($result, scanIObjectDirRecursive($filepath));
			} else {
				$result[] = explode('.', $filename)[0];
			}
		}

		return $result;
	}

	function getLostIObjects() {
		global $lostIObjects;

		if (0 === count($lostIObjects)) {
			$filePath = (file_exists('live-insights/live-insights-get.php') ? '' : '../') . '../api-data/assets-iid/';

			$dirIDs = scanIObjectDirRecursive($filePath);

			foreach($dirIDs as $iid) {
				$baseObject = findIObject($iid);

				if (!$baseObject) {
					$baseObject = (object) array(
						'iid' => $iid,
						'url' => '',
						'modified' => '',
					);

					$iObject = loadIObject($baseObject);

					if ($iObject) {
						$lostIObjects[] = $iObject;
					} else {
						$lostIObjects[] = $baseObject;
					}
				}
			}
		}

		return $lostIObjects;
	}

	function getUnauditedIObjects() {
		global $loadedIObjects;

		$missingIObjects = [];
		$today = date('Y-m-d');

		foreach($loadedIObjects as $iObject) {
			$iObject = loadIObject($iObject);

			if ($iObject && ($today === $iObject->modified) && !isset($iObject->audited)) {
				$missingIObjects[] = $iObject;
			}
		}

		return $missingIObjects;
	}

	function getAtticIObjects() {
		global $loadedIObjects;

		$atticIObjects = [];
		$today = date('Y-m-d');

		foreach($loadedIObjects as $iObject) {
			if ($iObject && ($today !== $iObject->modified)) {
				$atticIObjects[] = $iObject;
			}
		}

		return $atticIObjects;
	}
?>