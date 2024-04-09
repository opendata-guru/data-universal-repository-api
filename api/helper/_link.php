<?php
	function get_contents($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		$data = curl_exec($ch);

		curl_close($ch);

		return $data;
	}

	function unparse_url($parsedURL) {
		$scheme = isset($parsedURL['scheme']) ? $parsedURL['scheme'] . '://' : '';
		$host = isset($parsedURL['host']) ? $parsedURL['host'] : '';
		$port = isset($parsedURL['port']) ? ':' . $parsedURL['port'] : '';
		$user = isset($parsedURL['user']) ? $parsedURL['user'] : '';
		$pass = isset($parsedURL['pass']) ? ':' . $parsedURL['pass']  : '';
		$pass = ($user || $pass) ? $pass . '@' : '';
		$path = isset($parsedURL['path']) ? $parsedURL['path'] : '';
		$query = isset($parsedURL['query']) ? '?' . $parsedURL['query'] : '';
		$fragment = isset($parsedURL['fragment']) ? '#' . $parsedURL['fragment'] : '';

		return "$scheme$user$pass$host$port$path$query$fragment";
	}

	function getLink() {
		$parameterLink = htmlspecialchars($_GET['link']);
		$system = 'unknown';
		$error = null;
		$link = '';
		$url = '';

		if ($parameterLink == '') {
			$error = (object) array(
				'error' => 400,
				'header' => 'HTTP/1.0 400 Bad Request',
				'message' => 'Bad Request. Parameter \'link\' is not set',
			);
		}

		if (!$error && ($system === 'unknown')) {
			$link = parse_url($parameterLink);
			if ($link === false) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Parameter \'link\' contains a malformed URL',
				);
			}
		}

		if (!$error && ($system === 'unknown')) {
			$CKAN_CURRENT_PACKAGE_LIST = '/api/3/action/current_package_list_with_resources';
			$CKAN_ORGANIZATION_LIST = '/api/3/action/organization_list';
			$CKAN_PACKAGE_SEARCH = '/api/3/action/package_search';
			$CKAN_PACKAGE_SHOW = '/api/3/action/package_show';
			$CKAN_PACKAGE_LIST = '/api/3/action/package_list';
			$CKAN_STATUS_SHOW = '/api/3/action/status_show';
			$CKAN_GROUP_LIST = '/api/3/action/group_list';
			$CKAN_ACTION = '/api/3/action';
			$found = null;

			if ($CKAN_ORGANIZATION_LIST == substr($link['path'], -strlen($CKAN_ORGANIZATION_LIST))) {
				$found = substr($link['path'], 0, -strlen($CKAN_ORGANIZATION_LIST));
			} else if ($CKAN_GROUP_LIST == substr($link['path'], -strlen($CKAN_GROUP_LIST))) {
				$found = substr($link['path'], 0, -strlen($CKAN_GROUP_LIST));
			} else if ($CKAN_CURRENT_PACKAGE_LIST == substr($link['path'], -strlen($CKAN_CURRENT_PACKAGE_LIST))) {
				$found = substr($link['path'], 0, -strlen($CKAN_CURRENT_PACKAGE_LIST));
			} else if ($CKAN_PACKAGE_SEARCH == substr($link['path'], -strlen($CKAN_PACKAGE_SEARCH))) {
				$found = substr($link['path'], 0, -strlen($CKAN_PACKAGE_SEARCH));
			} else if ($CKAN_PACKAGE_SHOW == substr($link['path'], -strlen($CKAN_PACKAGE_SHOW))) {
				$found = substr($link['path'], 0, -strlen($CKAN_PACKAGE_SHOW));
			} else if ($CKAN_PACKAGE_LIST == substr($link['path'], -strlen($CKAN_PACKAGE_LIST))) {
				$found = substr($link['path'], 0, -strlen($CKAN_PACKAGE_LIST));
			} else if ($CKAN_STATUS_SHOW == substr($link['path'], -strlen($CKAN_STATUS_SHOW))) {
				$found = substr($link['path'], 0, -strlen($CKAN_STATUS_SHOW));
			} else if ($CKAN_ACTION == substr($link['path'], -strlen($CKAN_ACTION))) {
				$found = substr($link['path'], 0, -strlen($CKAN_ACTION));
			} else if (!$link['path'] && ('ckan' === explode('.', $link['host'])[0])) {
				$found = '';
			}

			if ($found !== null) {
				$link['path'] = $found;
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'CKAN';
			}
		}

		if (!$error && ($system === 'unknown')) {
			$PIVEAU_SEARCH_CATALOGUES_ = '/api/hub/search/catalogues/';
			$PIVEAU_SEARCH_CATALOGUES = '/api/hub/search/catalogues';
			$PIVEAU_SEARCH_DATASETS = '/api/hub/search/datasets';
			$PIVEAU_SEARCH_SCROLL = '/api/hub/search/scroll';
			$PIVEAU_SEARCH_SEARCH = '/api/hub/search/search';
			$PIVEAU_SEARCH_ = '/api/hub/search/';
			$PIVEAU_SEARCH = '/api/hub/search';
			$found = null;

			if ($PIVEAU_SEARCH_CATALOGUES_ == substr($link['path'], -strlen($PIVEAU_SEARCH_CATALOGUES_))) {
				$found = substr($link['path'], 0, -strlen($PIVEAU_SEARCH_CATALOGUES_));
			} else if ($PIVEAU_SEARCH_CATALOGUES == substr($link['path'], -strlen($PIVEAU_SEARCH_CATALOGUES))) {
				$found = substr($link['path'], 0, -strlen($PIVEAU_SEARCH_CATALOGUES));
			} else if ($PIVEAU_SEARCH_DATASETS == substr($link['path'], -strlen($PIVEAU_SEARCH_DATASETS))) {
				$found = substr($link['path'], 0, -strlen($PIVEAU_SEARCH_DATASETS));
			} else if ($PIVEAU_SEARCH_SCROLL == substr($link['path'], -strlen($PIVEAU_SEARCH_SCROLL))) {
				$found = substr($link['path'], 0, -strlen($PIVEAU_SEARCH_SCROLL));
			} else if ($PIVEAU_SEARCH_SEARCH == substr($link['path'], -strlen($PIVEAU_SEARCH_SEARCH))) {
				$found = substr($link['path'], 0, -strlen($PIVEAU_SEARCH_SEARCH));
			} else if ($PIVEAU_SEARCH_ == substr($link['path'], -strlen($PIVEAU_SEARCH_))) {
				$found = substr($link['path'], 0, -strlen($PIVEAU_SEARCH_));
			} else if ($PIVEAU_SEARCH == substr($link['path'], -strlen($PIVEAU_SEARCH))) {
				$found = substr($link['path'], 0, -strlen($PIVEAU_SEARCH));
			}

			if ($found !== null) {
				$link['path'] = $found;
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'Piveau';
			}
		}

		if (!$error && ($system === 'unknown')) {
			$host = explode('.', $link['host']);
			$found = (count($host) >= 2) && ('arcgis.com' === ($host[count($host) - 2] . '.' . $host[count($host) - 1]));

			if ($found) {
				unset($link['path']);
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'ArcGIS';
			}
		}

		if (!$error && ($system === 'unknown')) {
			$ENTRYSTORE_MANAGEMENT_STATUS = '/store/management/status';
			$ENTRYSTORE_SEARCH = '/store/search';
			$ENTRYSTORE_STORE_ = '/store/';
			$ENTRYSTORE_STORE = '/store';
			$found = null;

			if ($ENTRYSTORE_MANAGEMENT_STATUS == substr($link['path'], -strlen($ENTRYSTORE_MANAGEMENT_STATUS))) {
				$found = substr($link['path'], 0, -strlen($ENTRYSTORE_MANAGEMENT_STATUS));
			} else if ($ENTRYSTORE_SEARCH == substr($link['path'], -strlen($ENTRYSTORE_SEARCH))) {
				$found = substr($link['path'], 0, -strlen($ENTRYSTORE_SEARCH));
			} else if ($ENTRYSTORE_STORE_ == substr($link['path'], -strlen($ENTRYSTORE_STORE_))) {
				$found = substr($link['path'], 0, -strlen($ENTRYSTORE_STORE_));
			} else if ($ENTRYSTORE_STORE == substr($link['path'], -strlen($ENTRYSTORE_STORE))) {
				$found = substr($link['path'], 0, -strlen($ENTRYSTORE_STORE));
			}

			if ($found !== null) {
				$link['path'] = $found;
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'EntryStore';
			}
		}

		if (!$error && ($system === 'unknown')) {
			$ODS_20_CATALOG_METADATA_TEMPLATES = '/api/explore/v2.0/catalog/metadata_templates';
			$ODS_21_CATALOG_METADATA_TEMPLATES = '/api/explore/v2.1/catalog/metadata_templates';
			$ODS_20_CATALOG_DATASETS = '/api/explore/v2.0/catalog/datasets';
			$ODS_21_CATALOG_DATASETS = '/api/explore/v2.1/catalog/datasets';
			$ODS_20_CATALOG_EXPORTS = '/api/explore/v2.0/catalog/exports';
			$ODS_21_CATALOG_EXPORTS = '/api/explore/v2.1/catalog/exports';
			$ODS_20_CATALOG_FACETS = '/api/explore/v2.0/catalog/facets';
			$ODS_21_CATALOG_FACETS = '/api/explore/v2.1/catalog/facets';
			$ODS_2_CATALOG_FACETS = '/api/v2/catalog/facets';
			$ODS_20_MONITORING = '/api/explore/v2.0/monitoring';
			$ODS_21_MONITORING = '/api/explore/v2.1/monitoring';
			$ODS_20_CATALOG = '/api/explore/v2.0/catalog';
			$ODS_21_CATALOG = '/api/explore/v2.1/catalog';
			$ODS_20_ODS = '/api/explore/v2.0/opendatasoft';
			$ODS_21_ODS = '/api/explore/v2.1/opendatasoft';
			$ODS_20_ = '/api/explore/v2.0/';
			$ODS_21_ = '/api/explore/v2.1/';
			$ODS_20 = '/api/explore/v2.0';
			$ODS_21 = '/api/explore/v2.1';
			$found = null;

			if ($ODS_20_CATALOG_METADATA_TEMPLATES == substr($link['path'], -strlen($ODS_20_CATALOG_METADATA_TEMPLATES))) {
				$found = substr($link['path'], 0, -strlen($ODS_20_CATALOG_METADATA_TEMPLATES));
			} else if ($ODS_21_CATALOG_METADATA_TEMPLATES == substr($link['path'], -strlen($ODS_21_CATALOG_METADATA_TEMPLATES))) {
				$found = substr($link['path'], 0, -strlen($ODS_21_CATALOG_METADATA_TEMPLATES));
			} else if ($ODS_20_CATALOG_DATASETS == substr($link['path'], -strlen($ODS_20_CATALOG_DATASETS))) {
				$found = substr($link['path'], 0, -strlen($ODS_20_CATALOG_DATASETS));
			} else if ($ODS_21_CATALOG_DATASETS == substr($link['path'], -strlen($ODS_21_CATALOG_DATASETS))) {
				$found = substr($link['path'], 0, -strlen($ODS_21_CATALOG_DATASETS));
			} else if ($ODS_20_CATALOG_EXPORTS == substr($link['path'], -strlen($ODS_20_CATALOG_EXPORTS))) {
				$found = substr($link['path'], 0, -strlen($ODS_20_CATALOG_EXPORTS));
			} else if ($ODS_21_CATALOG_EXPORTS == substr($link['path'], -strlen($ODS_21_CATALOG_EXPORTS))) {
				$found = substr($link['path'], 0, -strlen($ODS_21_CATALOG_EXPORTS));
			} else if ($ODS_20_CATALOG_FACETS == substr($link['path'], -strlen($ODS_20_CATALOG_FACETS))) {
				$found = substr($link['path'], 0, -strlen($ODS_20_CATALOG_FACETS));
			} else if ($ODS_21_CATALOG_FACETS == substr($link['path'], -strlen($ODS_21_CATALOG_FACETS))) {
				$found = substr($link['path'], 0, -strlen($ODS_21_CATALOG_FACETS));
			} else if ($ODS_2_CATALOG_FACETS == substr($link['path'], -strlen($ODS_2_CATALOG_FACETS))) {
				$found = substr($link['path'], 0, -strlen($ODS_2_CATALOG_FACETS));
			} else if ($ODS_20_MONITORING == substr($link['path'], -strlen($ODS_20_MONITORING))) {
				$found = substr($link['path'], 0, -strlen($ODS_20_MONITORING));
			} else if ($ODS_21_MONITORING == substr($link['path'], -strlen($ODS_21_MONITORING))) {
				$found = substr($link['path'], 0, -strlen($ODS_21_MONITORING));
			} else if ($ODS_20_CATALOG == substr($link['path'], -strlen($ODS_20_CATALOG))) {
				$found = substr($link['path'], 0, -strlen($ODS_20_CATALOG));
			} else if ($ODS_21_CATALOG == substr($link['path'], -strlen($ODS_21_CATALOG))) {
				$found = substr($link['path'], 0, -strlen($ODS_21_CATALOG));
			} else if ($ODS_20_ODS == substr($link['path'], -strlen($ODS_20_ODS))) {
				$found = substr($link['path'], 0, -strlen($ODS_20_ODS));
			} else if ($ODS_21_ODS == substr($link['path'], -strlen($ODS_21_ODS))) {
				$found = substr($link['path'], 0, -strlen($ODS_21_ODS));
			} else if ($ODS_20_ == substr($link['path'], -strlen($ODS_20_))) {
				$found = substr($link['path'], 0, -strlen($ODS_20_));
			} else if ($ODS_21_ == substr($link['path'], -strlen($ODS_21_))) {
				$found = substr($link['path'], 0, -strlen($ODS_21_));
			} else if ($ODS_20 == substr($link['path'], -strlen($ODS_20))) {
				$found = substr($link['path'], 0, -strlen($ODS_20));
			} else if ($ODS_21 == substr($link['path'], -strlen($ODS_21))) {
				$found = substr($link['path'], 0, -strlen($ODS_21));
			}

			if ($found !== null) {
				$link['path'] = $found;
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'Opendatasoft';
			}
		}

		if (!$error && ($system === 'unknown')) {
			$found = 'datenadler.de' === $link['host'];

			if ($found) {
				unset($link['path']);
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'datenadler';
			}
		}

		if (!$error && ($system === 'unknown')) {
			$host = explode('.', $link['host']);
			$found = (count($host) >= 2) && ('mcloud.de' === ($host[count($host) - 2] . '.' . $host[count($host) - 1]));

			if ($found) {
				unset($link['path']);
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'mcloud';
			}
		}

		if (!$error && ($system === 'unknown')) {
			$host = explode('.', $link['host']);
			$found = (count($host) >= 2) && ('geoportal.de' === ($host[count($host) - 2] . '.' . $host[count($host) - 1]));

			if ($found) {
				unset($link['path']);
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'geoportal.de';
			}
		}

		if (!$error && ($system === 'unknown')) {
			$url = $link;
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterLink,
			'system' => $system,
			'url' => $url,
		);
	}
?>