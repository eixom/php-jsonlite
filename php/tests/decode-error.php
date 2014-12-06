<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * error
 */

$jsonlite = '{k:v]';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null, __LINE__);
$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'   => 'map.terminal',
			'range' =>
				array(3, 6),
			'chars' => ':v]'
		),
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(3, 3),
			'chars'  => ':v]',
			'detail' => '}'
		)
	), var_export($traces, true));


$jsonlite = '[true';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null, __LINE__);
$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(1, 1),
			'chars'  => '[t',
			'detail' => ']'
		)
	), var_export($traces, true));

$jsonlite = ']';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null, __LINE__);
$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'   => 'parse.char',
			'range' =>
				array(0, 0),
			'chars' => ']'
		)
	), var_export($traces, true));

$jsonlite = '[,';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null, __LINE__);
$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(0, 0),
			'chars'  => ',',
			'detail' => ']'
		)
	), var_export($traces, true));


$jsonlite = '[,';

$result = jsonlite_decode($jsonlite, true);
assert($result === null, __LINE__);
$traces = jsonlite_get_trace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(0, 0),
			'chars'  => ',',
			'detail' => ']'
		)
	), var_export($traces, true));


echo '[success] error';
