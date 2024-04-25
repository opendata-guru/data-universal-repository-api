<?php
	function suppliersCzech($url) {
		$catalogSuffix = '/api/v2/dataset?language=en&keywordLimit=0&publisherLimit=1000&fileTypeLimit=0&dataServiceTypeLimit=0&themeLimit=0&isPartOfLimit=0&offset=0&limit=0&sort=title%20asc';

		$uriDomain = explode('/', $url)[2];
		$uri = $url . $catalogSuffix;

		$json = json_decode(file_get_contents($uri));
		$graph = get_object_vars($json)['@graph'];
		$data = [];

		foreach ($graph as $item) {
			$obj = get_object_vars($item);
			$facet = get_object_vars($obj['facet']);
			if (($obj['@type'] == 'Facet') && ($facet['@id'] == 'urn:publisher')) {
				$key = $obj['@id'];
				$value = $obj['count'];
				$id = preg_replace('#[^a-z0-9-]#i', '', $key);

				$data[] = semanticContributor($uriDomain, array(
					'id' => $id,
					'name' => $key,
					'title' => $key,
					'created' => '',
					'packages' => $value,
					'uri' => ''
				));
			}
		}

		echo json_encode($data);
	}
?>