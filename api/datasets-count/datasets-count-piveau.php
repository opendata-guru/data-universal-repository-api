<?php
	function datasetsCountPiveau($url) {
		$countSuffix = '/api/hub/search/search?filter=dataset';

		$uri = $url . $countSuffix;
		$json = json_decode(file_get_contents($uri));

		echo json_encode((object) array(
			'number' => $json->result->count,
		));
	}
?>