<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_link.php');
	include('live-system/_cms.php');
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
		include 'live-system/live-system-ckan.php';
		liveSystemCKAN($link->url);
	} else if ('Piveau' === $link->system) {
		include 'live-system/live-system-piveau.php';
		liveSystemPiveau($link->url);
	} else if ('ArcGIS' === $link->system) {
		include 'live-system/live-system-arcgis.php';
		liveSystemArcGIS($link->url);
	} else if ('EntryStore' === $link->system) {
		include 'live-system/live-system-entrystore.php';
		liveSystemEntryStore($link->url);
	} else if ('Opendatasoft' === $link->system) {
		include 'live-system/live-system-opendatasoft.php';
		liveSystemOpendatasoft($link->url);
	} else if ('rdf' === $link->system) {
		include 'live-system/live-system-rdf.php';
		liveSystemRDF($link->url);
	} else if ('SPARQL' === $link->system) {
		include 'live-system/live-system-sparql.php';
		liveSystemSPARQL($link->url);
	} else if ('unknown' !== $link->system) {
		echo json_encode((object) array(
			'cms' => getCMS($link->url),
			'extensions' => null,
			'system' => $link->system,
			'url' => $link->url,
			'version' => '',
		));
	} else {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. The underlying system could not be detected',
		));
	}
?>