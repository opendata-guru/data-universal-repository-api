<?php
	$loadedProviders = [];
	$filePObjects = __DIR__ . '/../../api-data/providers.csv';

	loadMappingFileProviders($filePObjects, $loadedProviders);
	$hashPObjects = md5(serialize($loadedProviders));

	function getProvider() {
		global $loadedProviders;

		$parameterPID = htmlspecialchars($_GET['pID']);
		$parameterPID2 = htmlspecialchars($_GET['pid']);
		$error = null;
		$url = '';

		if ($parameterPID == '') {
			$parameterPID = $parameterPID2;
			if ($parameterPID == '') {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Parameter \'pID\' is not set',
					'parameter' => $parameterPID,
				);
			}
		}

		if (!$error) {
			foreach($loadedProviders as $provider) {
				if (providerGetPID($provider) == $parameterPID) {
					$url = providerGetDeepLink($provider);
				}
			}

			if ($url === '') {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'pID\' parameter.',
					'parameter' => $parameterPID,
				);
			}
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterPID,
			'url' => $url,
		);
	}

	function postPID() {
		global $loadedProviders;

		$parameterPID = htmlspecialchars($_GET['pid']);
		$error = null;
		$url = '';

		if ($parameterPID == '') {
			$error = (object) array(
				'error' => 400,
				'header' => 'HTTP/1.0 400 Bad Request',
				'message' => 'Bad Request. Path parameter \'pID\' is not set',
				'parameter' => $parameterPID,
			);
		}

		if (!$error) {
			$pObject = findPObjectByPID($parameterPID);

			if (is_null($pObject)) {
				$error = (object) array(
					'error' => 400,
					'header' => 'HTTP/1.0 400 Bad Request',
					'message' => 'Bad Request. Unknown ID in the \'pID\' path parameter.',
					'parameter' => $parameterPID,
				);
			}
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterPID,
			'pObject' => $pObject,
		);
	}

	function postPObject() {
		include('helper/_link.php');

		$parameterURL = trim(htmlspecialchars($_GET['url']));
		$deepLink = '';

		if ($parameterURL == '') {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode((object) array(
				'error' => 400,
				'message' => 'Bad Request. Parameter \'url\' is not set',
			));
			exit;
		} else {
			$deepLink = $parameterURL;
		}

		$url = $deepLink;
		$link = getLinkWithParam($deepLink);
		if (is_string($link->url) && ($link->url !== '')) {
			$url = $link->url;
		}

		$link = (object) array(
			'error' => null,
			'parameter' => $deepLink,
			'system' => null,
			'url' => $url,
		);

		$pObject = findPObjectByLink($link);
		if (!$pObject) {
			$pObject = pushPObject(createPID(), '', $url, $deepLink);
		}
		saveMappingFilePObjects();

		return (object) array(
			'pid' => providerGetPID($pObject),
			'sid' => providerGetSID($pObject),
			'url' => providerGetURL($pObject),
			'deepLink' => providerGetDeepLink($pObject),
		);
	}

	function loadMappingFileProviders($file, &$mapping) {
		$idDeepLink = null;
		$idModified = null;
		$idPID = null;
		$idSID = null;
		$idURL = null;

		$lines = explode("\n", file_get_contents($file));
		$mappingHeader = str_getcsv($lines[0], ',');

		for ($m = 0; $m < count($mappingHeader); ++$m) {
			if ($mappingHeader[$m] === 'pid') {
				$idPID = $m;
			} else if ($mappingHeader[$m] === 'sid') {
				$idSID = $m;
			} else if ($mappingHeader[$m] === 'url') {
				$idURL = $m;
			} else if ($mappingHeader[$m] === 'deeplink') {
				$idDeepLink = $m;
			} else if ($mappingHeader[$m] === 'modified') {
				$idModified = $m;
			}
		}

		array_shift($lines);
		foreach($lines as $line) {
			if ($line != '') {
				$arr = str_getcsv($line, ',');
				$mapping[] = [
					$arr[$idPID] ?: '',
					$arr[$idSID] ?: '',
					$arr[$idURL] ?: '',
					$arr[$idDeepLink] ?: '',
					$arr[$idModified] ?: ''
				];
			}
		}
	}

	function saveMappingFilePObjects() {
		global $loadedProviders;
		global $hashPObjects;
		global $filePObjects;

		$newHash = md5(serialize($loadedProviders));

		if ($hashPObjects !== $newHash) {
			$header = [
				'pid',
				'sid',
				'url',
				'deeplink',
				'modified'
			];

			$fp = fopen($filePObjects, 'wb');
			fputcsv($fp, $header, ',');
			foreach ($loadedProviders as $line) {
				fputcsv($fp, [
					providerGetPID($line),
					providerGetSID($line),
					providerGetURL($line),
					providerGetDeepLink($line),
					providerGetModified($line),
				], ',');
			}
			fclose($fp);

			$hashPObjects = $newHash;
		}
	}

	function pushPObject($pID, $sID, $url, $deepLink) {
		global $loadedProviders;

		$loadedProviders[] = [
			$pID ?: createPID(),
			$sID ?: '',
			$url ?: '',
			$deepLink ?: '',
			date('Y-m-d')
		];

		return end($loadedProviders);
	}

	function createPID() {
		global $loadedProviders;

		// https://www.rechner.club/kombinatorik/anzahl-variationen-geordnet-ohne-wiederholung-berechnen
		// objects  | 61    | 61      | 61         | 61
		// draws    | 2     | 3       | 4          | 5
		// variants | 3,660 | 215,940 | 12,524,520 | 713,897,640

		$ALLOWED_CHARS = '0123456789abcdefghijklmnoqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$prefix = 'p';
		$length = 3;

		$usedPIDs = [];
		foreach($loadedProviders as $pObject) {
			$usedPIDs[] = providerGetPID($pObject);
		}
		$usedPIDs = array_filter($usedPIDs);

		do {
			$pid = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);
		} while(in_array($pid, $usedPIDs));

		return $pid;
	}

	function providerGetPID($provider) {
		return $provider[0];
	}
	function providerGetSID($provider) {
		return $provider[1];
	}
	function providerGetURL($provider) {
		return $provider[2];
	}
	function providerGetDeepLink($provider) {
		return $provider[3];
	}
	function providerGetModified($provider) {
		return $provider[4];
	}

	function findPObjectByLink($link) {
		global $loadedProviders;

		foreach($loadedProviders as $pObject) {
			if ($link->url != '') {
				if (providerGetURL($pObject) == $link->url) {
					return $pObject;
				}
				if (providerGetDeepLink($pObject) == $link->url) {
					return $pObject;
				}
			}
			if ($link->parameter != '') {
				if (providerGetURL($pObject) == $link->parameter) {
					return $pObject;
				}
				if (providerGetDeepLink($pObject) == $link->parameter) {
					return $pObject;
				}
			}
		}

		return null;
	}

	function findPObjectByPID($pid) {
		global $loadedProviders;

		foreach($loadedProviders as $pObject) {
			if (providerGetPID($pObject) == $pid) {
				return $pObject;
			}
		}

		return null;
	}
?>