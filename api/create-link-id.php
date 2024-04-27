<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	// https://www.rechner.club/kombinatorik/anzahl-variationen-geordnet-ohne-wiederholung-berechnen
	// objects  | 61    | 61      | 61         | 61
	// draws    | 2     | 3       | 4          | 5
	// variants | 3,660 | 215,940 | 12,524,520 | 713,897,640

	$ALLOWED_CHARS = '0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$prefix = 'l';
	$length = 4;

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	}

	include('suppliers/_semantic.php');

	$usedLIDs = [];
	foreach($mapping as $line) {
		$usedLIDs[] = $line[$mappingLID];
	}
	$usedLIDs = array_filter($usedLIDs);

	do {
		$lid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
	} while(in_array($lid, $usedLIDs));

	echo json_encode((object) array(
		'lid' => $lid,
	));
?>