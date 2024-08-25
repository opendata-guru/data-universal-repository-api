<?php
	function systemSPARQL($url) {
		// Jena Fuseki
		// https://jena.apache.org/documentation/fuseki2/fuseki-server-info.html
		//
		// Virtuoso
		$endpoint = $url . '?query=';
		$sparql = '
SELECT
	( bif:sys_stat("st_dbms_name")          AS ?name )
	( bif:sys_stat("st_dbms_ver")           AS ?version )
	( bif:sys_stat("st_build_thread_model") AS ?thread )
	( bif:sys_stat("st_build_opsys_id")     AS ?opsys )
	( bif:sys_stat("st_build_date")         AS ?date )
	( bif:sys_stat("git_head")              AS ?git_head )   
WHERE
	{  ?s  ?p  ?o  }
LIMIT 1
		';
		$sparqlCommercialEdition = '
SELECT
	( bif:sys_stat("st_lic_owner")         AS ?owner )
	( bif:sys_stat("st_lic_serial_number") AS ?serial )
WHERE
	{  ?s  ?p  ?o  }
LIMIT 1
		';

		$opts = [
			"http" => [
				"method" => "GET",
				"header" => "Accept: application/sparql-results+json,*/*;q=0.9\r\n"
			]
		];
		$context = stream_context_create($opts);

		$json = json_decode(file_get_contents($endpoint . urlencode($sparql), false, $context));

		$git_head = $json ? $json->results->bindings[0]->git_head->value : '';
		$version = $json ? $json->results->bindings[0]->version->value : '';
		$thread = $json ? $json->results->bindings[0]->thread->value : '';
		$opsys = $json ? $json->results->bindings[0]->opsys->value : '';
		$name = $json ? $json->results->bindings[0]->name->value : '';
		$date = $json ? $json->results->bindings[0]->date->value : '';

		$json = json_decode(file_get_contents($endpoint . urlencode($sparqlCommercialEdition), false, $context));

		$owner = $json ? $json->results->bindings[0]->name->owner : '';
		$serial = $json ? $json->results->bindings[0]->name->serial : '';

		echo json_encode((object) array(
			'build' => (object) array(
				'date' => $date,
				'gitHead' => $git_head,
				'os' => $opsys,
				'thread' => $thread,
			),
			'commercial' => (object) array(
				'owner' => $owner,
				'serial' => $serial,
			),
			'name' => $name,
			'system' => 'SPARQL',
			'url' => $url,
			'version' => $version,
		));
	}
?>