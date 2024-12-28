<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$lObject = null;

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
/*		include('helper/_post.php');

		if (validPost()) {
			include('helper/_lobject.php');
			include('helper/_sobject.php');

			$link = postPID();

			if ($link->error) {
				header($link->error->header);
				echo json_encode((object) array(
					'error' => $link->error->error,
					'message' => $link->error->message,
				));
				exit;
			} else {
				$lObject = $link->pObject;
			}
		} else {*/
			header('HTTP/1.0 401 Unauthorized');
			echo json_encode((object) array(
				'error' => 401,
				'message' => 'Unauthorized. Please create an issue on GitHub for your change request',
				'createIssue' => 'https://github.com/opendata-guru/data-universal-repository-api/issues/new',
				'repository' => 'https://github.com/opendata-guru/data-universal-repository-api/tree/main/api-data',
			));
			exit;
//		}
	} else if ('GET' !== $_SERVER['REQUEST_METHOD']) {
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
	include('helper/_sobject.php');

	$sObject = findSObject($lObject['sid']);
	$pObject = findPObjectByPID($lObject['pid']);

//	$obj = [];
//	$obj['pid'] = providerGetPID($lObject);
	$lObject['pobject'] = [
		'pid' => providerGetPID($pObject),
		'sid' => providerGetSID($pObject),
		'url' => providerGetURL($pObject),
	];
	$lObject['sobject'] = $sObject;
//	$obj['url'] = providerGetURL($lObject);
//	$obj['deeplink'] = providerGetDeepLink($lObject);

	echo json_encode($lObject);
?>