<?php
	function countDatasetsEntrystore($url) {
		$query = '/store/search' .
			'?type=solr' .
			'&query=(' .
				'rdfType:http%5C%3A%2F%2Fwww.w3.org%2Fns%2Fdcat%23Dataset' .
				'+OR+' .
				'rdfType:http%5C%3A%2F%2Fentryscape.com%2Fterms%2FIndependentDataService' .
				'+OR+' .
				'rdfType:http%5C%3A%2F%2Fentryscape.com%2Fterms%2FServedByDataService' .
			')' .
			'+AND+' .
			'public:true' .
			'&limit=1';

		$json = json_decode(file_get_contents($url . $query));

		echo json_encode((object) array(
			'number' => $json->results,
		));
	}
?>