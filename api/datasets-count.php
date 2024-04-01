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
		include 'datasets-count/datasets-count-ckan.php';
		datasetsCountCKAN($link->url);
	} else if ('Piveau' === $link->system) {
		include 'datasets-count/datasets-count-piveau.php';
		datasetsCountPiveau($link->url);
	} else if ('_ArcGIS' === $link->system) {
		include 'datasets-count/datasets-count-arcgis.php';
		datasetsCountArcGIS($link->url);
	} else if ('EntryStore' === $link->system) {
		include 'datasets-count/datasets-count-entrystore.php';
		datasetsCountEntryStore($link->url);
	} else if ('Opendatasoft' === $link->system) {
		include 'datasets-count/datasets-count-opendatasoft.php';
		datasetsCountOpendatasoft($link->url);
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