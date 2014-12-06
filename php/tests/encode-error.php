<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * error
 */

$error_count = 0;
function __error_handler($code, $msg, $file, $line) {
	assert($msg === 'may.recursion', 'unknow.error.msg');
	global $error_count;
	$error_count++;
}

set_error_handler('__error_handler');
$value = array('key' => array(&$value));
$encoder = new JsonliteEncoder($value);
$result = $encoder->encode();
assert($error_count == 1);

unset($value);
$value = array('key' => &$value);
$encoder = new JsonliteEncoder($value);
$result = $encoder->encode();
assert($error_count == 2);
restore_error_handler();


echo '[success] error';
