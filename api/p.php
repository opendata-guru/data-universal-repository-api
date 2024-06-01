<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_provider.php');

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 400 Bad Request');
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Please create an issue on GitHub for your change request',
			'createIssue' => 'https://github.com/opendata-guru/data-universal-repository-api/issues/new',
			'repository' => 'https://github.com/opendata-guru/data-universal-repository-api/tree/main/api-data',
			'pid' => createPID(),
		));
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

	$dataProviders = [];

	foreach($loadedProviders as $provider) {
		$obj = [];
		$obj['pid'] = providerGetPID($provider);
		$obj['sid'] = providerGetSID($provider);
		$obj['url'] = providerGetServerURL($provider);

		$dataProviders[] = $obj;
	}

	header('HTTP/1.0 400 Bad Request');
	echo json_encode($dataProviders);
?>