<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_link.php');

	$link = (object) array(
		'system' => htmlspecialchars($_GET['system']),
	);

	if ($link->system == '') {
		$error = (object) array(
			'error' => 400,
			'header' => 'HTTP/1.0 400 Bad Request',
			'message' => 'Bad Request. Parameter \'system\' is not set',
		);
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

	function addColors($list) {
		$refMajor = null;
		$refMinor = null;
		$refPatch = null;
		$refDate = null;
		$minorVersions = 0;
		$minorMax = 2;
		$minorString = '-.-';
		$minorColor = '';

		foreach ($list as $item) {
			$date = new DateTime($item->date);
			$major = explode('.', $item->version)[0];
			$minor = explode('.', $item->version)[1];
			$patch = explode('.', $item->version)[2];

			if ($refDate === null) {
				$refDate = new DateTime($item->date);
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

			$item->color = $color;
		}

		return $list;
	}

	if ('CKAN' === $link->system) {
		include 'system-changelog/system-changelog-ckan.php';
		systemChangelogCKAN();
	} else if ('Piveau' === $link->system) {
		include 'system-changelog/system-changelog-piveau.php';
		systemChangelogPiveau();
	} else if ('_ArcGIS' === $link->system) {
		include 'system-changelog/system-changelog-arcgis.php';
		systemChangelogArcGIS();
	} else if ('_EntryStore' === $link->system) {
		include 'system-changelog/system-changelog-entrystore.php';
		systemChangelogEntryStore();
	} else if ('_Opendatasoft' === $link->system) {
		include 'system-changelog/system-changelog-opendatasoft.php';
		systemChangelogOpendatasoft();
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