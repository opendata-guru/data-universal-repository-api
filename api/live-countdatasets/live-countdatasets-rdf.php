<?php
	function countDatasetsRDF($url) {
		$rdf_xml = file_get_contents($url);
		$hydra = '';
		$totalItems = null;

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

		if ($totalItems !== null) {
			echo json_encode((object) array(
				'number' => $totalItems,
			));
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. The underlying system (rdf) could not be detected',
			));
		}
	}
?>