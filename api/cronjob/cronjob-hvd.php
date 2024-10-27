<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	// COMMENT THIS LINES
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include('../helper/_iobject.php');

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
				'countDataServicesDuration' => null,
				'countDataServicesTimestamp' => null,
				'countLicensesDuration' => null,
				'countLicensesTimestamp' => null,
				'distributionsDuration' => null,
				'distributionsTimestamp' => null,
				'distributionsInsightsDuration' => null,
				'distributionsInsightsBuster' => 0,
				'distributionsInsightsTimestamp' => null,
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
			'dataservices' => null,
			'licenses' => null,
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

	function getEUCountDataServicesData($catalog) {
		global $basePath;
		global $euPath;

		$date = date('Y-m-d');
		$fileDate = $basePath . 'hvd-date/' . date('Y-m') . '/hvd-' . $date . '.json';
		$countsData = (array) loadCronjobData($fileDate);

		$url = $euPath . '?query=' . rawurlencode(getSPARQLcountEUdataServicesByCatalog($catalog));
		$data = get_contents_sparql($url);
		$result = json_decode($data)->results->bindings[0];
		$count = intval($result->count->value);

		$countsData[$catalog]->dataservices = $count;

		saveCronjobData($fileDate, $countsData);
	}

	function getEUCountLicensesData($catalog) {
		global $basePath;
		global $euPath;

		$date = date('Y-m-d');
		$fileDate = $basePath . 'hvd-date/' . date('Y-m') . '/hvd-' . $date . '.json';
		$countsData = (array) loadCronjobData($fileDate);

		$url = $euPath . '?query=' . rawurlencode(getSPARQLcountEUlicensesByCatalog($catalog));
		$data = get_contents_sparql($url);
		$result = json_decode($data)->results->bindings;

		$valuesCCBYcomparable = array(
			'http://dcat-ap.de/def/licenses/odbl',
			'http://dcat-ap.de/def/licenses/dl-by-de/2.0',
			'http://dcat-ap.de/def/licenses/geonutz/20130319',
			'https://www.etalab.gouv.fr/licence-ouverte-open-licence',
			'https://www.etalab.gouv.fr/wp-content/uploads/2014/05/Licence_Ouverte.pdf'
		);
		$valuesCC0comparable = array(
			'http://dcat-ap.de/def/licenses/dl-de-zero-2.0',
			'http://dcat-ap.de/def/licenses/dl-zero-de/2.0'
		);
		$valuesRestrictive = array(
			'http://dcat-ap.de/def/licenses/other-closed',
			'http://dcat-ap.de/def/licenses/cc-by-sa/4.0',
			'http://dcat-ap.de/def/licenses/cc-by-nd/4.0'
		);
		$valuesCCBY = array(
			'http://publications.europa.eu/resource/authority/licence/CC_BY_4_0',
			'http://creativecommons.org/licenses/by/4.0/',
			'http://dcat-ap.de/def/licenses/cc-by',
			'http://dcat-ap.de/def/licenses/cc-by-de/3.0',
			'http://dcat-ap.de/def/licenses/cc-by/4.0',
			'http://dcat-ap.de/def/licenses/CC%20BY%204.0'
		);
		$valuesCC0 = array(
			'http://publications.europa.eu/resource/authority/licence/CC0',
			'http://creativecommons.org/publicdomain/zero/1.0/',
			'http://dcat-ap.de/def/licenses/cc-zero'
		);

		$countCCBYcomparable = 0;
		$countCC0comparable = 0;
		$countRestrictive = 0;
		$countUnknown = 0;
		$countCCBY = 0;
		$countCC0 = 0;

		foreach($result as $line) {
			if (isset($line->mapped)) {
				$line->license = $line->mapped;
			}

			if (!isset($line->license)) {
				$countUnknown += intval($line->count->value);
			} else if (in_array($line->license->value, $valuesCCBYcomparable)) {
				$countCCBYcomparable += intval($line->count->value);
			} else if (in_array($line->license->value, $valuesCC0comparable)) {
				$countCC0comparable += intval($line->count->value);
			} else if (in_array($line->license->value, $valuesRestrictive)) {
				$countRestrictive += intval($line->count->value);
			} else if (in_array($line->license->value, $valuesCCBY)) {
				$countCCBY += intval($line->count->value);
			} else if (in_array($line->license->value, $valuesCC0)) {
				$countCC0 += intval($line->count->value);
			} else {
				$countUnknown += intval($line->count->value);
			}
		}

		$count = array(
			'cc_0' => $countCC0,
			'cc_0_comparable' => $countCC0comparable,
			'cc_by' => $countCCBY,
			'cc_by_comparable' => $countCCBYcomparable,
			'restrictive' => $countRestrictive,
			'unknown' => $countUnknown,
		);

		$countsData[$catalog]->licenses = $count;

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

	function getEUaccessURLInsights($catalog) {
		global $basePath;
		global $loadedIObjects;

		$date = date('Y-m-d');
		$datetime = new DateTime($date);
		$fileDate = $basePath . 'hvd-access-url-date/' . date('Y-m') . '/hvd-access-date-' . $date . '.json';
		$accessData = (array) loadCronjobData($fileDate);

		foreach($accessData as $data) {
			if (!is_null($data->accessURL) && ('' !== $data->accessURL)) {
				$iObject = findIObjectByURL($data->accessURL);

				if (is_null($iObject) && !is_null($data->accessURL) && ('' !== $data->accessURL)) {
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
		}

		return true;
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

			$modified = $object->countDataServicesTimestamp;
			if (is_null($modified)) {
				$now = microtime(true);

				getEUCountDataServicesData($catalog);

				$object->countDataServicesDuration = round(microtime(true) - $now, 3);
				$object->countDataServicesTimestamp = date('Y-m-d H:i:s');

				return $data;
			}

			$modified = $object->countLicensesTimestamp;
			if (is_null($modified)) {
				$now = microtime(true);

				getEUCountLicensesData($catalog);

				$object->countLicensesDuration = round(microtime(true) - $now, 3);
				$object->countLicensesTimestamp = date('Y-m-d H:i:s');

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

			$modified = $object->distributionsInsightsTimestamp;
			if (is_null($modified)) {
				$now = microtime(true);
				$stamp = true;

				if (getEUcatalogGovData() === $catalog) {
					$stamp = getEUaccessURLInsights($catalog);
					++$object->distributionsInsightsBuster;
				}

				if ($stamp) {
					$object->distributionsInsightsDuration = round(microtime(true) - $now, 3);
					$object->distributionsInsightsTimestamp = date('Y-m-d H:i:s');
				}

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