--TEST--
check decoding: string
--FILE--
<?php
require_once dirname(__FILE__) . '/helper.php';

/**
 * string
 */
$value = 'test str';
assert_decode_result($value, 'test str', 'test str', 'test str');

$value = 'false';
assert_decode_result($value, 'false', 'false', 'false');

$value = 'test"str';
assert_decode_result($value, 'test"str', 'test"str', 'test"str');

$value = 'test {key:string}';
assert_decode_result($value, 'test {key:string}', 'test {key:string}', 'test {key:string}');

$value = 'test:';
assert_decode_result($value, 'test:', 'test:', 'test:');

$value = '123';
assert_decode_result($value, 123, 123, '123');

$value = '0123';
assert_decode_result($value, '0123', '0123', '0123');

$value = '00';
assert_decode_result($value, '00', '00', '00');

$value = '0.123';
assert_decode_result($value, 0.123, 0.123, '0.123');


$value = "\\\b\f\n\r\t\"";
assert_decode_result($value, "\\\b\f\n\r\t\"", "\\\b\f\n\r\t\"", "\\\b\f\n\r\t\"");

$value = '';
assert_decode_result($value, '', '', '');

$value = '127.0.0.1';
assert_decode_result($value, '127.0.0.1', '127.0.0.1', '127.0.0.1');

$value = '中文';
assert_decode_result($value, '中文', '中文', '中文');

echo '[success] string';
?>
--EXPECT--
[success] string