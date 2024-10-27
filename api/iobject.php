<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

//	include('helper/_link.php');

/*	if ('POST' === $_SERVER['REQUEST_METHOD']) {
		include('helper/_post.php');

		if (validPost()) {
			include('helper/_sobject.php');

			$supplier = postSID();

			if ($supplier->error) {
				header($supplier->error->header);
				echo json_encode((object) array(
					'error' => $supplier->error->error,
					'message' => $supplier->error->message,
				));
				exit;
			} else {
				$sObject = $supplier->sObject;
			}
		} else {
			header('HTTP/1.0 401 Unauthorized');
			echo json_encode((object) array(
				'error' => 401,
				'message' => 'Unauthorized. Please create an issue on GitHub for your change request',
				'createIssue' => 'https://github.com/opendata-guru/data-universal-repository-api/issues/new',
				'repository' => 'https://github.com/opendata-guru/data-universal-repository-api/tree/main/api-data',
			));
			exit;
		}*/
//	} else if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
/*	} else {
		include('helper/_sobject.php');

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
	}*/

//	echo json_encode($sObject);
?>