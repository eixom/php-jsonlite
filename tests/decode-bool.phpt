--TEST--
check decoding: bool
--FILE--
<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * bool
 */
$value = true;
assert_decode_result($value, 1, 1, true);

$value = false;
assert_decode_result($value, '', 0, false);

echo '[success] bool';
?>
--EXPECT--
[success] bool