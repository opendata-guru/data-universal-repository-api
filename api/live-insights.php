<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('live-insights/live-insights-curl.php');
	include('live-insights/live-insights-parser.php');

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	}

	$parameterURL = html_entity_decode(trim(htmlspecialchars($_GET['url'])));

	if ($parameterURL == '') {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Parameter \'url\' is not set',
		));
		exit;
	}

	// wrong
	// https://geo.sv.rostock.de/inspire/plu-localplans/download
	// https://geo.sv.rostock.de/inspire/plu-localplans/view
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/download
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/view
	// right
	// https://geo.sv.rostock.de/inspire/plu-localplans/download?service=WFS&version=2.0.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/plu-localplans/view?service=WMS&version=1.3.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/download?service=WFS&version=2.0.0&request=GetCapabilities
	// https://geo.sv.rostock.de/inspire/tn-publictransitstops/view?service=WMS&version=1.3.0&request=GetCapabilities

	$ret = (object) array(
		'passes' => [],
	);

	$content = curl($parameterURL);
	$parsed = parser($content);
	unset($content->content);
	$pass = (object) array(
		'file' => $content,
		'content' => $parsed,
	);
	$ret->passes[] = $pass;

	echo json_encode($ret);
?>