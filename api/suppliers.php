<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_link.php');
	include('suppliers/_semantic.php');

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
		include 'suppliers/suppliers-ckan.php';
		suppliersCKAN($link->url);
	} else if ('Piveau' === $link->system) {
		include 'suppliers/suppliers-piveau.php';
		suppliersPiveau($link->url);
	} else if ('ArcGIS' === $link->system) {
		include 'suppliers/suppliers-arcgis.php';
		suppliersArcGIS($link->url);
	} else if ('datenadler' === $link->system) {
		include 'suppliers/suppliers-datenadler.php';
		suppliersDatenadler($link->url);
	} else if ('EntryStore' === $link->system) {
		include 'suppliers/suppliers-entrystore.php';
		suppliersEntryStore($link->url);
	} else if ('geoportal.de' === $link->system) {
		include 'suppliers/suppliers-geoportalde.php';
		suppliersGeoportalDE($link->url);
	} else if ('mcloud' === $link->system) {
		include 'suppliers/suppliers-mcloud.php';
		suppliersMCloud($link->url);
	} else if ('mobilithek' === $link->system) {
		include 'suppliers/suppliers-mobilithek.php';
		suppliersMobilithek($link->url);
	} else if ('Opendatasoft' === $link->system) {
		include 'suppliers/suppliers-opendatasoft.php';
		suppliersOpendatasoft($link->url);
	} else if ('Spain' === $link->system) {
		include 'suppliers/suppliers-spain.php';
		suppliersSpain($link->url);
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