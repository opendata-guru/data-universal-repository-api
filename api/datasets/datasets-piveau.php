<?php
	function datasetsPiveau($url) {
		$datasetsSuffix = '/api/hub/search/datasets?catalogue=';
		$searchSuffix = '/api/hub/search/search?includes=id&filters=dataset&aggregationAllFields=false&limit=1000';

		$paramId = htmlspecialchars($_GET['sub_id']);
		$data = [];

		if ($paramId === '') {
			$uri = $url . $searchSuffix;
			$source = file_get_contents($uri);

			$list = json_decode($source);

			foreach($list->result->results as $result) {
				$data[] = $result->id;
			}
		} else {
			$uri = $url . $datasetsSuffix . $paramId;
			$source = file_get_contents($uri);

			$data = json_decode($source);
		}

		echo json_encode($data);
	}
?>