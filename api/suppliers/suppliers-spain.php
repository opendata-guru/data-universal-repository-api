<?php
	function suppliersSpain($url) {
		$catalogSuffix = '/en/catalogo?q=&_publisher_display_name_limit=0';
		$uriDomain = explode('/', $url)[2];

		$data = [];

		$uri = $url . $catalogSuffix;
		$source = file_get_contents($uri);

		$html = $source;
		$sections = explode('<section ', $html);

		foreach ($sections as $section) {
			$header = explode('<h2', $section)[1];
			$header = explode('</h2>', $header)[0];
			$header = str_replace('<i class="icon-medium icon-filter"></i>', '', $header);
			$header = explode('>', $header)[1];
			$header = trim($header);

			if ('Publisher' == $header) {
				$items = explode('<li ', $section);

				foreach ($items as $item) {
					$span = explode('<span>', $item);
					if (count($span) > 1) {
						$span = explode('</span>', $span[1])[0];
						$span = explode(' (', $span);

						$title = $span[0];
						$count = explode(')', $span[1])[0];
						$name = preg_replace('#[^a-z0-9]#i', '', $title);

						$data[] = semanticContributor($uriDomain, array(
							'id' => $name,
							'name' => $name,
							'title' => $title,
							'created' => '',
							'packages' => intval($count),
							'uri' => ''
						));
					}
				}
			}
		}

		echo json_encode($data);
	}
?>