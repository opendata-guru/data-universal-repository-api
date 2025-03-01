<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$ret = 'empty';

	// ckan
	// https://ckan.govdata.de/harvest/about/open-data-brandenburg-harvester

	// entryscape
	// https://register.opendata.sachsen.de/organization/475/information

	// GDI-DE
	// https://gdk.gdi-de.org/gdk_harvesting/#/

	echo json_encode($ret);
?>