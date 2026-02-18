<?php
	function countDatasetsPiveau($url) {
		$PIVEAU_HOST_SEARCH_PIVEAU = 'search.piveau.';
		$PIVEAU_HOST_UI_PIVEAU = 'ui.piveau.';

		$host = '';

		$link = parse_url($url);
		if (str_starts_with($link['host'], $PIVEAU_HOST_SEARCH_PIVEAU)) {
    		$host = substr($link['host'], strlen($PIVEAU_HOST_SEARCH_PIVEAU));
		} else if (str_starts_with($link['host'], $PIVEAU_HOST_UI_PIVEAU)) {
    		$host = substr($link['host'], strlen($PIVEAU_HOST_UI_PIVEAU));
		}

		if ($host) {
			$link['host'] = $PIVEAU_HOST_SEARCH_PIVEAU . $host;
			$url = unparse_url($link);
			$countSuffix = '/search?filter=dataset';
		} else {
			$countSuffix = '/api/hub/search/search?filter=dataset';
		}

		$uri = $url . $countSuffix;
//		$json = json_decode(file_get_contents($uri));
		$json = json_decode(get_contents($uri));

		echo json_encode((object) array(
			'number' => $json->result->count,
		));
	}
?>