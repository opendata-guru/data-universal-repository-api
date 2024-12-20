<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$sObject = null;

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	} else {
		include('helper/_sobject.php');
		include('helper/_lobject.php');

		$supplier = getSObject();
		$sid = $supplier->parameter;

		if ($sid === 'random') {
			$index = rand(0, count($loadedSObjects) - 1);
			$sObject = $loadedSObjects[$index];
		} else if ($supplier->error) {
			header($supplier->error->header);
			echo json_encode((object) array(
				'error' => $supplier->error->error,
				'message' => $supplier->error->message,
			));
			exit;
		} else {
			$sObject = $supplier->sObject;
		}
	}

	$sid = $sObject->sid;

	$lObjects = getLObjectChildren($sid);

	$obj = [];
	$obj['sid'] = $sid;
	$obj['lobjects'] = $lObjects;

	echo json_encode($obj);
?>