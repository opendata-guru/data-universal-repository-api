<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	// COMMENT THIS LINES
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	$basePath = '../../api-data/';
	$filePath = $basePath . 'temp-' . date('Y') . '/' . date('Y-m-d') . '-hvd.json';
	$euPath = 'https://data.europa.eu/sparql';

	function get_contents_sparql($url){
		$headers = [
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
			'Accept: application/sparql-results+json',
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$data = curl_exec($ch);

		curl_close($ch);

		return $data;
	}

	function loadCronjobData($file) {
		$dir = dirname($file);
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}

		$data = json_decode(file_get_contents($file));

		if (is_null($data)) {
			$data = array();
		}
		return $data;
	}

	function saveCronjobData($file, $data) {
		file_put_contents($file, json_encode($data));
	}

	function getInitialData() {
		$data = array(
			'europe' => array(
				'countDatasetsDuration' => null,
				'countDatasetsTimestamp' => null,
				'countDistributionsDuration' => null,
				'countDistributionsTimestamp' => null,
				'distributionsDuration' => null,
				'distributionsTimestamp' => null,
			),
			'govdata' => array(
			)
		);

		return $data;
	}

	function getEUCountDatasetsData() {
		global $basePath;
		global $euPath;

		include('cronjob-hvd-queries.php');

		$date = date('Y-m-d');
		$fileDate = $basePath . 'hvd-date/' . date('Y-m') . '/hvd-' . $date . '.json';
		$countsData = (array) loadCronjobData($fileDate);

		$url = $euPath . '?query=' . rawurlencode(getSPARQLcountEUdatasetsForMemberStates(getMemberStateCatalogGovData()));
		$data = get_contents_sparql($url);
		$result = json_decode($data)->results->bindings[0];
		$count = intval($result->count->value);

		$countsData['eu-govdata-datasets'] = $count;

		saveCronjobData($fileDate, $countsData);
	}

	function getEUCountDistributionsData() {
		global $basePath;
		global $euPath;

		include('cronjob-hvd-queries.php');

		$date = date('Y-m-d');
		$fileDate = $basePath . 'hvd-date/' . date('Y-m') . '/hvd-' . $date . '.json';
		$countsData = (array) loadCronjobData($fileDate);

		$url = $euPath . '?query=' . rawurlencode(getSPARQLcountEUdistributionsForMemberStates(getMemberStateCatalogGovData()));
		$data = get_contents_sparql($url);
		$result = json_decode($data)->results->bindings[0];
		$count = intval($result->count->value);

		$countsData['eu-govdata-distributions'] = $count;

		saveCronjobData($fileDate, $countsData);
	}

	function getEUaccessURLsData() {
		global $basePath;
		global $euPath;

		include('cronjob-hvd-queries.php');

		$date = date('Y-m-d');
		$fileDate = $basePath . 'hvd-access-url-date/' . date('Y-m') . '/hvd-access-date-' . $date . '.json';
		$accessData = (array) loadCronjobData($fileDate);

		$url = $euPath . '?query=' . rawurlencode(getSPARQLgetEUaccessURLsForMemberStates(getMemberStateCatalogGovData()));
		$data = get_contents_sparql($url);
		$result = json_decode($data)->results->bindings;

		foreach($result as $line) {
			$accessData[] = array(
				'identifier' => $line->identifier->value,
				'accessURL' => $line->accessURL->value
			);
		}

		saveCronjobData($fileDate, $accessData);
	}

	function getNextData($data) {

		$modified = $data->europe->countDatasetsTimestamp;
		if (is_null($modified)) {
			$now = microtime(true);

			getEUCountDatasetsData();

			$data->europe->countDatasetsDuration = round(microtime(true) - $now, 3);
			$data->europe->countDatasetsTimestamp = date('Y-m-d H:i:s');

			return $data;
		}

		$modified = $data->europe->countDistributionsTimestamp;
		if (is_null($modified)) {
			$now = microtime(true);

			getEUCountDistributionsData();

			$data->europe->countDistributionsDuration = round(microtime(true) - $now, 3);
			$data->europe->countDistributionsTimestamp = date('Y-m-d H:i:s');

			return $data;
		}

		$modified = $data->europe->distributionsTimestamp;
		if (is_null($modified)) {
			$now = microtime(true);

			getEUaccessURLsData();

			$data->europe->distributionsDuration = round(microtime(true) - $now, 3);
			$data->europe->distributionsTimestamp = date('Y-m-d H:i:s');

			return $data;
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