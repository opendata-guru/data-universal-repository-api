<?php
	header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$paramARS = '';

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
		$paramARS = htmlspecialchars($_POST['ars']);
		if ($paramARS === '') {
			$paramARS = htmlspecialchars($_GET['ars']);
		}
	} else if ('GET' === $_SERVER['REQUEST_METHOD']) {
		$paramARS = htmlspecialchars($_GET['ars']);
	} else {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	}

	if ($paramARS === '') {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Parameter \'ars\' is not set',
		));
		exit;
	}

	$paramARS = preg_replace("/[^0-9,]/", '', $paramARS);
	$listRS = explode(',', $paramARS);

	function loadGeoJSON($filePath) {
		$dir = dirname($filePath);
		mkdir($dir, 0777, true);

		$data = file_get_contents($filePath);
		if (false === $data) {
			return null;
		}

		return $data;
	}

	function saveGeoJSON($filePath, $data) {
		file_put_contents($filePath, $data);
	}

	function getGeoJSON($rs) {
		$rs = substr($rs, 0, 15);
		$filePath = '../api-data/geojson-' . date('Y') . '/' . date('Y-m') . '/' . $rs . '.geojson';

		$data = loadGeoJSON($filePath);

		if (is_null($data)) {
			// vg250 - Verwaltungsgebiete 1:250 000 (VG250)
			// Das Produkt VG250-EW enthält Einwohnerzahlen und Katasterflächen
			$service = 'wfs_vg250?TYPENAMES=vg250:vg250_';
			$layer = 'lan';
			$output = '&OUTPUTFORMAT=application%2Fjson';
			$crs = '&srsName=urn:ogc:def:crs:EPSG::4326';
			$getFeature = '&Service=WFS&Version=2.0.0&Request=GetFeature';
			$filter = '&FILTER=%3CFilter%3E%3CPropertyIsEqualTo%3E%3CValueReference%3Ears%3C/ValueReference%3E%3CLiteral%3E' . $rs . '%3C/Literal%3E%3C/PropertyIsEqualTo%3E%3C/Filter%3E';

			// https://sg.geodatenzentrum.de/web_public/gdz/dokumentation/deu/vg250_01-01.pdf
			if (0 === strlen($rs)) {
				$layer = 'sta'; // Staat
				$filter = '';
			} else if (2 === strlen($rs)) {
				$layer = 'lan'; // Land
			} else if (3 === strlen($rs)) {
				$layer = 'rbz'; // Regierungsbezirk
			} else if (5 === strlen($rs)) {
				$layer = 'krs'; // Kreis
			} else if (9 === strlen($rs)) {
				$layer = 'vwg'; // Verwaltungsgemeinschaft
			} else if (12 === strlen($rs)) {
				$layer = 'gem'; // Gemeinde
			}

			$uri = 'https://sgx.geodatenzentrum.de/' . $service . $layer . $getFeature . $output . $crs . $filter;

			$data = file_get_contents($uri);

			saveGeoJSON($filePath, $data);
		}

		return $data;
	}

	function concatFeatures(&$geoJSON, $geoJSON2) {
		if ($geoJSON2) {
//			$geoJSON->bbox = $geoJSON2->bbox;
			$geoJSON->features = array_merge($geoJSON->features, $geoJSON2->features);
		}
	}

	$geojson = (object) [
		'type' => 'FeatureCollection',
		'crs' => array(
			'type' => 'name',
			'properties' => array(
				'name' => 'urn:ogc:def:crs:EPSG::4326'
			)
		),
//		'bbox' => [],
		'features' => []
/*
  ["totalFeatures"]=>
  int(5)
  ["numberMatched"]=>
  int(5)
  ["numberReturned"]=>
  int(5)
  ["timeStamp"]=>
  string(24) "2023-06-22T20:40:58.575Z"
  ["bbox"]=>
  array(4) {
    [0]=>
    float(7.521)
    [1]=>
    float(53.3598)
    [2]=>
    float(11.6725)
    [3]=>
    float(55.0992)
  }
*/
	];

	foreach($listRS as $rs) {
		$data = json_decode(getGeoJSON($rs), false);
		concatFeatures($geojson, $data);
	}

	echo json_encode($geojson);
?>