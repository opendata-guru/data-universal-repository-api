<?php
	function suppliers($url, $pid) {
		$suffix = '/api/v1/search';

		$uri = $url . $suffix;
		$uriDomain = end(explode('/', $url));
		$json = json_decode(get_contents_30sec($uri));

		$data = [];

		if ($json) {
			foreach($json->suppliers as $supplier) {
				$title = $supplier->title;
				$name = preg_replace('#[^a-z0-9]#i', '', $title);
				$id = $name;

				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $id,
					'name' => $name,
					'title' => $title,
					'created' => '',
					'packages' => $supplier->count,
					'uri' => ''
				));
			}
		}

		echo json_encode($data);
	}
?>