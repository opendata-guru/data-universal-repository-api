<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$lObject = null;

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	} else {
		include('helper/_lobject.php');

		$link = getLObject();
		$lid = $link->parameter;

		if ($lid === 'random') {
			$index = rand(0, count($loadedLObjects) - 1);
			$lObject = $loadedLObjects[$index];
		} else if ($link->error) {
			header($link->error->header);
			echo json_encode((object) array(
				'error' => $link->error->error,
				'message' => $link->error->message,
			));
			exit;
		} else {
			$lObject = findLObjectByLID($lid);
		}
	}

	include('helper/_provider.php');
	include_once('helper/_sobject.php');

	$sObject = findSObject($lObject['sid']);
	$pObject = findPObjectByPID($lObject['pid']);

	$count = [];
	$path = '../api-data/counts-lid/' . $lObject['lid'] . '.json';

	if (!file_exists($path)) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Path parameter for \'pID\' is invalid',
		));
		exit;
	}

	$count = json_decode(file_get_contents($path));

	$obj = [];
	$obj['lid'] = $lObject['lid'];
	$obj['pid'] = $lObject['pid'];
	$obj['pobject'] = [
		'pid' => providerGetPID($pObject),
		'sid' => providerGetSID($pObject),
		'url' => providerGetURL($pObject),
	];
	$obj['identifier'] = $lObject['identifier'];
	$obj['sid'] = $lObject['sid'];
	$obj['sobject'] = $sObject;
	$obj['count'] = $count;

	echo json_encode($obj);
?>