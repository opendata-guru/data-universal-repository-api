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
	} else {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. The underlying system could not be detected',
		));
	}

exit;

	$link = htmlspecialchars($_GET['link']);

//	$piveauSuffix = '/api/hub/search/catalogues';
//	$arcGISHubSuffix = '.arcgis.com/';
//	$entryScapeSuffix = '/store/';
	$opendatasoftSuffix = '/api/v2/catalog/facets';
	$opendatasoftSuffix2_0 = '/api/explore/v2.0/catalog/facets';
	$opendatasoftSuffix2_1 = '/api/explore/v2.1/catalog/facets';

	if (substr($link, -strlen($piveauSuffix)) === $piveauSuffix) {
//		include 'system/system-piveau.php';
	} else if (substr($link, -strlen($arcGISHubSuffix)) === $arcGISHubSuffix) {
//		include 'system/system-arcgishub.php';
	} else if (substr($link, -strlen($entryScapeSuffix)) === $entryScapeSuffix) {
//		include 'system/system-entryscape.php';
	} else if ((substr($link, -strlen($opendatasoftSuffix)) === $opendatasoftSuffix) || (substr($link, -strlen($opendatasoftSuffix2_0)) === $opendatasoftSuffix2_0) || (substr($link, -strlen($opendatasoftSuffix2_1)) === $opendatasoftSuffix2_1)) {
		include 'system/system-opendatasoft.php';
	} else {
//		include 'system/system-ckan.php';
	}
?>