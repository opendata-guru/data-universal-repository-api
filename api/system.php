<?php
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

/*header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: X-Requested-With');
header('Content-Type: application/json; charset=utf-8');
echo json_encode($link);
exit;*/

	$link = htmlspecialchars($_GET['link']);

	$piveauSuffix = '/api/hub/search/catalogues';
	$arcGISHubSuffix = '.arcgis.com/';
	$entryScapeSuffix = '/store/';
	$opendatasoftSuffix = '/api/v2/catalog/facets';
	$opendatasoftSuffix2_0 = '/api/explore/v2.0/catalog/facets';
	$opendatasoftSuffix2_1 = '/api/explore/v2.1/catalog/facets';

	if (substr($link, -strlen($piveauSuffix)) === $piveauSuffix) {
		include 'system/system-piveau.php';
	} else if (substr($link, -strlen($arcGISHubSuffix)) === $arcGISHubSuffix) {
		include 'system/system-arcgishub.php';
	} else if (substr($link, -strlen($entryScapeSuffix)) === $entryScapeSuffix) {
		include 'system/system-entryscape.php';
	} else if ((substr($link, -strlen($opendatasoftSuffix)) === $opendatasoftSuffix) || (substr($link, -strlen($opendatasoftSuffix2_0)) === $opendatasoftSuffix2_0) || (substr($link, -strlen($opendatasoftSuffix2_1)) === $opendatasoftSuffix2_1)) {
		include 'system/system-opendatasoft.php';
	} else {
		include 'system/system-ckan.php';
	}
?>