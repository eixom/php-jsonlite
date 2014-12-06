<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * null
 */
$value = null;
assert_decode_result($value, '', 0, null);

echo '[success] null';
