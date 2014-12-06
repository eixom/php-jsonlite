<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * string
 */
$value = 'test str';
assert_encode_result($value, 'test str', '"test str"', 'test str');

$value = 'false';
assert_encode_result($value, '"false"', '"false"', '"false"');

$value = 'test"str';
assert_encode_result($value, 'test\\"str', '"test\\"str"', 'test\\"str');

$value = 'test {key:string}';
assert_encode_result($value, '"test {key:string}"', '"test {key:string}"', '"test {key:string}"');

$value = 'test:';
assert_encode_result($value, 'test:', '"test:"', 'test:');

$value = '123';
assert_encode_result($value, '123', '123', '"123"');

$value = '0123';
assert_encode_result($value, '0123', '"0123"', '0123');

$value = '00';
assert_encode_result($value, '00', '"00"', '00');

$value = '0.123';
assert_encode_result($value, '0.123', '0.123', '"0.123"');

$value = '';
assert_encode_result($value, '', '""', '');

$value = '127.0.0.1';
assert_encode_result($value, '127.0.0.1', '"127.0.0.1"', '127.0.0.1');

$value = '2014-12-07 01:01:20';
assert_encode_result($value, '2014-12-07 01:01:20', '"2014-12-07 01:01:20"', '2014-12-07 01:01:20');

$value = '中文';
assert_encode_result($value, '中文', '"中文"', '中文');


echo '[success] string';
