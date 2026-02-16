<?php
	function systemRDF($url) {
		$content = get_contents($url);

		$start = stripos($content, '<rdf:rdf') + 8;
		$end = stripos($content, '>', $start);
		$length = $end - $start;
		$content = substr($content, $start, $length);

		$ns = explode('xmlns:', $content);
		$extensions = [];

		foreach($ns as $item) {
			$name = explode('=', $item);

			if (count($name) > 1) {
				$extensions[] = 'xmlns:' . $name[0];
			}
		}

		echo json_encode((object) array(
			'extensions' => $extensions,
			'system' => 'rdf',
			'url' => $url,
			'version' => null,
		));
	}
?>