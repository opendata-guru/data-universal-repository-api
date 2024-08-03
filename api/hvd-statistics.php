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
		include('helper/_sobject.php');

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

		$path = '../api-data/hvd-date/' . substr($date, 0, 7) . '/hvd-' . $date . '.json';

		if (!file_exists($path)) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Path parameter for \'date\' is invalid',
			));
			exit;
		}

		$json = json_decode(file_get_contents($path));

		$pid = 'p000';
		foreach ($json as $key => $value) {
			$identifier = end(explode('/', $key));
			$sid = null;
			// todo: get 'sid' from lObject with $pid and $identifier

			$hvd[] = array(
				'catalogURI' => $key,
				'sObject' => findSObject($sid),
				'datasets' => $value->datasets,
				'distributions' => $value->distributions,
			);
		}
	}

	echo json_encode($hvd);
?>