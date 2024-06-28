<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$pObject = null;

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
		include('helper/_post.php');

		if (validPost()) {
			include('helper/_provider.php');

			$provider = postPID();

			if ($provider->error) {
				header($provider->error->header);
				echo json_encode((object) array(
					'error' => $provider->error->error,
					'message' => $provider->error->message,
				));
				exit;
			} else {
				$pObject = $provider->pObject;
			}
		} else {
			header('HTTP/1.0 403 Forbidden');
			echo json_encode((object) array(
				'error' => 403,
				'message' => 'Forbidden. Please create an issue on GitHub for your change request',
				'createIssue' => 'https://github.com/opendata-guru/data-universal-repository-api/issues/new',
				'repository' => 'https://github.com/opendata-guru/data-universal-repository-api/tree/main/api-data',
			));
			exit;
		}
	} else if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	} else {
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
	}

	$obj = [];
	$obj['pid'] = providerGetPID($pObject);
	$obj['sid'] = providerGetSID($pObject);
	$obj['url'] = providerGetURL($pObject);
	$obj['deeplink'] = providerGetDeepLink($pObject);

	echo json_encode($obj);
?>