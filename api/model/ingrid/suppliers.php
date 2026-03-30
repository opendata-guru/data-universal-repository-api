<?php
	function suppliers($url, $pid) {
		$suffix = '/freitextsuche';

		$uri = $url . $suffix;
		$uriDomain = end(explode('/', $url));
		$html = get_contents_30sec($uri);

		$start = stripos($html, 'provider-accordion');
		if (false === $start) {
			$start = stripos($html, 'maintain-accordion');
		}
		$start = stripos($html, '>', $start) + 1;
		$end = stripos($html, '</li>', $start);
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));

		$boxes = explode('"input-check"', $html);

		$data = [];

		if (count($boxes) > 1) {
			for($b = 1; $b < count($boxes); ++$b) {
				$box = $boxes[$b];

				$start = stripos($box, '"text"');
				$start = stripos($box, '>', $start) + 1;
				$end = stripos($box, '</span>', $start);
				$length = $end - $start;
				$title = trim(substr($box, $start, $length));

				$start = stripos($box, '"nr-results"');
				$start = stripos($box, '>', $start) + 1;
				$end = stripos($box, '</span>', $start);
				$length = $end - $start;
				$count = intval(trim(substr($box, $start, $length), ' ()'));

				$name = preg_replace('#[^a-z0-9]#i', '', $title);
				$id = $name;

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