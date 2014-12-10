--TEST--
check encoding: array
--FILE--
<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * array
 */
$value = array();
assert_encode_result($value, '[]', '[]', '[]');

$value = array('');
assert_encode_result($value, '[""]', '[""]', '[""]');

$value = array('', '');
assert_encode_result($value, '[,]', '["",""]', '[,]');

$value = array('', '', '');
assert_encode_result($value, '[,,]', '["","",""]', '[,,]');

$value = array('', null, false);
assert_encode_result($value, '[,,]', '["",0,0]', '[,null,false]');

$value = array(null);
assert_encode_result($value, '[""]', '[0]', '[null]');

$value = array(0);
assert_encode_result($value, '[0]', '[0]', '[0]');

$value = array('', 1.0, 2, 'null test', 'null', null, '', 'new', '');
assert_encode_result($value,
	'[,1,2,null test,"null",,,new,]',
	'["",1,2,"null test","null",0,"","new",""]',
	'[,1.0,2,null test,"null",null,,new,]'
);

$value = array('', 1.0, 2, '',
	array('', 1.0, 2, 'null test', null, 'null', '', 'new', ''),
	'null test', null, 'null', '', 'new', ''
);
assert_encode_result($value,
	'[,1,2,,[,1,2,null test,,"null",,new,],null test,,"null",,new,]',
	'["",1,2,"",["",1,2,"null test",0,"null","","new",""],"null test",0,"null","","new",""]',
	'[,1.0,2,,[,1.0,2,null test,null,"null",,new,],null test,null,"null",,new,]'
);

echo '[success] array';
?>
--EXPECT--
[success] array