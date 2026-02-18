<?php
	function countDatasetsCzech($url) {
		$catalogSuffix = '/api/v2/dataset?language=en&keywordLimit=0&publisherLimit=0&fileTypeLimit=0&dataServiceTypeLimit=0&themeLimit=0&isPartOfLimit=0&offset=0&limit=0&sort=title%20asc';

		$count = 0;

		$uri = $url . $catalogSuffix;
		$json = json_decode(file_get_contents($uri));
		$graph = get_object_vars($json)['@graph'];

		$count = 0;

		foreach ($graph as $item) {
			$obj = get_object_vars($item);
			if ($obj['@type'] == 'Metadata') {
				$count = $obj['datasets_count'];
			}
		}

		echo json_encode((object) array(
			'number' => intval($count),
		));
	}
?>