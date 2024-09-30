<?php
	function datasetsFindCKAN($url, $identifier) {
		$packageSearchSuffix = '/api/3/action/package_search?q=identifier:';

		// Q: Can you find records using keywords using our CKAN API?
		// A: Yes, that is possible. Here is an example:
		//    _path_to_/ckan/api/3/action/package_search?fq=tags:economy

		$json = json_decode(get_contents($url . $packageSearchSuffix . $identifier));
		$results = [];

		if ($json && $json->result) {
			$results = $json->result->results;
		}

		echo json_encode($results);
	}
?>