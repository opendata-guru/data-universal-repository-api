<?php
	function suppliersSPARQL($url, $pid) {
		$endpoint = $url . '?query=';
		$sparql = '
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX dcat: <http://www.w3.org/ns/dcat#>
PREFIX dcatde: <http://dcat-ap.de/def/dcatde/>

SELECT ?contributorid (COUNT(?contributorid) as ?datasets) WHERE {
  ?dataset a dcat:Dataset .
  ?dataset dcatde:contributorID ?contributorid_ .
  BIND (STR(?contributorid_) as ?contributorid)
}
GROUP BY ?contributorid
		';

		$opts = [
			"http" => [
				"method" => "GET",
				"header" => "Accept: application/sparql-results+json,*/*;q=0.9\r\n"
			]
		];
		$context = stream_context_create($opts);

		$json = json_decode(file_get_contents($endpoint . urlencode($sparql), false, $context));
		$suppliers = $json ? $json->results->bindings : [];

		$data = [];

		foreach ($suppliers as $item) {
			$contributorId = $item->contributorid->value;
			$value = $item->datasets->value;

			$data[] = semanticContributor($uriDomain, $pid, array(
				'id' => $contributorId,
				'name' => $contributorId,
				'title' => $contributorId,
				'created' => '',
				'packages' => intval($value),
				'uri' => $contributorId
			));
		}

		echo json_encode($data);
	}
?>