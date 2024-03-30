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

	if ('_CKAN' === $link->system) {
		include 'quantity/quantity-ckan.php';
		quantityCKAN($link->url);
	} else if ('_Piveau' === $link->system) {
		include 'quantity/quantity-piveau.php';
		quantityPiveau($link->url);
	} else if ('_ArcGIS' === $link->system) {
		include 'quantity/quantity-arcgis.php';
		quantityArcGIS($link->url);
	} else if ('_EntryStore' === $link->system) {
		include 'quantity/quantity-entrystore.php';
		quantityEntryStore($link->url);
	} else if ('_Opendatasoft' === $link->system) {
		include 'quantity/quantity-opendatasoft.php';
		quantityOpendatasoft($link->url);
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