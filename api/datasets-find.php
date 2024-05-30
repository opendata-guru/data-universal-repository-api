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

	$parameterIdentifier = htmlspecialchars($_GET['identifier']);
	if ($parameterIdentifier == '') {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Parameter \'identifier\' is not set',
		));
		exit;
	}

	if ('CKAN' === $link->system) {
		include 'datasets-find/datasets-find-ckan.php';
		datasetsFindCKAN($link->url, $parameterIdentifier);
/*	} else if ('Piveau' === $link->system) {
		include 'datasets-find/datasets-find-piveau.php';
		datasetsFindPiveau($link->url, $parameterIdentifier);*/
/*	} else if ('ArcGIS' === $link->system) {
		include 'datasets-find/datasets-find-arcgis.php';
		datasetsFindArcGIS($link->url, $parameterIdentifier);*/
/*	} else if ('datenadler' === $link->system) {
		include 'datasets-find/datasets-find-datenadler.php';
		datasetsFindDatenadler($link->url, $parameterIdentifier);*/
/*	} else if ('EntryStore' === $link->system) {
		include 'datasets-find/datasets-find-entrystore.php';
		datasetsFindEntryStore($link->url, $parameterIdentifier);*/
/*	} else if ('geoportal.de' === $link->system) {
		include 'datasets-find/datasets-find-geoportalde.php';
		datasetsFindGeoportalDE($link->url, $parameterIdentifier);*/
/*	} else if ('mcloud' === $link->system) {
		include 'datasets-find/datasets-find-mcloud.php';
		datasetsFindMCloud($link->url, $parameterIdentifier);*/
/*	} else if ('mobilithek' === $link->system) {
		include 'datasets-find/datasets-find-mobilithek.php';
		datasetsFindMobilithek($link->url, $parameterIdentifier);*/
/*	} else if ('Opendatasoft' === $link->system) {
		include 'datasets-find/datasets-find-opendatasoft.php';
		datasetsFindOpendatasoft($link->url, $parameterIdentifier);*/
/*	} else if ('Czech' === $link->system) {
		include 'datasets-find/datasets-find-czech.php';
		datasetsFindCzech($link->url, $parameterIdentifier);*/
/*	} else if ('Spain' === $link->system) {
		include 'datasets-find/datasets-find-spain.php';
		datasetsFindSpain($link->url, $parameterIdentifier);*/
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