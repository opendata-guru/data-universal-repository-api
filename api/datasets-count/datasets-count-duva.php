<?php
/*	function filterDatasets($var) {
		if ($var->{'@type'} === 'dcat:Catalog') {
			var_dump($var);
		} else if (!in_array($var->{'@type'}, ['foaf:Organization','foaf:Person','vcard:Individual','dct:PeriodOfTime','dcat:Distribution'])) {
			var_dump($var->{'@type'});
		}

		return $var->{'@type'} === 'dcat:Dataset';
	}*/

	function filterCatalog($var) {
		return $var->{'@type'} === 'dcat:Catalog';
	}

	function datasetsCountDUVA($url) {
//		$countSuffix = '/duva2dcat/';
//		$countSuffix = '/duva2dcat/catalog.ttl';
		$countSuffix = '/duva2dcat/catalog.jsonld';
//		$countSuffix = '/duva2dcat/catalog.rdf';
//		$countSuffix = '/duva2dcat/dcat/catalog';
		$dcatSuffix = '/dcat-ap/catalog.jsonld';

		$uri = $url . $countSuffix;
		$json = json_decode(get_contents($uri));

		if ($json === null) {
			$uri = $url . $dcatSuffix;
			$json = json_decode(get_contents($uri));
		}

		$catalog = [];
		$count = 0;

		if ($json) {
			$catalog = array_filter($json->{'@graph'}, 'filterCatalog');
		}

		if (count($catalog) === 1) {
			$obj = reset($catalog);
			$count = count($obj->dataset);
		}

		echo json_encode((object) array(
			'number' => $count,
		));
	}
?>