<?php
	function datasetsCountOpendatasoft($url) {
		$countSuffix = '/api/v2/catalog/datasets?limit=0';
		$countSuffix2_0 = '/api/explore/v2.0/catalog/datasets?limit=0';
		$countSuffix2_1 = '/api/explore/v2.1/catalog/datasets?limit=0';

		$uri = $url . $countSuffix2_1;
		$json = json_decode(file_get_contents($uri));

		echo json_encode((object) array(
			'number' => $json->total_count,
		));
	}
?>