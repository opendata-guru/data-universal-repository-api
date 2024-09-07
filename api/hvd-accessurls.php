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

		$path = '../api-data/hvd-access-url-date/' . substr($date, 0, 7) . '/hvd-access-date-' . $date . '.json';

		if (!file_exists($path)) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Path parameter for \'date\' is invalid',
			));
			exit;
		}

		$json = json_decode(file_get_contents($path));

		foreach($json as &$object) {
			$object->datasetIdentifier = $object->identifier;
			unset($object->identifier);

			$object->distributionAccessURL = $object->accessURL;
			unset($object->accessURL);
		}

		$hvd = $json;
	}

	echo json_encode($hvd);
?>