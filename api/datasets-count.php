<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_link.php');
	include('helper/_provider.php');

	$link = getLink();
	$provider = getProvider();
	$pid = '';

	if (($link->parameter !== '') && ($provider->parameter !== '')) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Both parameters \'link\' and \'pID\' are set',
		));
		exit;
	}
	if ($provider->parameter !== '') {
		if ($provider->error) {
			header($provider->error->header);
			echo json_encode((object) array(
				'error' => $provider->error->error,
				'message' => $provider->error->message,
			));
			exit;
		}
		$link = getLinkWithParam($provider->url);
		$pid = $provider->parameter;
	} else {
		$pObject = findPObjectByLink($link);
		if ($pObject) {
			$pid = providerGetPID($pObject);
		}
	}
	if ($link->error) {
		header($link->error->header);
		echo json_encode((object) array(
			'error' => $link->error->error,
			'message' => $link->error->message,
		));
		exit;
	}

	if ('CKAN' === $link->system) {
		include 'datasets-count/datasets-count-ckan.php';
		datasetsCountCKAN($link->url);
	} else if ('Piveau' === $link->system) {
		include 'datasets-count/datasets-count-piveau.php';
		datasetsCountPiveau($link->url);
	} else if ('ArcGIS' === $link->system) {
		include 'datasets-count/datasets-count-arcgis.php';
		datasetsCountArcGIS($link->url);
	} else if ('datenadler' === $link->system) {
		include 'datasets-count/datasets-count-datenadler.php';
		datasetsCountDatenadler($link->url);
	} else if ('DUVA' === $link->system) {
		include 'datasets-count/datasets-count-duva.php';
		datasetsCountDUVA($link->url);
	} else if ('EntryStore' === $link->system) {
		include 'datasets-count/datasets-count-entrystore.php';
		datasetsCountEntryStore($link->url);
	} else if ('geoportal.de' === $link->system) {
		include 'datasets-count/datasets-count-geoportalde.php';
		datasetsCountGeoportalDE($link->url);
	} else if ('mcloud' === $link->system) {
		include 'datasets-count/datasets-count-mcloud.php';
		datasetsCountMCloud($link->url);
	} else if ('mobilithek' === $link->system) {
		include 'datasets-count/datasets-count-mobilithek.php';
		datasetsCountMobilithek($link->url);
	} else if ('Opendatasoft' === $link->system) {
		include 'datasets-count/datasets-count-opendatasoft.php';
		datasetsCountOpendatasoft($link->url);
	} else if ('Czech' === $link->system) {
		include 'datasets-count/datasets-count-czech.php';
		datasetsCountCzech($link->url);
	} else if ('Spain' === $link->system) {
		include 'datasets-count/datasets-count-spain.php';
		datasetsCountSpain($link->url);
	} else if ('SPARQL' === $link->system) {
		include 'datasets-count/datasets-count-sparql.php';
		datasetsCountSPARQL($link->url);
	} else if ('unknown' !== $link->system) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Could not create a result for system \'' . $link->system . '\'',
		));
	} else {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. The underlying system could not be detected',
		));
	}
?>