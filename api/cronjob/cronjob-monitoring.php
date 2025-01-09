<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include('../helper/_lobject.php');
	include('../helper/_provider.php');
	include('../helper/_sobject.php');

	$basePath = '../../api-data/';
	$filePath = $basePath . 'monitor-date/' . date('Y-m') . '/monitor-' . date('Y-m-d') . '.json';

	function getCounts() {
		$today = date('Y-m-d');
		$path = '../../api-data/counts-date/' . substr($today, 0, 7) . '/counts-' . $today . '.json';

		if (!file_exists($path)) {
			return [];
		}

		return json_decode(file_get_contents($path));
	}

	function getData() {
		global $loadedProviders;

		$errors = [];
		$warnings = [];
		$infos = [];

		$counts = (array) getCounts();

		foreach($loadedProviders as $provider) {
			$pid = providerGetPID($provider);
			$sid = providerGetSID($provider);
			$sObject = findSObject($sid);
			$url = providerGetURL($provider);
			$deeplink = providerGetDeepLink($provider);

			if ($url && ($url !== '')) {
				// done
			} else {
				$errors[] = (object) array('pid' => $pid, 'message' => 'noURLFound');
			}

			if ($deeplink && ($deeplink !== '')) {
				// done
			} else {
				$errors[] = (object) array('pid' => $pid, 'message' => 'noDeepLinkFound');
			}

			if (!$sObject) {
				$warnings[] = (object) array('pid' => $pid, 'message' => 'noSObjectFound');
			}

			$count = 0;
			if (isset($counts[$pid])) {
				$count = $counts[$pid];
			}
			if ($count == 0) {
				$errors[] = (object) array('pid' => $pid, 'message' => 'couldNotCountPObject');
			}

			$today = date('Y-m-d');
			$lObjects = getLObjectChildren($pid);
			$lCount = 0;
			$sCount = 0;
			foreach($lObjects as $lObject) {
				if ($lObject['lastseen'] === $today) {
					++$lCount;

					if ($lObject['sobject']) {
						++$sCount;
					}
				}
			}

			if ($lCount === 0) {
				$errors[] = (object) array('pid' => $pid, 'message' => 'noLObjectsFound');
			} else {
			}
			if ($sCount < $lCount) {
				$infos[] = (object) array('pid' => $pid, 'message' => 'missingSObjects', 'sObjects' => $sCount, 'lObjects' => $lCount);
			}
		}

		return (object) array(
			'errors' => $errors,
			'warnings' => $warnings,
			'infos' => $infos
		);
	}

	$dir = dirname($filePath);
	if (!file_exists($dir)) {
		mkdir($dir, 0777, true);
	}

	if (!file_exists($filePath)) {
		$data = getData();
		file_put_contents($filePath, json_encode($data));
	}

	echo json_encode(array('result' => 'done'));
?>