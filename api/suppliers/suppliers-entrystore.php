<?php
	function suppliersEntryStore($url) {
		$entryScapeSuffix = '/store/';

		$uriDomain = $url;
		$uriDomain = end(explode('/',$uriDomain));
		$uri = $url . $entryScapeSuffix;

		$query = 'search' .
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
			'&limit=0' .
			'&facetFields=' .
				'metadata.predicate.uri.9259d4c1';           // organizations
/*				'metadata.predicate.literal_s.b5d28d0a,' .   // filetype
				'metadata.predicate.uri.0ce6b231,' .         // groups
				'metadata.predicate.literal_s.a6424133,' .   // keywords
				'metadata.predicate.uri.23fc4665,' .         // access rights
				'related.metadata.predicate.uri.3494e2ce,' . // licenses
				'rdfType,' .                                 // type
				'metadata.predicate.literal_s.759a4eb7,' .   // ?
				'metadata.predicate.uri.2198789d,' .         // ?
				'metadata.predicate.uri.1c06f647';           // ?
*/

		function getRessourceQuery($organisation) {
			$str = str_replace(':', '\:', $organisation);
			$str = urlencode($str);
			$query = 'search' .
				'?type=solr' .
				'&query=' .
					'public:true' .
					'+AND+' .
					'(resource:' . $str . ')';

			return $query;
		}

		$json = json_decode(file_get_contents($uri . $query));
		$data = [];

		foreach($json->facetFields[0]->values as $organisation) {
			$name = $organisation->name;

			$ressourceQuery = getRessourceQuery($name);
			$ressourceJSON = json_decode(file_get_contents($uri . $ressourceQuery));
//			$ressourceJSON = file_get_contents($uri . $ressourceQuery);

			$id = $name;
			if ('urn:uuid' === substr($name, 0, 8)) {
				$id = end(explode(':', $name));
			} else if ('https://' === substr($name, 0, 8)) {
				$store = explode('/store/', $name);
				$resource = explode('/resource/', end($store));
				if ((count($store) === 2) && (count($resource) === 2)) {
					$uriPortal = end(explode('/', $store[0]));
					$uriPortal = preg_replace('#[^a-z0-9]#i', '', $uriPortal);
					$id = $uriPortal . '-' . implode('-', $resource);
				} else {
					$uriParts = explode('/', $name);
					array_shift($uriParts);
					array_shift($uriParts);
					$id = implode('-', explode('.', implode('-', $uriParts)));
				}
			}
			$id = preg_replace('#[^a-z0-9-]#i', '', $id);

			$metadata = $ressourceJSON->resource->children[0]->metadata;
			$nameEntry = get_object_vars(reset($metadata))['http://xmlns.com/foaf/0.1/name'];
			$title = $nameEntry[0]->value;

			$data[] = semanticContributor($uriDomain, array(
				'id' => $id,
				'name' => $name,
				'title' => $title,
				'created' => '',
				'packages' => $organisation->count,
				'uri' => ''
			));
		}

		echo json_encode($data);
	}
?>