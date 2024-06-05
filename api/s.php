<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_sobject.php');

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Please create an issue on GitHub for your change request',
			'createIssue' => 'https://github.com/opendata-guru/data-universal-repository-api/issues/new',
			'repository' => 'https://github.com/opendata-guru/data-universal-repository-api/tree/main/api-data',
			'sid' => createSID(),
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

	$dataSuppliers = [];

	foreach($loadedSObjects as $sObject) {
		$obj = [];
		$obj['sid'] = $sObject['sid'];
		$obj['title'] = [];
		$obj['title']['de'] = $sObject['title@DE'];
		$obj['title']['en'] = $sObject['title@EN'];
		$obj['type'] = $sObject['type'];
		$obj['sameAs'] = [];
		$obj['sameAs']['rs'] = $sObject['sameAsRS'];
		$obj['sameAs']['wikidata'] = $sObject['sameAsWikidata'];
		$obj['partOf'] = [];
		$obj['partOf']['rs'] = $sObject['partOfRS'];
		$obj['partOf']['wikidata'] = $sObject['partOfWikidata'];

		$dataSuppliers[] = $obj;
	}

	header('HTTP/1.0 400 Bad Request');
	echo json_encode($dataSuppliers);
?>