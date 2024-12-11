<?php
	// https://github.com/cubiclesoft/js-fileexplorer

	$dict = array(
		'de' => (object) array(
			'all_objects' => 'Alle Objekte',
			'defects' => 'Defekte Objekte',
			'journal' => 'Tagebuch',
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
		$entry = array(
			'id' => $file->id,
			'name' => $file->title,
			'type' => 'file',
			'hash' => getEntryHash($path, $file->id),

			// optional
			'tooltip' => getTooltip($file->title, [$file->error]),
//			'size' => 127,
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

	function getSampleFile($path, $lang, $result) {
		$result->files[] = (object) array(
			'id' => 'foobar',
			'title' => 'File Name.txt',
			'error' => null,
		);

		return $result;
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
		} else if (count($path) < 2) {
			$level = $path[0];
			array_shift($path);

			$datetime = new DateTime('today');
			$datetime->add(new DateInterval('P1D'));

			do {
				$datetime->sub(new DateInterval('P1D'));
				$iso = $datetime->format('Y-m-d');
				$titleDE = intval($datetime->format('d')) . '. ' . $dict[$lang]->{'month' . $datetime->format('m')};
				$titleEN = $dict[$lang]->{'month' . $datetime->format('m')} . ' ' . intval($datetime->format('d'));
				$title = 'en' === $lang ? $titleEN : $titleDE;

				if (0 === strpos($iso, $level)) {
					$result->folders[] = (object) array(
						'id' => $iso,
						'title' => $title
					);
				}
			} while($iso !== '2024-08-02');
		} else {
			$level = $path[0];
			array_shift($path);
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
					$name = $iObject->url;
					$name = trim($name, '/');
					$name = end(explode('/', $name));
					$name = reset(explode('?', $name));

					$result->files[] = (object) array(
						'id' => $iObject->iid,
						'title' => $name,
						'error' => $error,
					);
				}
			}
		}

		return $result;
	}

	function getFilesAndFoldersHVDTombstones($path, $lang, $result) {
		$iObjects = getAtticIObjects();

		if (count($path) < 1) {
			foreach($iObjects as $iObject) {
				$name = $iObject->url;
				$name = trim($name, '/');
				$name = end(explode('/', $name));
				$name = reset(explode('?', $name));

				$result->files[] = (object) array(
					'id' => $iObject->iid,
					'title' => $name,
				);
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

		return getSampleFile($path, $lang, $result);
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