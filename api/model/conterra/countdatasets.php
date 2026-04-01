<?php
	function countDatasets($url) {
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
		$html = get_contents_30sec($uri);
		$json = json_decode($html);

		echo json_encode((object) array(
			'number' => intval($json->response->numFound),
		));
	}
?>