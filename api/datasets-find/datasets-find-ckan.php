<?php
	function datasetsFindCKAN($url, $identifier) {
		$packageSearchSuffix = '/api/3/action/package_search?q=identifier:';

		$json = json_decode(get_contents($url . $packageSearchSuffix . $identifier));
		$results = [];

		if ($json && $json->result) {
			$results = $json->result->results;
		}

		echo json_encode($results);
	}
?>