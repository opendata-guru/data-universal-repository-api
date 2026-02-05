<?php
	// COMMENT THIS LINES
//	ini_set('display_errors', 1);
//	ini_set('display_startup_errors', 1);
//	error_reporting(E_ALL);

	function filterDatasets($var) {
		return $var->{'@type'} === 'dcat:Dataset';
	}

	function suppliersDUVA($url, $pid) {
//		$countSuffix = '/duva2dcat/';
//		$countSuffix = '/duva2dcat/catalog.ttl';
		$countSuffix = '/duva2dcat/catalog.jsonld';
//		$countSuffix = '/duva2dcat/catalog.rdf';
//		$countSuffix = '/duva2dcat/dcat/catalog';
		$dcatSuffix = '/dcat-ap/catalog.jsonld';

		$uri = $url . $countSuffix;
		$uriDomain = end(explode('/', $url));
		$json = json_decode(get_contents($uri));

		if ($json === null) {
			$uri = $url . $dcatSuffix;
			$json = json_decode(get_contents($uri));
		}

		$data = [];
		$publisher = [];

		if ($json) {
			$datasets = array_filter($json->{'@graph'}, 'filterDatasets');

			foreach($datasets as $dataset) {
				$key = array_search($dataset->publisher, array_column($json->{'@graph'}, '@id'));
				$name = $json->{'@graph'}[$key]->name;

				if (isset($publisher[$name])) {
					++$publisher[$name];
				} else {
					$publisher[$name] = 1;
				}
			}
		}

		if ($publisher) {
			foreach($publisher as $title => $count) {
				$id = preg_replace('#[^a-z0-9-]#i', '', $name);
				$name = $id;

				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $id,
					'name' => $name,
					'title' => $title,
					'created' => '',
					'packages' => $count,
					'uri' => '',
				));
			}
		}

		echo json_encode($data);
	}
?>