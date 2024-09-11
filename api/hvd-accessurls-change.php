<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	} else {
		include('helper/_date.php');

		$hvd = array();
		$date = getDateParameter();

		if (is_null($date)) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Path parameter for \'date\' is invalid',
			));
			exit;
		}

		$yesterday = date('Y-m-d', strtotime($date . ' -1 day'));

		$path = '../api-data/hvd-access-url-date/' . substr($date, 0, 7) . '/hvd-access-date-' . $date . '.json';
		$pathYesterday = '../api-data/hvd-access-url-date/' . substr($yesterday, 0, 7) . '/hvd-access-date-' . $yesterday . '.json';

		if (!file_exists($path) || !file_exists($pathYesterday)) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Path parameter for \'date\' is invalid',
			));
			exit;
		}

		$jsonToday = json_decode(file_get_contents($path));
		$jsonYesterday = json_decode(file_get_contents($pathYesterday));
//		$jsonBoth = [];

		for ($t = count($jsonToday) - 1; $t >= 0; --$t) {
			for ($y = count($jsonYesterday) - 1; $y >= 0; --$y) {
				if (($jsonToday[$t]->identifier === $jsonYesterday[$y]->identifier) &&
					($jsonToday[$t]->accessURL === $jsonYesterday[$y]->accessURL)) {
//					$jsonBoth[] = $jsonToday[$t];

					array_splice($jsonToday, $t, 1);
					array_splice($jsonYesterday, $y, 1);
				}
			}
		}

		foreach($jsonToday as &$object) {
			$object->datasetIdentifier = $object->identifier;
			unset($object->identifier);

			$object->distributionAccessURL = $object->accessURL;
			unset($object->accessURL);
		}

		foreach($jsonYesterday as &$object) {
			$object->datasetIdentifier = $object->identifier;
			unset($object->identifier);

			$object->distributionAccessURL = $object->accessURL;
			unset($object->accessURL);
		}

		$hvd = (object) array(
			'added' => $jsonToday,
			'removed' => $jsonYesterday,
		);
	}

	echo json_encode($hvd);
?>