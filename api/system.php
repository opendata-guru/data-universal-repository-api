<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_link.php');
	include('system/_cms.php');

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
		include 'system/system-ckan.php';
		systemCKAN($link->url);
	} else if ('Piveau' === $link->system) {
		include 'system/system-piveau.php';
		systemPiveau($link->url);
	} else if ('ArcGIS' === $link->system) {
		include 'system/system-arcgis.php';
		systemArcGIS($link->url);
	} else if ('EntryStore' === $link->system) {
		include 'system/system-entrystore.php';
		systemEntryStore($link->url);
	} else if ('Opendatasoft' === $link->system) {
		include 'system/system-opendatasoft.php';
		systemOpendatasoft($link->url);
	} else {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. The underlying system could not be detected',
		));
	}
?>