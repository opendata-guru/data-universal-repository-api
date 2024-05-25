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

	include('helper/_provider.php');

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

	$obj = [];
	$obj['pid'] = providerGetPID($pObject);
	$obj['sid'] = providerGetSID($pObject);
	$obj['url'] = providerGetServerURL($pObject);

	echo json_encode($obj);
?>