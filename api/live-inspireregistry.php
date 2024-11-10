<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	include('helper/_link.php');

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	}

	$lang = 'en';
	$uri = 'http://inspire.ec.europa.eu/layer/layer.' . $lang . '.json';
	$json = json_decode(get_contents($uri));

	$data = [];

	if ($json && $json->register && $json->register->containeditems) {
		foreach($json->register->containeditems as $item) {
			if ($item && $item->layer) {
				$spatial = trim($item->layer->spatialobjecttype->text);
				$layerName = $item->layer->layername->text;
				$schemaName = end(explode('/', $item->layer->applicationschema->uri)) . ':' . $spatial;
				$themeName = end(explode('/', $item->layer->theme->uri)) . ':' . $spatial;

				$data[] = (object) array(
					'id' => $item->layer->id,
					'layername' => $layerName,
					'schemaname' => $schemaName,
					'themename' => $themeName,
					'label_' . $lang => $item->layer->label->text,
				);
			}
		}
	}

	$lang = 'de';
	$uri = 'http://inspire.ec.europa.eu/layer/layer.' . $lang . '.json';
	$json = json_decode(get_contents($uri));

	if ($json && $json->register && $json->register->containeditems) {
		foreach($json->register->containeditems as $item) {
			if ($item && $item->layer) {
				$found_key = array_search($item->layer->id, array_column($data, 'id'));
				$data[$found_key]->{'label_' . $lang} = $item->layer->label->text;
			}
		}
	}

	$ret = $data;

	echo json_encode($ret);
?>