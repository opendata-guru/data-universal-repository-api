<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	// https://www.rechner.club/kombinatorik/anzahl-variationen-geordnet-ohne-wiederholung-berechnen
	// 61 objects
	// 4 draws
	// 12.524.520 variants

	$ALLOWED_CHARS = '0123456789abcdefghijklmnoqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$prefix = 'p';
	$length = 4;

	$id = $prefix . substr(str_shuffle($ALLOWED_CHARS), 0, $length);

	echo json_encode((object) array(
		'id' => $id,
	));
?>