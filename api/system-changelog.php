<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_link.php');

	$CHANGELOG = 'https://raw.githubusercontent.com/ckan/ckan/master/CHANGELOG.rst';

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	}

	$md = get_contents($CHANGELOG);
	$content = preg_split("/\r\n|\n|\r/", $md);
	$list = [];

	foreach ($content as $index=>$line) {
		if (substr($line, 0, 1) === '=') {
			$title = $content[$index - 1];
			$parts = explode(' ', $title);

			$list[] = (object) array(
				'date' => $parts[1],
				'version' => trim($parts[0], 'v. '),
			);
		}
	}

	echo json_encode((object) array(
		'history' => $list,
	));
?>