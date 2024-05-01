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

	function loadTempData() {
		global $filePath;

		$dir = dirname($filePath);
		mkdir($dir, 0777, true);

		$data = json_decode(file_get_contents($filePath));

		if (is_null($data)) {
			$data = array();
		}
		return $data;
	}

	function saveTempData($data) {
		global $filePath;

		file_put_contents($filePath, json_encode($data));
	}

	function getInitialData() {
		include('../helper/_provider.php');

		$data = array();

		foreach($loadedProviders as $provider) {
			$data[] = array(
				'pid' => providerGetPID($provider),
				'url' => providerGetServerURL($provider),
				'duration' => null,
				'modified' => null,
			);
		}

		return $data;
	}

	function getAPIData($link) {
		$uri = 'https://opendata.guru/api/2';
		$uri .= '/suppliers?link=' . urlencode($link);

		$data = curl($uri);
		$json = json_decode($data);

		if ($json->error) {
			$json = null;
		}

		return $json;
	}

	function getProviderData($providerId, $url) {
		$apiData = getAPIData($url);

		foreach($apiData as $newOrga) {
/*			$newOrga->packagesInId = $providerId;
			$newOrga->packagesInPortal = $portalTitle;
			$found = false;

			foreach($data as $existingOrga) {
				if ($newOrga->id == $existingOrga->id) {
//				if (($newOrga->id == $existingOrga->id) && ($newOrga->packagesInPortal == $existingOrga->packagesInPortal)) {
					$found = true;
					break;
				}
			}

			if ($found == false) {
				$data[] = $newOrga;
			}*/
		}
	}

	function getNextData($data) {
		foreach ($data as &$provider) {
			$url = $provider->url;
			$modified = $provider->modified;

			if (!empty($url) && is_null($modified)) {
				$now = microtime(true);

				getProviderData($provider->pid, $url);

				$provider->duration = round(microtime(true) - $now, 3);
				$provider->modified = date('Y-m-d H:i:s');
			}
		}

		return $data;
	}

	$data = loadTempData();
	$dataHash = md5(serialize($data));

	if (empty($data)) {
		$data = getInitialData();
	} else {
		$data = getNextData($data);
var_dump($data);
exit;
	}

	if ($dataHash == md5(serialize($data))) {
		echo json_encode(array('result' => 'done'));
	} else {
		saveTempData($data);

		echo json_encode(array('result' => 'in progress'));
	}
?>