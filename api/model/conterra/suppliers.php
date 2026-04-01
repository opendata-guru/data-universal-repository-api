<?php
	function suppliers($url, $pid) {
		$urlParts = parse_url($url);
    	$url = $urlParts['scheme'] . '://' . $urlParts['host'];

		$suffix = '/smartfinder-search/iso/select'
		. '?q=*'
		. '&wt=json'
		. '&rows=0'
		. '&start=0'
		. '&sort='
		. '&facet=true'
		. '&facet.field=publisher_facet'
		. '&request.preventCache=1775071961834';

		$uri = $url . $suffix;
		$uriDomain = end(explode('/', $url));
		$html = get_contents_30sec($uri);
		$json = json_decode($html);

		$data = [];

		if ($json) {
			$publisher_facet = $json->facet_counts->facet_fields->publisher_facet;
			for($p = 0; $p < count($publisher_facet); ++$p) {
				$title = $publisher_facet[$p];
				$name = preg_replace('#[^a-z0-9]#i', '', $title);
				$id = $name;

				++$p;
				$count = $publisher_facet[$p];

				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $id,
					'name' => $name,
					'title' => $title,
					'created' => '',
					'packages' => $count,
					'uri' => ''
				));
			}
		}

		echo json_encode($data);
	}
?>