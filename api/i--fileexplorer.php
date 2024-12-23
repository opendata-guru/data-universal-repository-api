<?php
	// https://github.com/cubiclesoft/js-fileexplorer

	$dict = array(
		'de' => (object) array(
			'all_objects' => 'Alle Objekte',
			'defects' => 'Defekte Objekte',
			'journal' => 'Tagebuch',
			'linksAdded' => 'Neue Links',
			'linksRemoved' => 'Entfernte Links',
			'member_states' => 'EU-Mitgliedstaaten',
			'month01' => 'Januar',
			'month02' => 'Februar',
			'month03' => 'März',
			'month04' => 'April',
			'month05' => 'Mai',
			'month06' => 'Juni',
			'month07' => 'Juli',
			'month08' => 'August',
			'month09' => 'September',
			'month10' => 'Oktober',
			'month11' => 'November',
			'month12' => 'Dezember',
			'tombstones' => 'Archiv',
		),
		'en' => (object) array(
			'all_objects' => 'All objects',
			'defects' => 'Defective objects',
			'journal' => 'Journal',
			'linksAdded' => 'New links',
			'linksRemoved' => 'Removed links',
			'member_states' => 'EU member states',
			'month01' => 'January',
			'month02' => 'February',
			'month03' => 'March',
			'month04' => 'April',
			'month05' => 'May',
			'month06' => 'June',
			'month07' => 'July',
			'month08' => 'August',
			'month09' => 'September',
			'month10' => 'October',
			'month11' => 'November',
			'month12' => 'December',
			'tombstones' => 'Archive',
		),
	);

	$feStupidID = 0;

	function curlAPI($url) {
		$headers = [
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
		];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$ret = curl_exec($ch);
		curl_close($ch);

		return $ret;
	}

	function callAPI($link) {
		$uri = 'https://' . $_SERVER['HTTP_HOST'] . htmlspecialchars($_SERVER['REQUEST_URI']);
		$uri = dirname($uri);
		$uri .= '/' . $link;

		$data = curlAPI($uri);
		return json_decode($data);
	}

	function getEntryHash($path, $file)
	{
		return md5($path . '|' . $file);
	}

	function getTooltip($title, $arr) {
		$tooltip = array();

		if (strlen($title) > 35) {
			$tooltip[] = $title;
		}

		if ($arr) {
			$tooltip = array_merge($tooltip, $arr);
		}

		return implode("\n", $tooltip);
	}

	function buildFile($path, $file) {
		global $feStupidID;

		$iObject = $file->iObject;
		$filetype = '';

		if (isset($iObject) && isset($iObject->insights) && isset($iObject->insights->contentType)) {
			$filetype = end(explode(':', $iObject->insights->contentType));
		}
		$filetype = str_replace(':', '_', $filetype);
		$filetype = str_replace('-', '_', $filetype);
		if ($file->error) {
			$filetype = 'error';
		}

		++$feStupidID;

		$entry = array(
			'id' => $feStupidID,
			'name' => $file->title,
			'type' => 'file',
			'hash' => getEntryHash($path, $iObject->iid),

			// optional
			'tooltip' => getTooltip($file->title, []),
			'overlay' => 'filetype_' . $filetype,
//			'size' => 127,

			// customized
			'datasetIdentifier' => $file->datasetIdentifier,
			'iObject' => $iObject,
		);

//		$entry["thumb"] = $options["base_url"] . substr($path, strlen($options["base_dir"])) . "/" . $file;
//		$entry["thumb"] = $options["thumbs_url"] . $filename;
//		$entry["thumb"] = $options["thumb_create_url"] . (strpos($options["thumb_create_url"], "?") !== false ? "&" : "?") . "path=" . urlencode(json_encode(explode("/", substr($path, strlen($options["base_dir"]))), JSON_UNESCAPED_SLASHES)) . "&id=" . urlencode($file);
//		$entry["thumb"] = $options["base_url"] . substr($path, strlen($options["base_dir"])) . "/" . $file;
//		$entry["thumb"] = $options["thumbs_url"] . $filename;

		return $entry;
	}

	function buildFolder($path, $folder) {
		$entry = array(
			'id' => $folder->id,
			'name' => $folder->title,
			'type' => 'folder',
			'hash' => getEntryHash($path, $folder->id),

			// optional
			'tooltip' => getTooltip($folder->title, []),
//			'overlay' => 'overlay_own_class',
//			'attrs' => ???,
//			'thumb' => 'https://opendata.guru/govdata/assets/folder.svg',
		);

		return $entry;
	}

	function getError($iObject) {
		if (!$iObject) {
			return null;
		}

		$error = isset($iObject->insights) ? $iObject->insights->error : null;

		if ($error) {
			if ('string' === gettype($error)) {
				return trim($error);
			} else {
				if (isset($error->{'@attributes'}) && isset($error->{'@attributes'}->code)) {
					return $error->{'@attributes'}->code;
				} else {
					return json_encode($error);
				}
			}
		}

		return null;
	}

	function getFilesAndFoldersHVDDataset($dataset) {
		$datasetIdentifier = $dataset->datasetIdentifier;
		$iObject = $dataset->distribution;

		$filetype = '';
		if (isset($iObject->insights) && isset($iObject->insights->contentType)) {
			$filetype = end(explode(':', $iObject->insights->contentType));
		}

		$error = getError($iObject);

		return (object) array(
			'title' => $iObject->iid . '.'. $filetype,
			'error' => $error,
			'datasetIdentifier' => $datasetIdentifier,
			'iObject' => $iObject,
		);
	}

	function getFilesAndFoldersHVDJournal($path, $lang, $result) {
		global $dict;

		if (count($path) < 1) {
			$datetime = new DateTime('today');
			$datetime->sub(new DateInterval('P' . (intval($datetime->format('d')) - 1) . 'D'));
			$datetime->add(new DateInterval('P1M'));

			do {
				$datetime->sub(new DateInterval('P1M'));
				$iso = $datetime->format('Y-m');

				$result->folders[] = (object) array(
					'id' => $iso,
					'title' => $iso . ' ' . $dict[$lang]->{'month' . $datetime->format('m')}
				);
			} while($iso !== '2024-08');

			return $result;
		}

		$pathYM = $path[0];
		array_shift($path);

		if (count($path) < 1) {
			$datetime = new DateTime('today');
			$datetime->add(new DateInterval('P1D'));

			do {
				$datetime->sub(new DateInterval('P1D'));
				$iso = $datetime->format('Y-m-d');
				$titleDE = intval($datetime->format('d')) . '. ' . $dict[$lang]->{'month' . $datetime->format('m')};
				$titleEN = $dict[$lang]->{'month' . $datetime->format('m')} . ' ' . intval($datetime->format('d'));
				$title = 'en' === $lang ? $titleEN : $titleDE;

				if (0 === strpos($iso, $pathYM)) {
					$result->folders[] = (object) array(
						'id' => $iso,
						'title' => $title
					);
				}
			} while($iso !== '2024-08-02');

			return $result;
		}

		$pathYMD = $path[0];
		array_shift($path);

		if (count($path) < 1) {
			$result->folders[] = (object) array(
				'id' => 'added',
				'title' => $dict[$lang]->linksAdded
			);
			$result->folders[] = (object) array(
				'id' => 'removed',
				'title' => $dict[$lang]->linksRemoved
			);

			return $result;
		}

		$pathAction = $path[0];
		array_shift($path);

		if (count($path) < 1) {
			$api = callAPI('hvd/accessurls/' . $pathYMD . '/change');

			if ($api && isset($api->added) && isset($api->removed)) {
				if (('added' === $pathAction) && (0 < count($api->added))) {
					foreach($api->added as $dataset) {
						$result->files[] = getFilesAndFoldersHVDDataset($dataset);
					}
				}
				if (('removed' === $pathAction) && (0 < count($api->added))) {
					foreach($api->removed as $dataset) {
						$result->files[] = getFilesAndFoldersHVDDataset($dataset);
					}
				}
			}

			return $result;
		}

		return $result;
	}

	function getFilesAndFoldersHVDDefects($path, $lang, $result) {
		$iObjects = getErrorIObject();

		if (count($path) < 1) {
			$folders = [];

			foreach($iObjects as $iObject) {
				$error = getError($iObject);

				$folders[$error] = $error;
			}

			foreach($folders as $folder) {
				$result->folders[] = (object) array(
					'id' => md5($folder),
					'title' => $folder
				);
			}
		} else {
			$level = $path[0];
			array_shift($path);

			foreach($iObjects as $iObject) {
				if($level === md5(getError($iObject))) {
					$dataset = (object) array(
						'datasetIdentifier' => '',
						'distribution' => $iObject,
					);
					$result->files[] = getFilesAndFoldersHVDDataset($dataset);
				}
			}
		}

		return $result;
	}

	function getFilesAndFoldersHVDTombstones($path, $lang, $result) {
		$iObjects = getAtticIObjectsDetails();

		if (count($path) < 1) {
			foreach($iObjects as $iObject) {
				$dataset = (object) array(
					'datasetIdentifier' => '',
					'distribution' => $iObject,
				);
				$result->files[] = getFilesAndFoldersHVDDataset($dataset);
			}
		} else {
			$level = $path[0];
			array_shift($path);

			// nothing to do
		}

		return $result;
	}

	function getFilesAndFoldersHVD($path, $lang, $result) {
		global $dict;

		if (count($path) < 1) {
/*			$result->folders[] = (object) array(
				'id' => 'all_objects',
				'title' => $dict[$lang]->all_objects
			);*/
			$result->folders[] = (object) array(
				'id' => 'journal',
				'title' => $dict[$lang]->journal
			);
			$result->folders[] = (object) array(
				'id' => 'defects',
				'title' => $dict[$lang]->defects
			);
/*			$result->folders[] = (object) array(
				'id' => 'member_states',
				'title' => $dict[$lang]->member_states
			);*/
			$result->folders[] = (object) array(
				'id' => 'tombstones',
				'title' => $dict[$lang]->tombstones
			);

			return $result;
		}

		$level = $path[0];
		array_shift($path);

		if ('journal' === $level) {
			return getFilesAndFoldersHVDJournal($path, $lang, $result);
		} else if ('defects' === $level) {
			return getFilesAndFoldersHVDDefects($path, $lang, $result);
		} else if ('tombstones' === $level) {
			return getFilesAndFoldersHVDTombstones($path, $lang, $result);
		}

		return $result;
	}

	function getFilesAndFolders($path, $lang) {
		$result = (object) array(
			'files' => [],
			'folders' => [],
		);
		$path = explode('/', $path);

		if (count($path) < 1) {
			return null;
		}
		if ('hvd' === $path[0]) {
			array_shift($path);
			return getFilesAndFoldersHVD($path, $lang, $result);
		}

		return $result;
	}

	function processRefreshAction($param, $lang) {
		$path = isset($param['path']) ? $param['path'] : '';
		$dir = getFilesAndFolders($path, $lang);

		if (!$dir) {
			return array(
				'success' => false,
				'error' => 'Invalid path specified.',
				'errorcode' => 'invalid_path'
			);
		} else {
			$result = array(
				'success' => true,
				'entries' => array()
			);

			foreach($dir->folders as $folder) {
				$entry = buildFolder($path, $folder);
				if ($entry !== false) {
					$result['entries'][] = $entry;
				}
			}

			foreach($dir->files as $file) {
				$entry = buildFile($path, $file);
				if ($entry !== false) {
					$result['entries'][] = $entry;
				}
			}
		}

		return $result;
	}

	function handleFileExplorer() {
		global $dict;

		$param = $_POST;
		$action = isset($param['action']) ? $param['action'] : '';
		$lang = strtolower(isset($param['lang']) ? substr($param['lang'], 0, 2) : 'en');

		if (!isset($dict[$lang])) {
			$lang = 'en';
		}

		if ('file_explorer_refresh' === $action) {
			return processRefreshAction($param, $lang);
		}
		/*
		file_explorer_thumbnail
		file_explorer_rename
		file_explorer_file_info
		file_explorer_load_file
		file_explorer_save_file
		file_explorer_new_folder
		file_explorer_new_file
		file_explorer_upload_init
		file_explorer_download
		file_explorer_copy_init
		file_explorer_copy
		file_explorer_move
		file_explorer_recycle
		file_explorer_delete
		*/

		return null;
	}
?>