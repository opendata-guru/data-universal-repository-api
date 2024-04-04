<?php
	function suppliersOpendatasoft($url) {
		$opendatasoftSuffix = '/api/explore/v2.1/catalog/facets';

		$uriDomain = end(explode('/', $url));

		$source = file_get_contents($url . $opendatasoftSuffix);

		$data = [];

		$json = json_decode($source);
		$facets = $json->facets;
		$list = [];

		for ($f = 0; $f < count($facets); ++$f) {
			if ('publisher' === $facets[$f]->name) {
				$list = array_merge($list, $facets[$f]->facets);
			}
		}

		for ($l = 0; $l < count($list); ++$l) {
			$entry = $list[$l];

			$title = $entry->value;
			$name = preg_replace('#[^a-z0-9]#i', '', $entry->name);

			$count = $entry->count;

			$data[] = semanticContributor($uriDomain, array(
				'id' => $name,
				'name' => $name,
				'title' => $title,
				'created' => '',
				'packages' => $count,
				'uri' => ''
			));
		}

		echo json_encode($data);
	}
?>