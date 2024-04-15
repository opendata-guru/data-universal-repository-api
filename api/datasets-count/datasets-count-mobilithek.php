<?php
	function getJSONLDRequest($url) {
		$headers = [
			'Accept: application/ld+json',
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$ret = curl_exec($ch);
		curl_close ($ch);

		return $ret;
	}

	function datasetsCountMobilithek($url) {
		$countSuffix = '/mobilithek/api/v1.0/export/datasets/mobilithek?page=0&size=1';

		$uri = $url . $countSuffix;
		$json = json_decode(getJSONLDRequest($uri));
		$graph = get_object_vars($json)['@graph'];

		$count = 0;

		foreach ($graph as $item) {
			$obj = get_object_vars($item);
			if ($obj['@type'] == 'hydra:PagedCollection') {
				$count = $obj['totalItems'];
			}
		}

		echo json_encode((object) array(
			'number' => intval($count),
		));
	}
?>