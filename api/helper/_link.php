<?php
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
			$CKAN_ORGANIZATION_LIST = '/api/3/action/organization_list';
			$CKAN_GROUP_LIST = '/api/3/action/group_list';
			$CKAN_STATUS_SHOW = '/api/3/action/status_show';
			$CKAN_ACTION = '/api/3/action';

			if ($CKAN_ORGANIZATION_LIST == substr($link['path'], -strlen($CKAN_ORGANIZATION_LIST))) {
				$link['path'] = substr($link['path'], 0, -strlen($CKAN_ORGANIZATION_LIST));
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'CKAN';
			} else if ($CKAN_GROUP_LIST == substr($link['path'], -strlen($CKAN_GROUP_LIST))) {
				$link['path'] = substr($link['path'], 0, -strlen($CKAN_GROUP_LIST));
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'CKAN';
			} else if ($CKAN_STATUS_SHOW == substr($link['path'], -strlen($CKAN_STATUS_SHOW))) {
				$link['path'] = substr($link['path'], 0, -strlen($CKAN_STATUS_SHOW));
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'CKAN';
			} else if ($CKAN_ACTION == substr($link['path'], -strlen($CKAN_ACTION))) {
				$link['path'] = substr($link['path'], 0, -strlen($CKAN_ACTION));
				unset($link['query']);
				unset($link['fragment']);
				$url = unparse_url($link);
				$system = 'CKAN';
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