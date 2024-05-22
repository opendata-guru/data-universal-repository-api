<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$basePath = '../../api-data/';
	$filePath = $basePath . 'temp-' . date('Y') . '/' . date('Y-m-d') . '-providers.json';

	function curl($url) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$ret = curl_exec($curl);
		curl_close($curl);

		return $ret;
	}

	function loadCronjobData($file) {
		$dir = dirname($file);
		mkdir($dir, 0777, true);

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
		include('../helper/_provider.php');

		$data = array();

		foreach($loadedProviders as $provider) {
			$data[] = array(
				'pid' => providerGetPID($provider),
				'suppliersDuration' => null,
				'suppliersTimestamp' => null,
				'countDatasetDuration' => null,
				'countDatasetTimestamp' => null,
			);
		}

		return $data;
	}

	function curlSuppliersData($pID) {
		$uri = 'https://opendata.guru/api/2';
		$uri .= '/suppliers?pID=' . urlencode($pID);

		$data = curl($uri);
		$json = json_decode($data);

		if ($json->error) {
			$json = null;
		}

		return $json;
	}

	function curlCountDatasetData($pID) {
		$uri = 'https://opendata.guru/api/2';
		$uri .= '/datasets/count?pID=' . urlencode($pID);

		$data = curl($uri);
		$json = json_decode($data);

		if ($json->error) {
			$json = null;
		}

		return $json;
	}

	function getSuppliersData($pID) {
		global $basePath;

		$date = date('Y-m-d');
		$fileDate = $basePath . 'counts-date/' . date('Y-m') . '/counts-' . $date . '.json';
		$countsData = (array) loadCronjobData($fileDate);

		$apiData = curlSuppliersData($pID);

		foreach($apiData as $supplier) {
			$lid = $supplier->lobject->lid;
			$count = $supplier->packages;

			$fileLID = $basePath . 'counts-lid/' . $lid . '.json';
			$lidData = (array) loadCronjobData($fileLID);

			$countsData[$lid] = $count;
			$lidData[$date] = $count;

			saveCronjobData($fileLID, $lidData);
		}

		saveCronjobData($fileDate, $countsData);
	}

	function getCountDatasetData($pID) {
		global $basePath;

		$date = date('Y-m-d');
		$fileDate = $basePath . 'counts-date/' . date('Y-m') . '/counts-' . $date . '.json';
		$countsData = (array) loadCronjobData($fileDate);

		$apiData = curlCountDatasetData($pID);
		$count = $apiData->number;

		$filePID = $basePath . 'counts-pid/' . $pID . '.json';
		$pidData = (array) loadCronjobData($filePID);

		$countsData[$pID] = $count;
		$pidData[$date] = $count;

		saveCronjobData($filePID, $pidData);
		saveCronjobData($fileDate, $countsData);
	}

	function getNextData($data) {
		foreach ($data as &$provider) {
			$pid = $provider->pid;

			$modified = $provider->suppliersTimestamp;
			if (!empty($pid) && is_null($modified)) {
				$now = microtime(true);

				getSuppliersData($pid);

				$provider->suppliersDuration = round(microtime(true) - $now, 3);
				$provider->suppliersTimestamp = date('Y-m-d H:i:s');

				return $data;
			}

			$modified = $provider->countDatasetTimestamp;
			if (!empty($pid) && is_null($modified)) {
				$now = microtime(true);

				getCountDatasetData($pid);

				$provider->countDatasetDuration = round(microtime(true) - $now, 3);
				$provider->countDatasetTimestamp = date('Y-m-d H:i:s');

				return $data;
			}

			// todo: get systems data
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