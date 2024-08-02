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
		global $euPath;

		include('cronjob-hvd-queries.php');

		$url = $euPath . '?query=' . rawurlencode(getSPARQLgetEUcatalogs());
		$data = get_contents_sparql($url);
		$result = json_decode($data)->results->bindings;

		$data = array();
		foreach($result as $object) {
			$data[] = array(
				'catalog' => $object->catalog->value,
				'title' => $object->title->value,
				'countDatasetsDuration' => null,
				'countDatasetsTimestamp' => null,
				'countDistributionsDuration' => null,
				'countDistributionsTimestamp' => null,
				'distributionsDuration' => null,
				'distributionsTimestamp' => null,
			);
		}

		return $data;
	}

	function getEUCountDatasetsData($catalog) {
		global $basePath;
		global $euPath;

		$date = date('Y-m-d');
		$fileDate = $basePath . 'hvd-date/' . date('Y-m') . '/hvd-' . $date . '.json';
		$countsData = (array) loadCronjobData($fileDate);

		$url = $euPath . '?query=' . rawurlencode(getSPARQLcountEUdatasetsByCatalog($catalog));
		$data = get_contents_sparql($url);
		$result = json_decode($data)->results->bindings[0];
		$count = intval($result->count->value);

		$countsData[$catalog] = array(
			'datasets' => $count,
			'distributions' => null,
		);

		saveCronjobData($fileDate, $countsData);
	}

	function getEUCountDistributionsData($catalog) {
		global $basePath;
		global $euPath;

		$date = date('Y-m-d');
		$fileDate = $basePath . 'hvd-date/' . date('Y-m') . '/hvd-' . $date . '.json';
		$countsData = (array) loadCronjobData($fileDate);

		$url = $euPath . '?query=' . rawurlencode(getSPARQLcountEUdistributionsByCatalog($catalog));
		$data = get_contents_sparql($url);
		$result = json_decode($data)->results->bindings[0];
		$count = intval($result->count->value);

		$countsData[$catalog]->distributions = $count;

		saveCronjobData($fileDate, $countsData);
	}

	function getEUaccessURLsData($catalog) {
		global $basePath;
		global $euPath;

		$date = date('Y-m-d');
		$fileDate = $basePath . 'hvd-access-url-date/' . date('Y-m') . '/hvd-access-date-' . $date . '.json';
		$accessData = (array) loadCronjobData($fileDate);

		$url = $euPath . '?query=' . rawurlencode(getSPARQLgetEUaccessURLsByCatalog($catalog));
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
		include('cronjob-hvd-queries.php');

		foreach ($data as &$object) {
			$catalog = $object->catalog;

			$modified = $object->countDatasetsTimestamp;
			if (is_null($modified)) {
				$now = microtime(true);

				getEUCountDatasetsData($catalog);

				$object->countDatasetsDuration = round(microtime(true) - $now, 3);
				$object->countDatasetsTimestamp = date('Y-m-d H:i:s');

				return $data;
			}

			$modified = $object->countDistributionsTimestamp;
			if (is_null($modified)) {
				$now = microtime(true);

				getEUCountDistributionsData($catalog);

				$object->countDistributionsDuration = round(microtime(true) - $now, 3);
				$object->countDistributionsTimestamp = date('Y-m-d H:i:s');

				return $data;
			}

			$modified = $object->distributionsTimestamp;
			if (is_null($modified)) {
				$now = microtime(true);

				if (getEUcatalogGovData() === $catalog) {
					getEUaccessURLsData($catalog);
				}

				$object->distributionsDuration = round(microtime(true) - $now, 3);
				$object->distributionsTimestamp = date('Y-m-d H:i:s');

				return $data;
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