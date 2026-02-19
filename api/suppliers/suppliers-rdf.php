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

	function suppliersRDF($url, $pid) {
		$rdf_xml = file_get_contents($url);
		$hydra = '';
		$totalItems = null;
		$itemsPerPage = null;

		$start = stripos($rdf_xml, '<hydra:PagedCollection');
		if (false !== $start) {
			$start = stripos($rdf_xml, '>', $start) + 1;
			$end = stripos($rdf_xml, '</hydra:PagedCollection>', $start);
			$length = $end - $start;
			$hydra = trim(substr($rdf_xml, $start, $length));
		}

		if ('' !== $hydra) {
			$start = stripos($rdf_xml, '<hydra:totalItems');
			if (false !== $start) {
				$start = stripos($rdf_xml, '>', $start) + 1;
				$end = stripos($rdf_xml, '</hydra:totalItems>', $start);
				$length = $end - $start;
				$totalItems = trim(substr($rdf_xml, $start, $length));
				$totalItems = intval($totalItems);
			}

			$start = stripos($rdf_xml, '<hydra:itemsperpage');
			if (false !== $start) {
				$start = stripos($rdf_xml, '>', $start) + 1;
				$end = stripos($rdf_xml, '</hydra:itemsperpage>', $start);
				$length = $end - $start;
				$itemsPerPage = trim(substr($rdf_xml, $start, $length));
				$itemsPerPage = intval($itemsPerPage);
			}
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

			$start = stripos($rdf_xml, '<hydra:PagedCollection');
			if (false !== $start) {
				$start = stripos($rdf_xml, '>', $start) + 1;
				$end = stripos($rdf_xml, '</hydra:PagedCollection>', $start);
				$length = $end - $start;
				$hydra = trim(substr($rdf_xml, $start, $length));
			}
		}

		$supplier = [];

//		do {
			parseDatasets($rdf_xml, $supplier);

			$next = '';
			if ('' !== $hydra) {
				$start = stripos($rdf_xml, '<hydra:nextPage');
				if (false !== $start) {
					$start = stripos($rdf_xml, '>', $start) + 1;
					$end = stripos($rdf_xml, '</hydra:nextPage>', $start);
					$length = $end - $start;
					$next = trim(substr($rdf_xml, $start, $length));
				}
			}

			if ('' !== $next) {
				$rdf_xml = file_get_contents($url);
			}
//		} while('' !== $next);

		if ($supplier) {
			foreach($supplier as $title => $count) {
				$id = preg_replace('#[^a-z0-9-]#i', '', $name);
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