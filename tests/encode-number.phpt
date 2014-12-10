--TEST--
check encoding: number
--FILE--
<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * number
 */
$value = 1;
assert_encode_result($value, '1', '1', '1');

$value = 1.000;
assert_encode_result($value, '1', '1', '1.0');

$value = 1.010;
assert_encode_result($value, '1.01', '1.01', '1.01');

$value = 0.010;
assert_encode_result($value, '0.01', '0.01', '0.01');

echo '[success] number';
?>
--EXPECT--
[success] number