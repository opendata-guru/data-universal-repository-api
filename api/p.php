<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_provider.php');

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
		include('helper/_post.php');

		if (validPost()) {
			echo json_encode(postPObject());
		} else {
			header('HTTP/1.0 403 Forbidden');
			echo json_encode((object) array(
				'error' => 403,
				'message' => 'Forbidden. Please create an issue on GitHub for your change request',
				'createIssue' => 'https://github.com/opendata-guru/data-universal-repository-api/issues/new',
				'repository' => 'https://github.com/opendata-guru/data-universal-repository-api/tree/main/api-data',
			));
		}

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
		$obj['url'] = providerGetURL($provider);
		$obj['deeplink'] = providerGetDeepLink($provider);

		$dataProviders[] = $obj;
	}

	header('HTTP/1.0 400 Bad Request');
	echo json_encode($dataProviders);
?>