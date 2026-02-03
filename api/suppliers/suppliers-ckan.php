<?php
	function scrapeWebsite($groupID, $url, $groupWebsiteSuffix) {
		$uri = $url . $groupWebsiteSuffix . $groupID;
//		$source = file_get_contents($uri);
		$source = get_contents($uri);

		$html = $source;
		$start = stripos($html, 'breadcrumb');
		$end = stripos($html, '</ul>', $start);
		$length = $end - $start;
		$html = substr($html, $start, $length);
		$html = explode('<', $html);
		$html = $html[count($html) - 2];
		$title = explode('>', $html)[1];

		$html = $source;
		$start = stripos($html, 'view-header');
		$end = stripos($html, '</div>', $start);
		$length = $end - $start;
		$html = trim(substr($html, $start, $length));
		$html = explode(' ', $html);
		$packageCount = $html[count($html) - 2];

		return array(
			'id' => $groupID,
			'name' => $groupID,
			'title' => $title,
			'created' => '',
			'packages' => intval($packageCount),
			'uri' => ''
		);
	}

	function collectDataBySOLR($uriDomain, $url, $pid, &$data) {
		// e.g. avoindata.suomi.fi
		$searchSuffix = '/api/3/action/package_search';
		$searchParameter = '?facet.field=[%22organization%22]';

		if ('open.rlp.de' === $uriDomain) {
			$searchSuffix = '/api/action/package_search';
			$searchParameter = '?facet.field=[%22publisher_name%22]';
			$searchParameter .= '&fq=(isopen%3A%22true%22)';
		}

		$searchParameter .= '&facet.limit=-1';
		$searchParameter .= '&rows=0';
		$uri = $url . $searchSuffix . $searchParameter;
		$json = json_decode(get_contents($uri));

		$jsonData = $json;
		if ($jsonData->result) {
			$jsonData = $jsonData->result;
		}
		if ($jsonData->search_facets) {
			$jsonData = $jsonData->search_facets;
		}
		if ($jsonData->organization) {
			$jsonData = $jsonData->organization;
		} else if ($jsonData->publisher_name) {
			$jsonData = $jsonData->publisher_name;
		}
		if ($jsonData->items) {
			$jsonData = $jsonData->items;

			foreach($jsonData as $orga) {
				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $orga->name,
					'name' => $orga->name,
					'title' => $orga->display_name,
					'created' => '',
					'packages' => intval($orga->count),
					'uri' => ''
				));
			}
		}
	}

	function collectDataByDKAN($uriDomain, $url, $pid, &$data) {
		$groupListSuffix = '/api/3/action/group_list';
		$groupShowSuffix = '/api/3/action/group_show?id=';
		$groupPackageShowSuffix = '/api/3/action/group_package_show?id=';
		$groupWebsiteSuffix = '/group/';

		$uri = $url . $groupListSuffix;
//		$json = json_decode(file_get_contents($uri));
		$json = json_decode(get_contents($uri));

		if ($json) {
			$jsonData = $json;
			if (is_object($jsonData) && property_exists($jsonData, 'result')) {
				$jsonData = $jsonData->result;
			}

			foreach($jsonData as $groupID) {
				$uri = $url . $groupShowSuffix;
//				$json = json_decode(file_get_contents($uri . $groupID->name));
				$json = json_decode(get_contents($uri . $groupID->name));

				if ($json) {
					$uris = json_decode($json->result->extras[0]->value);
					$data[] = semanticContributor($uriDomain, $pid, array(
						'id' => $json->result->id,
						'name' => $json->result->name,
						'title' => $json->result->title,
						'created' => $json->result->created,
						'packages' => $json->result->package_count,
						'uri' => $uris[0]
					));
				} else {
/*					$uri = $url . $groupPackageShowSuffix;
					$json = json_decode(get_contents($uri . $groupID->name));

					if ($json) {
						$uris = json_decode($json->result->extras[0]->value);
						$data[] = semanticContributor($uriDomain, $pid, array(
							'id' => $json->result->id,
							'name' => $json->result->name,
							'title' => $json->result->title,
							'created' => $json->result->created,
							'packages' => $json->result->package_count,
							'uri' => $uris[0]
						));
					} else*/ {
						$data[] = semanticContributor($uriDomain, $pid, scrapeWebsite($groupID->name, $url, $groupWebsiteSuffix));
					}
				}
			}
		}
	}

	function suppliersCKAN($url, $pid) {
		$orgaListSuffix = '/api/3/action/organization_list';
		$orgaShowSuffix = '/api/3/action/organization_show?id=';

		$uri = $url . $orgaListSuffix;
//		$uriDomain = end(explode('/', $url));
		$uriDomain = explode('/', $url)[2];

		$data = [];

		collectDataBySOLR($uriDomain, $url, $pid, $data);
		if (count($data) > 0) {
			echo json_encode($data);
			return;
		}

		$json = json_decode(get_contents($uri));

		if ($json) {
			foreach($json->result as $orgaID) {
				$uri = $url . $orgaShowSuffix;
				$json = json_decode(get_contents($uri . $orgaID));

				if (is_null($json)) {
					// I'm to fast for CKAN APIs. The CKAN need a cool down phase ;)
					// If I wait for 1/10 second for every call, it will work fine
					// (but I want more speed)
					time_nanosleep(0, 1000000000 / 2); // 1/2 second
					$json = json_decode(get_contents($uri . $orgaID));
				}

				$uris = json_decode($json->result->extras[0]->value);
				$title = $json->result->title;
				$id = $json->result->id;
				$name = $json->result->name;

				if (is_object($title)) {
					if ($title->en && ($title->en !== '')) {
						$title = $title->en;
					} else {
						foreach(get_object_vars($title) as $val) {
							$title = $val ?: $title;
						}
					}
				}

				if (is_null($id)) {
					$id = $orgaID;
				}
				if (is_null($name)) {
					$name = $id;
				}
				if (is_null($title)) {
					$title = $orgaID;
				}

				// extras - key=gnd - value
				$data[] = semanticContributor($uriDomain, $pid, array(
					'id' => $id,
					'name' => $name,
					'title' => $title,
					'created' => $json->result->created,
					'packages' => $json->result->package_count,
					'uri' => (!is_null($uris) && is_array($uris)) ? $uris[0] : ''
				));
			}
		} else {
			collectDataByDKAN($uriDomain, $url, $pid, $data);
			if (count($data) > 0) {
				echo json_encode($data);
				return;
			}

			// page '403 Forbidden'

			// todo
		}

		echo json_encode($data);
	}
?>