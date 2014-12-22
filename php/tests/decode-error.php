<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * error
 */

$jsonlite = '{k:v]';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null);

$traces = $decoder->getTrace();
assert($traces === array(
		array('map.terminal', 3, 6,),
		array('brackets.match', 3, 3, '}'),
	), var_export($traces, true));

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
			'chars'  => ':v',
			'detail' => '}'
		)
	), var_export($traces, true));

$jsonlite = '[true';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null);

$traces = $decoder->getTrace();
assert($traces === array(
		array('brackets.match', 1, 1, ']'),
	), var_export($traces, true));

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
assert($result === null);

$traces = $decoder->getTrace();
assert($traces === array(
		array('parse.char', 0, 0),
	), var_export($traces, true));

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
assert($result === null);

$traces = $decoder->getTrace();
assert($traces === array(
		array('brackets.match', 0, 0, ']'),
	), var_export($traces, true));

$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(0, 0),
			'chars'  => '[',
			'detail' => ']'
		)
	), var_export($traces, true));


$jsonlite = '[,';

$result = jsonlite_decode($jsonlite, true);
assert($result === null);

$traces = $decoder->getTrace();
assert($traces === array(
		array('brackets.match', 0, 0, ']'),
	), var_export($traces, true));

$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(0, 0),
			'chars'  => '[',
			'detail' => ']'
		)
	), var_export($traces, true));
echo '[success] error';
