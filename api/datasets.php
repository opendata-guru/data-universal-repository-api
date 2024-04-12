<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_link.php');

	$link = getLink();

	if ($link->error) {
		header($link->error->header);
		echo json_encode((object) array(
			'error' => $link->error->error,
			'message' => $link->error->message,
		));
		exit;
	}

	if ('CKAN' === $link->system) {
		include 'datasets/datasets-ckan.php';
		datasetsCKAN($link->url);
	} else if ('Piveau' === $link->system) {
		include 'datasets/datasets-piveau.php';
		datasetsPiveau($link->url);
	} else if ('_ArcGIS' === $link->system) {
		include 'datasets/datasets-arcgis.php';
		datasetsArcGIS($link->url);
	} else if ('_datenadler' === $link->system) {
		include 'datasets/datasets-datenadler.php';
		datasetsDatenadler($link->url);
	} else if ('EntryStore' === $link->system) {
		include 'datasets/datasets-entrystore.php';
		datasetsEntryStore($link->url);
	} else if ('_geoportal.de' === $link->system) {
		include 'datasets/datasets-geoportalde.php';
		datasetsGeoportalDE($link->url);
	} else if ('_mcloud' === $link->system) {
		include 'datasets/datasets-mcloud.php';
		datasetsMCloud($link->url);
	} else if ('_Opendatasoft' === $link->system) {
		include 'datasets/datasets-opendatasoft.php';
		datasetsOpendatasoft($link->url);
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