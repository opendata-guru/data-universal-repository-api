<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('_cms.php');

	$orgaListSuffix = '/api/3/action/organization_list';
	$groupListSuffix = '/api/3/action/group_list';
	$statusShowSuffix = '/api/3/action/status_show';

	$paramLink = htmlspecialchars($_GET['link']);
	if ($paramLink == '') {
		header("HTTP/1.0 400 Bad Request");
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Parameter "link" is not set',
		));
		exit;
	}

	if (($orgaListSuffix != substr($paramLink, -strlen($orgaListSuffix))) && ($groupListSuffix != substr($paramLink, -strlen($groupListSuffix)))) {
		header("HTTP/1.0 400 Bad Request");
		echo json_encode((object) array(
			'error' => 400,
			'message' => 'Bad Request. Parameter "link" must end with "' . $orgaListSuffix . '"',
		));
		exit;
	}

	if ($orgaListSuffix == substr($paramLink, -strlen($orgaListSuffix))) {
		$uriCKAN = substr($paramLink, 0, -strlen($orgaListSuffix));
	} else {
		$uriCKAN = substr($paramLink, 0, -strlen($groupListSuffix));
	}

	function get_contents($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

//	$json = json_decode(file_get_contents($uriCKAN . $statusShowSuffix));
	$json = json_decode(get_contents($uriCKAN . $statusShowSuffix));

	if ($json) {
		echo json_encode((object) array(
			'cms' => getCMS($uriCKAN),
			'extensions' => $json->result->extensions,
			'system' => 'CKAN',
			'url' => $json->result->site_url,
			'version' => $json->result->ckan_version,
		));
	} else {
		$cms = getCMS($uriCKAN);
		echo json_encode((object) array(
			'cms' => $cms,
			'extensions' => null,
			'system' => (substr($cms, 0, 6) === 'Drupal') ? 'DKAN' : null,
			'url' => $uriCKAN,
			'version' => null,
		));
	}
?>