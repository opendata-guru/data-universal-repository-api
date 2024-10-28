<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	if ('POST' === $_SERVER['REQUEST_METHOD']) {
		include('helper/_post.php');

		if (validPost()) {
			include('helper/_iobject.php');

			$insights = postIID();

			if ($insights->error) {
				header($insights->error->header);
				echo json_encode((object) array(
					'error' => $insights->error->error,
					'message' => $insights->error->message,
				));
				exit;
			} else {
				$iObject = $insights->iObject;
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
		}
	} else if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	} else {
		include('helper/_iobject.php');

		$insights = getIObject();

		if ($insights->error) {
			header($insights->error->header);
			echo json_encode((object) array(
				'error' => $insights->error->error,
				'message' => $insights->error->message,
			));
			exit;
		} else {
			$iObject = $insights->iObject;
		}
	}

	echo json_encode($iObject);
?>