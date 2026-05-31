<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET');
	header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	/*
	1) get a website url and crawl to an appointment url
	   e.g. https://www.berlin.de/ -> https://service.berlin.de/terminvereinbarung/ or https://service.berlin.de/terminvereinbarung/termin/
	   e.g. https://www.muenchen.de/ -> https://stadt.muenchen.de/buergerservice/terminvereinbarung.html
	2) get an appointment url and start the appointment process
	3) get an ARS and do the same es 2)
	*/

	/*
	ZMS (Zeitmanagementsystem)
	- Berlin create ZMS as open source https://gitlab.com/eappointment/eappointment
	- Munich is using ZMS too and publish at https://github.com/it-at-m/eappointment/
	- Berlin use ServerSiteRendering. It has a status page at https://service.berlin.de/terminvereinbarung/termin/status/
	- Munich use some API: https://www48.muenchen.de/buergeransicht/api/citizen/status/
	*/

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	$test = 'ready';

	echo json_encode((object) array(
		'test' => $test,
	));
?>