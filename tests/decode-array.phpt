--TEST--
check decoding: decoding
--FILE--
<?php
require_once dirname(__FILE__) . '/helper.php';
/**
 * string
 */
$value = array();
assert_decode_result($value, array(), array(), array());

$value = array('');
assert_decode_result($value, array(''), array(''), array(''));

$value = array('', '');
assert_decode_result($value, array('', ''), array('', ''), array('', ''));

$value = array('', '', '');
assert_decode_result($value, array('', '', ''), array('', '', ''), array('', '', ''));

$value = array('', null, false);
assert_decode_result($value, array('', '', ''), array('', 0, 0), array('', null, false));

$value = array('', 1.0, 2, 'null test', null, '', 'new', '');
assert_decode_result($value,
	array('', 1, 2, 'null test', '', '', 'new', ''),
	array('', 1, 2, 'null test', 0, '', 'new', ''),
	array('', 1.0, 2, 'null test', null, '', 'new', '')
);

$value = array('', 1.0, 2, '',
	array('', 1.0, 2, 'null test', null, 'null', '', 'new', ''),
	'null test', null, 'null', '', 'new', '');
assert_decode_result($value,
	array('', 1, 2, '',
		array('', 1, 2, 'null test', '', 'null', '', 'new', ''),
		'null test', '', 'null', '', 'new', ''),

	array('', 1, 2, '',
		array('', 1, 2, 'null test', 0, 'null', '', 'new', ''),
		'null test', 0, 'null', '', 'new', ''),

	array('', 1.0, 2, '',
		array('', 1.0, 2, 'null test', null, 'null', '', 'new', ''),
		'null test', null, 'null', '', 'new', '')
);

echo '[success] array';
?>
--EXPECT--
[success] array