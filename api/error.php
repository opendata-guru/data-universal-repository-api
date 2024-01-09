<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET');
	header('Access-Control-Allow-Headers: X-Requested-With');
	header('Content-Type: application/json; charset=utf-8');

	$error = intval(htmlspecialchars($_GET['error']));
	$message = '';

	if (400 == $error) {
		$error = 400;
		$message = 'Bad Request';
	} else if (401 == $error) {
		$error = 401;
		$message = 'Unauthorized';
	} else if (402 == $error) {
		$error = 402;
		$message = 'Payment Required';
	} else if (403 == $error) {
		$error = 403;
		$message = 'Forbidden';
	} else if (500 == $error) {
		$error = 500;
		$message = 'Internal Server Error';
	} else {
		$error = 404;
		$message = 'Not Found';
	}

	header("HTTP/1.0 $error $message");

	echo json_encode((object) array(
		'error' => $error,
		'message' => $message,
	));
?>