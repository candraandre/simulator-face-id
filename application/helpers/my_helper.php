<?php

if (!function_exists('http_header_message')) {
	/**
	 * @param int $status_header
	 *
	 * @return string
	 */
	function http_header_message($status_header)
	{
		$http_response = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		);

		if (!is_null($status_header)) {
			if (array_key_exists($status_header, $http_response)) {
				return $http_response[$status_header];
			}
			return "Unknown HTTP status code: " . htmlentities($status_header);
		}

		return $http_response[200];
	}
}

function json_response_success(array $data = null)
{
	header("Content-Type: application/json; charset=utf-8");
	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Pragma: no-cache");
	header("Expires: 0");

	http_response_code(200);

	$errorResponse = array(
		'error' => array(
			'errorCode' => 6018,
			'errorMessage' => "Sukses"
		),
		'httpResponseCode' => 200
	);

	if (is_array($data) && count($data) > 0)
	{
		$errorResponse['matchScore'] = rand(5, 10);
		if (!empty($data['transactionId'])) $errorResponse['transactionId'] = $data['transactionId'];
		$errorResponse['uid'] = $data['user_id'];
		$errorResponse['verificationResult'] = true;
		$errorResponse['quotaLimiter'] = rand(10, 100);
		//$errorResponse['ipAddress'] = $data['ip'];
	}

	echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function json_response_error($errorCode, $errorMessage, array $data = null)
{
	header("Content-Type: application/json; charset=utf-8");
	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Pragma: no-cache");
	header("Expires: 0");

	http_response_code(200);

	$errorResponse = array(
		'error' => array(
			'errorCode' => $errorCode,
			'errorMessage' => $errorMessage
		),
		'httpResponseCode' => 200
	);

	if (is_array($data) && count($data) > 0)
	{
		$errorResponse['matchScore'] = rand(0, 3);
		$errorResponse['transactionId'] = $data['transactionId'];
		$errorResponse['uid'] = $data['user_id'];
		$errorResponse['verificationResult'] = false;
		$errorResponse['quotaLimiter'] = 0;
	}

	echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function is_valid_fields(array $in, array $valid_fields)
{
	$result = array();
	foreach ($valid_fields as $k)
	{
		if (array_key_exists($k, $in))
		{
			$result[$k] = $in[$k];
		}
	}

	return (is_array($result) && count($result) == count($valid_fields)) ? $result : false;
}

function required_has_values(array $in, array $required_fields)
{
	foreach ($required_fields as $k)
	{
		$value = $in[$k];
		if (empty($value)) return false;
	}
	return true;
}

if (!function_exists('current_datetime'))
{
	/**
	 * Current datetime with format (default: Y-m-d H:i:s)
	 *
	 * @param string $format
	 *
	 * @return false|string
	 */
	function current_datetime($format = "Y-m-d H:i:s")
	{
		$datetime = date_timezone_set(date_create(date('Y-m-d H:i:s')), new DateTimeZone('Asia/Jakarta'));
		return date_format($datetime, $format);
	}
}
