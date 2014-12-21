--TEST--
check decoding: error
--FILE--
<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * error
 */

$jsonlite = '{k:v]';
$result = jsonlite_decode($jsonlite);
assert($result === null);
$traces = jsonlite_get_trace(true);
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
$result = jsonlite_decode($jsonlite);
assert($result === null);
$traces = jsonlite_get_trace(true);
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
$result = jsonlite_decode($jsonlite);
assert($result === null);
$traces = jsonlite_get_trace(true);
assert($traces === array(
		array(
			'msg'   => 'parse.char',
			'range' =>
				array(0, 0),
			'chars' => ']'
		)
	), var_export($traces, true));

$jsonlite = '[,';
$result = jsonlite_decode($jsonlite);
assert($result === null);
$traces = jsonlite_get_trace(true);
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
$result = jsonlite_decode($jsonlite);
assert($result === null);
$traces = jsonlite_get_trace(true);
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
?>
--EXPECT--
[success] error