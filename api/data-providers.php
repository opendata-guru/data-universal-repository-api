<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('suppliers/_semantic.php');

	$dataProviders = [];

	foreach($mapping as $line) {
		$obj = [];
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