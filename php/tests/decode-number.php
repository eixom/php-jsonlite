<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * number
 */

$value = 1;
assert_decode_result($value, 1, 1, 1);

$value = 011;
assert_decode_result($value, 9, 9, 9);

$value = 0x11;
assert_decode_result($value, 17, 17, 17);

$value = 1.000;
assert_decode_result($value, 1, 1, 1.0);

$value = 1.010;
assert_decode_result($value, 1.01, 1.01, 1.01);


$value = 0.010;
assert_decode_result($value, 0.01, 0.01, 0.01);


echo '[success] number';
