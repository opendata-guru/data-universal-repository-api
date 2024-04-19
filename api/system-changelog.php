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

	$refMajor = null;
	$refMinor = null;
	$refPatch = null;
	$refDate = null;
	$minorVersions = 0;
	$minorMax = 2;
	$minorString = '-.-';
	$minorColor = '';

	foreach ($content as $index=>$line) {
		if (substr($line, 0, 1) === '=') {
			$title = $content[$index - 1];
			$parts = explode(' ', $title);
			$date = new DateTime($parts[1]);
			$version = trim($parts[0], 'v. ');
			$major = explode('.', $version)[0];
			$minor = explode('.', $version)[1];
			$patch = explode('.', $version)[2];
			if ($refDate === null) {
				$refDate = new DateTime($parts[1]);
			}
			$color = '';

			if (($major . '.' . $minor) !== $minorString) {
				$minorString = $major . '.' . $minor;
				++$minorVersions;

				if ($refDate->diff($date)->format('%a') <= 5) {
					$color = 'green';
					$minorColor = 'yellow';
				} else if ($minorVersions <= $minorMax) {
					$minorColor = $color = 'yellow';
				} else {
					$minorColor = $color = 'red';
				}
			} else {
				$color = $minorColor;
			}

			$list[] = (object) array(
				'color' => $color,
				'date' => $date->format('Y-m-d'),
				'version' => $version,
			);
		}
	}

	echo json_encode((object) array(
		'history' => $list,
	));
?>