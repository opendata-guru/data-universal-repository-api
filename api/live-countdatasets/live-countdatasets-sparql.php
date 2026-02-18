<?php
	function countDatasetsSPARQL($url) {
		$endpoint = $url . '?query=';
		$sparql = '
PREFIX dcat: <http://www.w3.org/ns/dcat#>
SELECT (COUNT(?dataset) AS ?datasets) WHERE {
	?dataset a dcat:Dataset .
}
		';

		$opts = [
			"http" => [
				"method" => "GET",
				"header" => "Accept: application/sparql-results+json,*/*;q=0.9\r\n"
			]
		];
		$context = stream_context_create($opts);

		$json = json_decode(file_get_contents($endpoint . urlencode($sparql), false, $context));

		$datasets = $json ? $json->results->bindings[0]->datasets->value : '';

		echo json_encode((object) array(
			'number' => intval($datasets),
		));
	}
?>