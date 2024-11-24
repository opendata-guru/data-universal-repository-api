<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	ini_set('max_execution_time', '300');

	$DEBUG_insights = false;
	$DEBUG_ignoreUpTime = false;

	// COMMENT THIS LINES
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include('../helper/_iobject.php');

	$basePath = '../../api-data/';
	$filePath = $basePath . 'temp-' . date('Y') . '/' . date('Y-m-d') . '-insights.json';

	function loadCronjobData($file) {
		$dir = dirname($file);
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}

		$data = null;
		if (file_exists($file)) {
			$data = json_decode(file_get_contents($file));
		}

		if (is_null($data)) {
			$data = array();
		}
		return $data;
	}

	function saveCronjobData($file, $data) {
		file_put_contents($file, json_encode($data));
	}

	function getInitialData() {
		return (object) array(
			'startTimestamp' => date('Y-m-d H:i:s'),
			'endTimestamp' => null,
			'duration' => null,
			'progress' => 0,
		);
	}

	function getCronInsights() {
		global $loadedIObjects;

		$today = date('Y-m-d');
		$objects = [];

		foreach($loadedIObjects as $iObject) {
			if ($today === $iObject->modified) {
				$obj = loadIObject($iObject);

				if (isset($obj->audited) && ($today === $obj->audited)) {
					// ignore objects audited today
				} else {
					$objects[] = $obj;
				}
			}
		}

		foreach($objects as $iObject) {
			if (!isset($iObject->insights)) {
				$iObject = updateIObjectFile($iObject);
				saveIObject($iObject);

var_dump($iObject);
exit;
				return false;
			}
		}
var_dump($objects);
//var_dump(count($objects));
exit;

var_dump($today);
var_dump($iObject->modified);
var_dump($iObject->insights);
exit;
			if (!is_null($data->accessURL) && ('' !== $data->accessURL)) {
				$iObject = findIObjectByURL($data->accessURL);

				if (is_null($iObject)) {
					pushIObject(createIID(), $data->accessURL);
					saveMappingFileIObjects();

					return false;
				}

				$modified = new DateTime($iObject->modified);
				if ($modified->diff($datetime)->format('%a') >= 5) {
					// update every 5 days

//					return false;
				}
			}

		return true;
	}

	function getNextData($data) {
		global $DEBUG_insights;
		global $DEBUG_ignoreUpTime;

		$modified = $data->endTimestamp;
		if ($DEBUG_insights || is_null($modified)) {
			$now = microtime(true);

			$upTime = new DateTime($data->startTimestamp);
			$upTimeDiff = $upTime->diff(new DateTime());
			$upTimeMinutes = $upTimeDiff->h * 60 + $upTimeDiff->i;
			$stamp = true;

			if (!$DEBUG_ignoreUpTime && ($upTimeMinutes > (60 * 4))) {
				// process insights of files maximum of 4 hours
				$stamp = true;
			} else {
				$stamp = getCronInsights();
				++$data->progress;
			}

			if ($stamp) {
				$data->duration = round(microtime(true) - $now, 3);
				$data->endTimestamp = date('Y-m-d H:i:s');
			}
		}

		return $data;
	}

	$data = loadCronjobData($filePath);
	$dataHash = md5(serialize($data));

	if (empty($data)) {
		$data = getInitialData();
	} else {
		$data = getNextData($data);
	}

	if ($dataHash == md5(serialize($data))) {
		echo json_encode(array('result' => 'done'));
	} else {
		saveCronjobData($filePath, $data);

		echo json_encode(array('result' => 'in progress'));
	}
?>