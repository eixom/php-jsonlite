--TEST--
check encoding: bool
--FILE--
<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * boolean
 */
$value = true;
assert_encode_result($value, '1', '1', 'true');

$value = false;
assert_encode_result($value, '', '0', 'false');


echo '[success] bool';
?>
--EXPECT--
[success] bool