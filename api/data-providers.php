<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
	header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Please create an issue on GitHub for your change request',
			'createIssue' => 'https://github.com/opendata-guru/data-universal-repository-api/issues/new',
			'repository' => 'https://github.com/opendata-guru/data-universal-repository-api/tree/main/api-data',
		));
		return;
	}
	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	}

	include('suppliers/_semantic.php');

	$dataProviders = [];

	foreach($mapping as $line) {
		$obj = [];
		$obj['sid'] = $line[$mappingSID];
		$obj['title'] = $line[$mappingTitle];
		$obj['contributor'] = $line[$mappingContributor];
		$obj['type'] = $line[$mappingType];
		$obj['rs'] = $line[$mappingRS];
		$obj['associated_rs'] = $line[$mappingAssociatedRS];
		$obj['wikidata'] = $line[$mappingWikidata];
		$obj['link'] = $line[$mappingLink];

		$dataProviders[] = $obj;
	}

	header('HTTP/1.0 400 Bad Request');
	echo json_encode($dataProviders);
?>