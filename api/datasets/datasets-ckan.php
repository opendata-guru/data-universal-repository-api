<?php
	function datasetsCKAN($url) {
		$organisatoinShowSuffix = '/api/3/action/organization_show?include_dataset_count=false&include_datasets=true&include_extras=false&include_followers=false&include_groups=false&include_tags=false&include_users=false&id=';
		$packageListSuffix = '/api/3/action/package_list';

		$paramId = htmlspecialchars($_GET['sub_id']);

		if ($paramId === '') {
			$uri = $url . $packageListSuffix;
			$json = json_decode(file_get_contents($uri));

			echo json_encode($json->result);
		} else {
			$uri = $url . $organisatoinShowSuffix . $paramId;
			$json = json_decode(file_get_contents($uri));

			$data = [];

			foreach($json->result->packages as $dataset) {
//				$data[] = $dataset->id;
				$data[] = $dataset->name;
			}

			echo json_encode($data);
		}
	}
?>