<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * null
 */
$value = null;
assert_encode_result($value, '', '0', 'null');

echo '[success] null';
