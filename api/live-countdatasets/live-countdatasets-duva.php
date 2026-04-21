<?php
/*	function filterDatasets($var) {
		if ($var->{'@type'} === 'dcat:Catalog') {
			var_dump($var);
		} else if (!in_array($var->{'@type'}, ['foaf:Organization','foaf:Person','vcard:Individual','dct:PeriodOfTime','dcat:Distribution'])) {
			var_dump($var->{'@type'});
		}

		return $var->{'@type'} === 'dcat:Dataset';
	}*/

	function scrapeWebsite($html) {
		$start = stripos($html, '<table>') + 7;
		$end = stripos($html, '</table>', $start);
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));

		$body = explode('<tr>', $html);
		if ($body[0] === '') {
			array_shift($body);
		}

		// remove table header
		array_shift($body);

		return count($body);
	}

	function filterCatalog($var) {
		return $var->{'@type'} === 'dcat:Catalog';
	}

	function countDatasetsDUVA($url) {
//		$countSuffix = '/duva2dcat/';
//		$countSuffix = '/duva2dcat/catalog.ttl';
		$countSuffix = '/duva2dcat/catalog.jsonld';
//		$countSuffix = '/duva2dcat/catalog.rdf';
//		$countSuffix = '/duva2dcat/dcat/catalog';
		$dcatSuffix = '/dcat-ap/catalog.jsonld';
		$dcatFileListSuffix = '/dcat-ap/';

		$uri = $url . $countSuffix;
		$json = json_decode(get_contents($uri));

		$catalog = [];
		$count = 0;

		if ($json === null) {
			$uri = $url . $dcatSuffix;
			$json = json_decode(get_contents($uri));
		}

		if ($json === null) {
			$uri = $url . $dcatFileListSuffix;
			$count = scrapeWebsite(get_contents($uri));
		}

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