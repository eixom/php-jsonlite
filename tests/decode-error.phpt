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

$traces = jsonlite_get_trace();
assert($traces === array(
		array('map.terminal', 3, 6,),
		array('brackets.match', 3, 3, '}'),
	));

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
	));

$jsonlite = '[true';
$result = jsonlite_decode($jsonlite);
assert($result === null);

$traces = jsonlite_get_trace();
assert($traces === array(
		array('brackets.match', 1, 1, ']'),
	));

$traces = jsonlite_get_trace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(1, 1),
			'chars'  => '[t',
			'detail' => ']'
		)
	));

$jsonlite = ']';
$result = jsonlite_decode($jsonlite);
assert($result === null);

$traces = jsonlite_get_trace();
assert($traces === array(
		array('parse.char', 0, 0),
	));

$traces = jsonlite_get_trace(true);
assert($traces === array(
		array(
			'msg'   => 'parse.char',
			'range' =>
				array(0, 0),
			'chars' => ']'
		)
	));

$jsonlite = '[,';
$result = jsonlite_decode($jsonlite);
assert($result === null);

$traces = jsonlite_get_trace();
assert($traces === array(
		array('brackets.match', 0, 0, ']'),
	));

$traces = jsonlite_get_trace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(0, 0),
			'chars'  => '[',
			'detail' => ']'
		)
	));


$jsonlite = '[,';
$result = jsonlite_decode($jsonlite);
assert($result === null);

$traces = jsonlite_get_trace();
assert($traces === array(
		array('brackets.match', 0, 0, ']'),
	));

$traces = jsonlite_get_trace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(0, 0),
			'chars'  => '[',
			'detail' => ']'
		)
	));


echo '[success] error';
?>
--EXPECT--
[success] error