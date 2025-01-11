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
	}

	include('helper/_date.php');

	$issues = [];
	$date = getDateParameter();

	if (is_null($date)) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Path parameter for \'date\' is invalid',
		));
		exit;
	}

	$path = '../api-data/monitor-date/' . substr($date, 0, 7) . '/monitor-' . $date . '.json';

	if (!file_exists($path)) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Path parameter for \'date\' is invalid',
		));
		exit;
	}

	$json = json_decode(file_get_contents($path));

	foreach ($json->errors as $error) {
		$pid = $error->pid;
		if (!isset($issues[$pid])) {
			$issues[$pid] = [];
		}

		$error->severity = 'error';
		unset($error->pid);
		$issues[$pid][] = $error;
	}

	foreach ($json->warnings as $warning) {
		$pid = $warning->pid;
		if (!isset($issues[$pid])) {
			$issues[$pid] = [];
		}

		$warning->severity = 'warning';
		unset($warning->pid);
		$issues[$pid][] = $warning;
	}

	foreach ($json->infos as $info) {
		$pid = $info->pid;
		if (!isset($issues[$pid])) {
			$issues[$pid] = [];
		}

		$info->severity = 'info';
		unset($info->pid);
		$issues[$pid][] = $info;
	}

	echo json_encode($issues);
?>