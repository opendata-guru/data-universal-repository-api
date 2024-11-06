<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	// COMMENT THIS LINES
//	ini_set('display_errors', 1);
//	ini_set('display_startup_errors', 1);
//	error_reporting(E_ALL);

	include('helper/_iobject.php');

	function sortURLs($a, $b) {
		$a_ = explode('.', $a);
		$a__ = $a_[count($a_) - 2];

		$b_ = explode('.', $b);
		$b__ = $b_[count($b_) - 2];

		if ($a__ == $b__) {
			return 0;
		}
		return ($a__ < $b__) ? -1 : 1;
	}

	if ('GET' !== $_SERVER['REQUEST_METHOD']) {
		header('HTTP/1.0 405 Method Not Allowed');
		echo json_encode((object) array(
			'error' => 405,
			'message' => 'Method Not Allowed. HTTP verb used to access this page is not allowed',
		));
		return;
	} else {
		include('helper/_date.php');

		$hvd = array();
		$date = getDateParameter();

		if (is_null($date)) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Path parameter for \'date\' is invalid',
			));
			exit;
		}

		$yesterday = date('Y-m-d', strtotime($date . ' -1 day'));

		$path = '../api-data/hvd-access-url-date/' . substr($date, 0, 7) . '/hvd-access-date-' . $date . '.json';
		$pathYesterday = '../api-data/hvd-access-url-date/' . substr($yesterday, 0, 7) . '/hvd-access-date-' . $yesterday . '.json';

		if (!file_exists($path) || !file_exists($pathYesterday)) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Path parameter for \'date\' is invalid',
			));
			exit;
		}

		$jsonToday = json_decode(file_get_contents($path));
		$jsonYesterday = json_decode(file_get_contents($pathYesterday));
//		$jsonBoth = [];
		$urlsBoth = [];

		foreach($jsonToday as &$object) {
			$url = null;
			if ($object->accessURL) {
				$url = parse_url($object->accessURL, PHP_URL_HOST);
				$url = preg_replace('#^www\.(.+\.)#i', '$1', $url);
			}

			if ($url) {
				$urlsBoth[] = $url;
			}
		}
		foreach($jsonYesterday as &$object) {
			$url = null;
			if ($object->accessURL) {
				$url = parse_url($object->accessURL, PHP_URL_HOST);
				$url = preg_replace('#^www\.(.+\.)#i', '$1', $url);
			}

			if ($url) {
				$urlsBoth[] = $url;
			}
		}

		$toda = [];
		$yest = [];
		for ($j = count($jsonToday) - 1; $j >= 0; --$j) {
			$toda[] = $jsonToday[$j]->identifier . '§' . $jsonToday[$j]->accessURL;
		}
		for ($j = count($jsonYesterday) - 1; $j >= 0; --$j) {
			$yest[] = $jsonYesterday[$j]->identifier . '§' . $jsonYesterday[$j]->accessURL;
		}

		$newToday = array_diff($toda, $yest);
		$newTodayObj = [];
		$now = microtime(true);
		foreach($newToday as $value) {
			$parts = explode('§', $value);
			$iObject = findIObjectByURL($parts[1]);

			if ($iObject) {
				$duration = round(microtime(true) - $now, 3);
				if ($duration < 5) {
//					$iObject = updateIObjectFile($iObject);
					$iObject = loadIObject($iObject);
				}
			}

			$newTodayObj[] = (object) array(
				'datasetIdentifier' => $parts[0],
				'distributionAccessURL' => $parts[1],
				'distribution' => $iObject,
			);
		}

		$removedToday = array_diff($yest, $toda);
		$removedTodayObj = [];
		foreach($removedToday as $value) {
			$parts = explode('§', $value);
			$iObject = findIObjectByURL($parts[1]);

			if ($iObject) {
				$duration = round(microtime(true) - $now, 3);
				if ($duration < 5) {
//					$iObject = updateIObjectFile($iObject);
					$iObject = loadIObject($iObject);
				}
			}

			$removedTodayObj[] = (object) array(
				'datasetIdentifier' => $parts[0],
				'distributionAccessURL' => $parts[1],
				'distribution' => $iObject,
			);
		}

		$urlsToday = [];
		foreach($newTodayObj as $object) {
			$url = parse_url($object->distributionAccessURL, PHP_URL_HOST);
			$url = preg_replace('#^www\.(.+\.)#i', '$1', $url);

			if ($url) {
				$urlsToday[] = $url;
			}
		}

		$urlsYesterday = [];
		foreach($removedTodayObj as $object) {
			$url = parse_url($object->distributionAccessURL, PHP_URL_HOST);
			$url = preg_replace('#^www\.(.+\.)#i', '$1', $url);

			if ($url) {
				$urlsYesterday[] = $url;
			}
		}

		$urlsBoth = array_values(array_unique($urlsBoth));
		$urlsToday = array_values(array_unique($urlsToday));
		$urlsYesterday = array_values(array_unique($urlsYesterday));
		$urlsNew = array_diff($urlsToday, $urlsBoth);
		$urlsDeleted = array_diff($urlsYesterday, $urlsBoth);

		usort($urlsNew, 'sortURLs');
		usort($urlsToday, 'sortURLs');
		usort($urlsDeleted, 'sortURLs');
		usort($urlsYesterday, 'sortURLs');

		$hvd = (object) array(
			'hostsNew' => $urlsNew,
			'hostsAdded' => $urlsToday,
			'hostsRemoved' => $urlsYesterday,
			'hostsDeleted' => $urlsDeleted,
			'added' => $newTodayObj,
			'removed' => $removedTodayObj,
		);
	}

	echo json_encode($hvd);
?>