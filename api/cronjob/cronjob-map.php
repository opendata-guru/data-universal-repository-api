<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include('../helper/_lobject.php');
	include('../helper/_sobject.php');

	$filePath = '../../govdata/assets/map-' . date('Y') . '/' . date('Y-m-d') . '-map.geojson';

	function curl($url) {
		$headers = [
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
		];

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$ret = curl_exec($curl);
		curl_close($curl);

		return $ret;
	}

	function getLinksCount() {
		$uri = 'https://opendata.guru/api/2';
		$uri .= '/l/count/today';

		$data = curl($uri);
		$json = json_decode($data);

		$ret = [];

		foreach($json as $lid => $count) {
			if ($count > 0) {
				$ret[$lid] = $count; // this could overwrite old values. Save the highest one
			}
		}

		return $ret;
	}

	function getRSList($counts) {
		global $loadedLObjects;

		$ret = [];

		foreach($counts as $lid => $count) {
			$lobject = findLObjectByLID($lid);
			$sobject = findSObject($lobject['sid']);

			$geocoding = $sobject ? $sobject->geocoding : null;
			$germanRegionalKey = $geocoding ? $geocoding['germanRegionalKey'] : null;

			if ($germanRegionalKey) {
				$ret[] = $germanRegionalKey;
			}
		}

		$ret = array_unique($ret);
		return $ret;
	}

	function sortRS($a, $b) {
		if (strlen($a) == strlen($b)) {
			if ($a == $b) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
		}
		return (strlen($a) < strlen($b)) ? -1 : 1;
	}

	function getGeoJSON($param) {
		$headers = [
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
		];

		$uri = 'https://' . $_SERVER['HTTP_HOST'] . htmlspecialchars($_SERVER['REQUEST_URI']);
		$uri = dirname(dirname($uri));

		$encodedParams = str_replace('%2C', ',', urlencode($param));
		$uri .= '/convert-ars.php';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'ars=' . $encodedParams);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$ret = curl_exec($ch);

		curl_close($ch);

		return $ret;
	}

	$dir = dirname($filePath);
	if (!file_exists($dir)) {
		mkdir($dir, 0777, true);
	}

	if (!file_exists($filePath)) {
		$counts = getLinksCount();
		$rs = getRSList($counts);
		usort($rs, 'sortRS');

		$param = implode(',', $rs);
		$data = getGeoJSON($param);

		file_put_contents($filePath, $data);
	}

	echo json_encode(array('result' => 'done'));
?>