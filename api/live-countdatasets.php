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
		include 'live-countdatasets/live-countdatasets-ckan.php';
		countDatasetsCKAN($link->url);
	} else if ('Piveau' === $link->system) {
		include 'live-countdatasets/live-countdatasets-piveau.php';
		countDatasetsPiveau($link->url);
	} else if ('ArcGIS' === $link->system) {
		include 'live-countdatasets/live-countdatasets-arcgis.php';
		countDatasetsArcGIS($link->url);
	} else if ('datenadler' === $link->system) {
		include 'live-countdatasets/live-countdatasets-datenadler.php';
		countDatasetsDatenadler($link->url);
	} else if ('DUVA' === $link->system) {
		include 'live-countdatasets/live-countdatasets-duva.php';
		countDatasetsDUVA($link->url);
	} else if ('EntryStore' === $link->system) {
		include 'live-countdatasets/live-countdatasets-entrystore.php';
		countDatasetsEntryStore($link->url);
	} else if ('geoportal.de' === $link->system) {
		include 'live-countdatasets/live-countdatasets-geoportalde.php';
		countDatasetsGeoportalDE($link->url);
	} else if ('mcloud' === $link->system) {
		include 'live-countdatasets/live-countdatasets-mcloud.php';
		countDatasetsMCloud($link->url);
	} else if ('mobilithek' === $link->system) {
		include 'live-countdatasets/live-countdatasets-mobilithek.php';
		countDatasetsMobilithek($link->url);
	} else if ('Opendatasoft' === $link->system) {
		include 'live-countdatasets/live-countdatasets-opendatasoft.php';
		countDatasetsOpendatasoft($link->url);
	} else if ('Czech' === $link->system) {
		include 'live-countdatasets/live-countdatasets-czech.php';
		countDatasetsCzech($link->url);
	} else if ('rdf' === $link->system) {
		include 'live-countdatasets/live-countdatasets-rdf.php';
		countDatasetsRDF($link->url);
	} else if ('Spain' === $link->system) {
		include 'live-countdatasets/live-countdatasets-spain.php';
		countDatasetsSpain($link->url);
	} else if ('SPARQL' === $link->system) {
		include 'live-countdatasets/live-countdatasets-sparql.php';
		countDatasetsSPARQL($link->url);
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