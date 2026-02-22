<?php
	function parseDatasets($rdf_xml, &$supplier) {
		$start = 0;

		while(true) {
			$start = strpos($rdf_xml, '<dcat:Dataset', $start);
			if (false === $start) {
				return;
			}

			$start = stripos($rdf_xml, '>', $start) + 1;
			$end = strpos($rdf_xml, '</dcat:Dataset>', $start);
			$length = $end - $start;
			$dataset = trim(substr($rdf_xml, $start, $length));

			$publisher = '';
			$publisherName = '';

			$s = stripos($dataset, ':publisher');
			if (false !== $s) {
				$s = stripos($dataset, '>', $s) + 1;
				$e = stripos($dataset, ':publisher>', $s);
				$l = $e - $s;
				$publisher = trim(substr($dataset, $s, $l));
				$publisher = trim(substr($publisher, 0, strripos($publisher, '<')));
			}

			if ('' !== $publisher) {
				// is a foaf:Organization or foaf:Agent
				$s = stripos($publisher, ':name');
				if (false !== $s) {
					$s = stripos($publisher, '>', $s) + 1;
					$e = stripos($publisher, ':name>', $s);
					$l = $e - $s;
					$publisherName = trim(substr($publisher, $s, $l));
					$publisherName = trim(substr($publisherName, 0, strripos($publisherName, '<')));
				}
			}

			if ('' !== $publisherName) {
				if (isset($supplier[$publisherName])) {
					++$supplier[$publisherName];
				} else {
					$supplier[$publisherName] = 1;
				}
			}

			$start = $end;
		}
	}

	function getHydra($rdf_xml) {
		$start = stripos($rdf_xml, '<hydra:PagedCollection');

		if (false !== $start) {
			$start = stripos($rdf_xml, '>', $start) + 1;
			$end = stripos($rdf_xml, '</hydra:PagedCollection>', $start);
			$length = $end - $start;
			return trim(substr($rdf_xml, $start, $length));
		}

		return '';
	}

	function getHydraTotalItems($hydra) {
		if ('' !== $hydra) {
			$start = stripos($hydra, '<hydra:totalItems');

			if (false !== $start) {
				$start = stripos($hydra, '>', $start) + 1;
				$end = stripos($hydra, '</hydra:totalItems>', $start);
				$length = $end - $start;

				$totalItems = trim(substr($hydra, $start, $length));
				return intval($totalItems);
			}
		}

		return null;
	}

	function getHydraItemsPerPage($hydra) {
		if ('' !== $hydra) {
			$start = stripos($hydra, '<hydra:itemsperpage');

			if (false !== $start) {
				$start = stripos($hydra, '>', $start) + 1;
				$end = stripos($hydra, '</hydra:itemsperpage>', $start);
				$length = $end - $start;

				$itemsPerPage = trim(substr($hydra, $start, $length));
				return intval($itemsPerPage);
			}
		}

		return null;
	}

	function getHydraNextPage($hydra) {
		if ('' !== $hydra) {
			$start = stripos($hydra, '<hydra:nextPage');

			if (false !== $start) {
				$start = stripos($hydra, '>', $start) + 1;
				$end = stripos($hydra, '</hydra:nextPage>', $start);
				$length = $end - $start;

				$next = trim(substr($hydra, $start, $length));
				return str_replace('&amp;', '&', $next);
			}
		}

		return '';
	}

	function suppliersRDF($url, $pid) {
		$hydra = '';
		$totalItems = null;
		$itemsPerPage = null;

		$uriDomain = explode('/', $url)[2];

		$rdf_xml = file_get_contents($url);
		$hydra = getHydra($rdf_xml);

		if ('' !== $hydra) {
			$totalItems = getHydraTotalItems($hydra);
			$itemsPerPage = getHydraItemsPerPage($hydra);
		} else {
			$start = stripos($rdf_xml, '<dcat:catalog');
			if (false !== $start) {
				$start = stripos($rdf_xml, '>', $start) + 1;
				$end = stripos($rdf_xml, '</dcat:catalog>', $start);
				$length = $end - $start;
				$catalog = trim(substr($rdf_xml, $start, $length));

				$totalItems = substr_count($catalog, '<dcat:dataset');
			}
		}

		if ((null !== $itemsPerPage) && ($itemsPerPage < $totalItems)) {
			// itemsPerPage | limit | h
			$limit = min($totalItems, 1000);

			$query = parse_url($url, PHP_URL_QUERY);
			if ($query) {
				$url .= '&h=' . $limit;
			} else {
				$url .= '?h=' . $limit;
			}

			$rdf_xml = file_get_contents($url);
			$hydra = getHydra($rdf_xml);
		}

		$supplier = [];
		$continue = true;
		$lastNext = $url;

		do {
			parseDatasets($rdf_xml, $supplier);

			$next = getHydraNextPage($hydra);

			if ('' !== $next) {
				$rdf_xml = file_get_contents($next);
				$hydra = getHydra($rdf_xml);

				$continue = $lastNext !== $next;
				$lastNext = $next;
			} else {
				$continue = false;
			}
		} while($continue);

		if ($supplier) {
			foreach($supplier as $title => $count) {
				$id = preg_replace('#[^a-z0-9-]#i', '', $title);
				$name = $id;

				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $id,
					'name' => $name,
					'title' => $title,
					'created' => '',
					'packages' => $count,
					'uri' => '',
				));
			}
		}

		echo json_encode($data);
	}
?>