<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$pObject = null;

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	} else {
		include('helper/_provider.php');
		include('helper/_sobject.php');
		include('helper/_lobject.php');

		$provider = getProvider();
		$pid = $provider->parameter;

		if ($pid === 'random') {
			$index = rand(0, count($loadedProviders) - 1);
			$pObject = $loadedProviders[$index];
		} else if ($provider->error) {
			header($provider->error->header);
			echo json_encode((object) array(
				'error' => $provider->error->error,
				'message' => $provider->error->message,
			));
			exit;
		} else {
			$pObject = findPObjectByPID($pid);
		}
	}

	$sid = providerGetSID($pObject);
	$sObject = findSObject($sid);

	$pID = providerGetPID($pObject);

	$count = [];
	$path = '../api-data/counts-pid/' . $pID . '.json';

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
	$obj['pid'] = $pID;
	$obj['sobject'] = $sObject;
	$obj['count'] = $count;

	echo json_encode($obj);
?>