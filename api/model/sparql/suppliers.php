<?php
	function suppliers($url, $pid) {
		$endpoint = $url . '?query=';
		$sparql = '
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX dcat: <http://www.w3.org/ns/dcat#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>

SELECT ?name (COUNT(DISTINCT ?dataset) as ?datasets)
WHERE {
  ?dataset a dcat:Dataset ;
           dct:publisher ?publisher .
  ?publisher foaf:name ?name .
}
GROUP BY ?name
ORDER BY DESC(?datasets)
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
			$supplier = $item->name->value;
			$value = $item->datasets->value;

			$data[] = semanticContributor($uriDomain, $pid, array(
				'id' => $supplier,
				'name' => $supplier,
				'title' => $supplier,
				'created' => '',
				'packages' => intval($value),
				'uri' => $supplier
			));
		}

		echo json_encode($data);
	}
?>