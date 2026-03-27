<?php
	// COMMENT THIS LINES
//	ini_set('display_errors', 1);
//	ini_set('display_startup_errors', 1);
//	error_reporting(E_ALL);

	function countCataloges($url, $path_prefix) {
		$catalogSuffix = $path_prefix . '/catalogues';

		$uri = $url . $catalogSuffix;
		$source = get_contents($uri);

		$list = json_decode($source);
		if ($list) {
			return count($list);
		}

		return 0;
	}

	function getCataloges($url, $path_prefix, $uriDomain, $pid) {
		$catalogSuffix = $path_prefix . '/catalogues';
		$catalogsSuffix = $path_prefix . '/catalogues/';
		$countSuffix = $path_prefix . '/search?q=&filter=dataset&facets={%22catalog%22:[%22###%22]}&limit=0';

		$uri = $url . $catalogSuffix;
		$source = get_contents($uri);

		$data = [];

		$list = json_decode($source);
		if ($list) {
			for ($l = 0; $l < count($list); ++$l) {
				$catalogURI = $url . $catalogsSuffix . $list[$l];
				$source = get_contents($catalogURI);
				$catalog = json_decode($source);

				// $catalog->result->country->label

				$count = $catalog->result->count;
				if (!$count || ($count < 1)) {
					$countURI = $url . $countSuffix;
					$countURI = str_replace('###', $catalog->result->id, $countURI);
					$source = get_contents($countURI);
					$countData = json_decode($source);
					$count = $countData->result->count;
				}

				$title = $catalog->result->title;
				if ($title) {
					$titleLang = array_keys((array)$title)[0];
					$title = ((array)$title)[$titleLang];
				} else if ($catalog->result->publisher) {
					$title = $catalog->result->publisher->name;
				}

				$id = $catalog->result->id;

				// used in Bavaria and Europe
				$isPartOf = $catalog->result->is_part_of;
				$hasPart = $catalog->result->has_part;

				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $id,
					'name' => $id,
					'title' => $title,
					'created' => '',
					'packages' => $count,
					'uri' => '',
					'ispartof' => $isPartOf,
					'haspart' => $hasPart,
				));
			}
		}

		return $data;
	}

	function getCatalogesByFacet($url, $path_prefix, $uriDomain, $pid) {
		// %22is_hvd%22:[]
		// %22hvd_category%22:[]
		// %22categories%22:[]
		// %22publisher%22:[]
		// %22format%22:[]
		// %22license%22:[]
		// %22catalog%22:[] -> %22catalog%22:[%22name%22]
		// %22superCatalog%22:[]
		$facetSuffix = $path_prefix . '/search?q=&filter=dataset&facets={%22publisher%22:[],%22catalog%22:[],%22superCatalog%22:[]}&limit=0';

		$uri = $url . $facetSuffix;
		$source = get_contents($uri);

		$data = [];

		$result = json_decode($source);
		if ($result) {
			$facets = $result->result->facets;
			if ($facets) {
				foreach ($facets as $facet) {
					if ('catalog' === $facet->id) {
						foreach ($facet->items as $item) {
							$id = $item->id;
							$title = $item->title;
							$count = $item->count;

							// used in Bavaria and Europe
							$isPartOf = ''; // ???
							$hasPart = ''; // ???

							$data[] = semanticContributor($uriDomain, $pid, array(
								'id' => $id,
								'name' => $id,
								'title' => $title,
								'created' => '',
								'packages' => $count,
								'uri' => '',
								'ispartof' => $isPartOf,
								'haspart' => $hasPart,
							));
						}
					}
				}
			}
		}

		return $data;
	}

	function getPublisherByFacet($url, $path_prefix, $uriDomain, $pid) {
		// %22is_hvd%22:[]
		// %22hvd_category%22:[]
		// %22categories%22:[]
		// %22publisher%22:[]
		// %22format%22:[]
		// %22license%22:[]
		// %22catalog%22:[] -> %22catalog%22:[%22name%22]
		// %22superCatalog%22:[]
		$facetSuffix = $path_prefix . '/search?q=&filter=dataset&facets={%22publisher%22:[],%22catalog%22:[],%22superCatalog%22:[]}&limit=0';

		$uri = $url . $facetSuffix;
		$source = get_contents($uri);

		$data = [];

		$result = json_decode($source);
		if ($result) {
			$facets = $result->result->facets;
			if ($facets) {
				foreach ($facets as $facet) {
					if ('publisher' === $facet->id) {
						foreach ($facet->items as $item) {
							$id = $item->id;
							$title = $item->title;
							$count = $item->count;

							// used in Bavaria and Europe
							$isPartOf = ''; // ???
							$hasPart = ''; // ???

$data[] = $title;
/*							$data[] = semanticContributor($uriDomain, $pid, array(
								'id' => $id,
								'name' => $id,
								'title' => $title,
								'created' => '',
								'packages' => $count,
								'uri' => '',
								'ispartof' => $isPartOf,
								'haspart' => $hasPart,
							));*/
						}
					}
				}
			}
		}

		return $data;
	}

	function liveSuppliersPiveau($url, $pid) {
		$PIVEAU_HOST_SEARCH_PIVEAU = 'search.piveau.';
		$PIVEAU_HOST_UI_PIVEAU = 'ui.piveau.';

		$path_prefix = '/api/hub/search';
		$host = '';

		$link = parse_url($url);
		if (str_starts_with($link['host'], $PIVEAU_HOST_SEARCH_PIVEAU)) {
    		$host = substr($link['host'], strlen($PIVEAU_HOST_SEARCH_PIVEAU));
		} else if (str_starts_with($link['host'], $PIVEAU_HOST_UI_PIVEAU)) {
    		$host = substr($link['host'], strlen($PIVEAU_HOST_UI_PIVEAU));
		}

		if ($host) {
			$link['host'] = $PIVEAU_HOST_SEARCH_PIVEAU . $host;
			$url = unparse_url($link);
			$path_prefix = '';
		}

		$uriDomain = end(explode('/', $url));

		$data = [];

		//$data = getPublisherByFacet($url, $path_prefix, $uriDomain, $pid);

		$count = countCataloges($url, $path_prefix);
		if ($count < 250) {
			// find all cataloges (also cataloges without datasets)
			// make a single call for every catalog -> timeout for too many cataloges
			$data = getCataloges($url, $path_prefix, $uriDomain, $pid);
		} else {
			// find only cataloges with data
			// did not find 'isPartOf' and 'hasPart' properties
			$data = getCatalogesByFacet($url, $path_prefix, $uriDomain, $pid);
		}

		for ($d = 0; $d < count($data); ++$d) {
			if ($data[$d]['ispartof']) {
				$parentLObject = findLObject($data[$d]['lobject']['pid'], $data[$d]['ispartof']);
				if ($parentLObject) {
					$parentIndex = array_search($parentLObject['lid'], array_column($data, 'lid'));

					if (!$data[$d]['lobject']['ispartof']) {
						$data[$d]['lobject']['ispartof'] = array();
					}
					$data[$d]['lobject']['ispartof'][] = $parentLObject['lid'];
					$data[$d]['lobject']['ispartof'] = array_unique($data[$d]['lobject']['ispartof']);
					$data[$d]['lobject']['ispartof'] = array_filter($data[$d]['lobject']['ispartof']);

					updateLObject($data[$d]['lobject']);

					if ($parentIndex !== false) {
						if (!$data[$parentIndex]['lobject']['haspart']) {
							$data[$parentIndex]['lobject']['haspart'] = array();
						}
						$data[$parentIndex]['lobject']['haspart'][] = $data[$d]['lobject']['lid'];
						$data[$parentIndex]['lobject']['haspart'] = array_unique($data[$parentIndex]['lobject']['haspart']);
						$data[$parentIndex]['lobject']['haspart'] = array_filter($data[$parentIndex]['lobject']['haspart']);

						updateLObject($data[$parentIndex]['lobject']);
					}
				}
			}

			if ($data[$d]['haspart']) {
				for ($h = 0; $h < count($data[$d]['haspart']); ++$h) {
					if ($data[$d]['id'] === $data[$d]['haspart'][$h]) {
						// error - recursion
					} else {
						$childLObject = findLObject($data[$d]['lobject']['pid'], $data[$d]['haspart'][$h]);
						if ($childLObject) {
							$childIndex = array_search($childLObject['lid'], array_column($data, 'lid'));

							if (!$data[$d]['lobject']['haspart']) {
								$data[$d]['lobject']['haspart'] = array();
							}
							if (!is_array($data[$d]['lobject']['haspart'])) {
								$data[$d]['lobject']['haspart'] = array();
							}

							$data[$d]['lobject']['haspart'][] = $childLObject['lid'];
							$data[$d]['lobject']['haspart'] = array_unique($data[$d]['lobject']['haspart']);
							$data[$d]['lobject']['haspart'] = array_filter($data[$d]['lobject']['haspart']);

							updateLObject($data[$d]['lobject']);

							if ($childIndex !== false) {
								if (!$data[$childIndex]['lobject']['ispartof']) {
									$data[$childIndex]['lobject']['ispartof'] = array();
								}
								$data[$childIndex]['lobject']['ispartof'][] = $data[$d]['lobject']['lid'];
								$data[$childIndex]['lobject']['ispartof'] = array_unique($data[$childIndex]['lobject']['ispartof']);
								$data[$childIndex]['lobject']['ispartof'] = array_filter($data[$childIndex]['lobject']['ispartof']);

								updateLObject($data[$childIndex]['lobject']);
							}
						}
					}
				}
			}
		}

		for ($d = 0; $d < count($data); ++$d) {
			if ($data[$d]['ispartof'] === null) {
				unset($data[$d]['ispartof']);
			}

			if ($data[$d]['haspart']) {
				if (!$data[$d]['parts']) {
					$data[$d]['parts'] = array();
				}

				$reset = false;

				for ($h = 0; !$reset && ($h < count($data[$d]['haspart'])); ++$h) {
					if ($data[$d]['id'] === $data[$d]['haspart'][$h]) {
//						$data[$d]['parts'][] = '!!! error - recursion';
					} else {
						for ($d2 = 0; !$reset && ($d2 < count($data)); ++$d2) {
							if ($data[$d2]['id'] === $data[$d]['haspart'][$h]) {
								if ($data[$d2]['haspart'] === null) {
									unset($data[$d2]['haspart']);
								}
								unset($data[$d2]['ispartof']);
								$data[$d]['packages'] += $data[$d2]['packages'];

								array_splice($data[$d]['haspart'], $h, 1);
								$data[$d]['parts'][] = $data[$d2];

								array_splice($data, $d2, 1);
								$reset = true;
								$d2 = -1;
							}
						}
					}
				}

				if ($reset) {
					$d = -1;
				} else if (0 === count($data[$d]['haspart'])) {
					$data[$d]['haspart'] = null;
				}
			} else if ($data[$d]['haspart'] === null) {
				unset($data[$d]['haspart']);
			}
		}

		echo json_encode($data);
	}
?>