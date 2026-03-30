<?php
	function countDatasets($url) {
		$suffix = '/api/v1/count';

		$uri = $url . $suffix;
		$json = json_decode(get_contents_30sec($uri));

		echo json_encode((object) array(
			'number' => $json->count,
		));
	}
?>