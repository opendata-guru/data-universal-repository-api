<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization, Accept, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Authorization: true');
    header('Content-Type: application/json; charset=utf-8');

	include('helper/_iobject.php');

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
		include('helper/_post.php');

		if (validPost()) {
			echo json_encode(postIObject());
		} else {
			header('HTTP/1.0 401 Unauthorized');
			echo json_encode((object) array(
				'error' => 401,
				'message' => 'Unauthorized. Please create an issue on GitHub for your change request',
				'createIssue' => 'https://github.com/opendata-guru/data-universal-repository-api/issues/new',
				'repository' => 'https://github.com/opendata-guru/data-universal-repository-api/tree/main/api-data',
			));
		}

		return;
	}
//	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
//	}
//
//	echo json_encode($loadedSObjects);
?>