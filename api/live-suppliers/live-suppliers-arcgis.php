<?php
	function getSiteInjection($html) {
		$start = stripos($html, 'id="site-injection"') + 19;
		$end = stripos($html, '</script>', $start);
		$length = $end - $start;
		$html = substr($html, $start, $length);

		$start = stripos($html, '"') + 1;
		$end = strripos($html, '"');
		$length = $end - $start;
		$html = substr($html, $start, $length);

		return json_decode(urldecode($html));
	}

	function getGroups($injection) {
		$data = $injection->site->data;
		$groups = [];

		if ($data->catalog) {
			$groups = $data->catalog->groups;
		} else {
			$groups = $data->catalogV2->scopes->item->filters[0]->predicates[0]->group->any;
		}

		return implode(rawurlencode(', '), $groups);
	}

	function getTypes($types) {
		$items = [];

		foreach($types as $type) {
			if (false === strpos($type, ' ')) {
				$items[] = rawurlencode($type);
			} else {
				$items[] = rawurlencode("'" . $type . "'");
			}
		}

		return implode(rawurlencode(', '), $items);
	}

	function liveSuppliersArcGIS($url, $pid) {
		$html = get_contents($url);

		$injection = getSiteInjection($html);
		$groups = getGroups($injection);
		$types = getTypes([
			'CSV Collection',
			'CSV',
			'Feature Collection',
			'Feature Layer',
			'Feature Service',
			'File Geodatabase',
			'GeoJSON',
			'GeoJson',
			'KML Collection',
			'KML',
			'Shapefile',
			'Stream Service',
			'Table',
			'Image Service']);

		if ($groups === null) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. The underlying system (ArcGIS) could not be detected',
			));

			return;
		}

		$uriDomain = $injection->site->domainInfo->domain;
//		$uriDomain = $injection->site->domainInfo->hostname;
//		$uriDomain = explode('/', $injection->site->item->url)[2];

		$searchAPI = 'https://hub.arcgis.com/api/search/v1';
		$allItems = $searchAPI . '/collections/all/items'; // all = datasets + documents + apps + maps
		$datasetItems = $searchAPI . '/collections/dataset/items';
		$aggregations = $searchAPI . '/collections/all/aggregations';

/*		$uri = $allItems . '?filter=((group%20IN%20(GROUPS)))&limit=0';
		$uri = str_replace('GROUPS', $groups, $uri);
		$json = json_decode(get_contents($uri));
		$number = $json->numberMatched;*/

/*		$uri = $datasetItems . '?filter=((group%20IN%20(GROUPS)))&limit=0';
		$uri = str_replace('GROUPS', $groups, $uri);
		$json = json_decode(get_contents($uri));
		$number = $json->numberMatched;*/

		$uri = $aggregations
			. '?aggregations=terms(fields%3D(source))'
			. '&filter=((type%20IN%20(TYPES)))%20AND%20((group%20IN%20(GROUPS)))';
		$uri = str_replace('GROUPS', $groups, $uri);
		$uri = str_replace('TYPES', $types, $uri);
		$json = json_decode(get_contents($uri));
		$result = $json->aggregations->aggregations[0]->aggregations;

/*		$uri = $aggregations
			. '?aggregations=terms(fields%3D(source))' // fields: type%2Csource%2Ctags
			. '&filter=((group%20IN%20(GROUPS)))';
		$uri = str_replace('GROUPS', $groups, $uri);
		$json = json_decode(get_contents($uri));*/

		$data = [];

		if ($result) {
			foreach($result as $source) {
				$id = preg_replace('#[^a-z0-9-]#i', '', $source->label);
				$name = $id;

				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $id,
					'name' => $name,
					'title' => $source->label,
					'created' => '',
					'packages' => $source->value,
					'uri' => '',
				));
			}
		}

		echo json_encode($data);
	}
?>