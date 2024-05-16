<?php
	function suppliersPiveau($url, $pid) {
		$catalogSuffix = '/api/hub/search/catalogues';
		$catalogsSuffix = '/api/hub/search/catalogues/';
		$countSuffix = '/api/hub/search/search?q=&filter=dataset&facets={%22catalog%22:[%22###%22]}&limit=0';

		$uri = $url . $catalogSuffix;
		$uriDomain = end(explode('/', $url));

		$source = file_get_contents($uri);

		$data = [];

		$list = json_decode($source);

		for ($l = 0; $l < count($list); ++$l) {
			$catalogURI = $url . $catalogsSuffix . $list[$l];
			$source = file_get_contents($catalogURI);
			$catalog = json_decode($source);

			$countURI = $url . $countSuffix;
			$countURI = str_replace('###', $catalog->result->id, $countURI);
			$source = file_get_contents($countURI);
			$countData = json_decode($source);

			$title = $catalog->result->title;
			$titleLang = array_keys((array)$title)[0];
			$title = ((array)$title)[$titleLang];

			$id = $catalog->result->id;
//			$count = $catalog->result->count;
			$count = $countData->result->count;

			// new in Bavaria
			$is_part_of = $catalog->result->is_part_of;
			$has_part = $catalog->result->has_part;

			$data[] = semanticContributor($uriDomain, $pid, array(
				'id' => $id,
				'name' => $id,
				'title' => $title,
				'created' => '',
				'packages' => $count,
				'uri' => '',
				'is_part_of' => $is_part_of,
				'has_part' => $has_part,
			));
		}

		for ($d = 0; $d < count($data); ++$d) {
			if ($data[$d]['is_part_of'] === null) {
				unset($data[$d]['is_part_of']);
			}
			if ($data[$d]['has_part']) {
				if (!$data[$d]['parts']) {
					$data[$d]['parts'] = array();
				}
				$reset = false;

				for ($h = 0; !$reset && ($h < count($data[$d]['has_part'])); ++$h) {
					if ($data[$d]['id'] === $data[$d]['has_part'][$h]) {
//						$data[$d]['parts'][] = '!!! error - recursion';
					} else {
						for ($d2 = 0; !$reset && ($d2 < count($data)); ++$d2) {
							if ($data[$d2]['id'] === $data[$d]['has_part'][$h]) {
								if ($data[$d2]['has_part'] === null) {
									unset($data[$d2]['has_part']);
								}
								unset($data[$d2]['is_part_of']);
								$data[$d]['packages'] += $data[$d2]['packages'];

								array_splice($data[$d]['has_part'], $h, 1);
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
				} else if (0 === count($data[$d]['has_part'])) {
					$data[$d]['has_part'] = null;
				}
			} else if ($data[$d]['has_part'] === null) {
				unset($data[$d]['has_part']);
			}
		}

		echo json_encode($data);
	}
?>