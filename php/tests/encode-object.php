<?php
require_once dirname(__FILE__) . '/helper.php';

/**
 * array
 */
$value = array();
assert_encode_result($value, '[]', '[]', '[]');

$value = array();
assert_encode_result($value, '{}', '{}', '{}', 1);

$value = new stdClass();
assert_encode_result($value, '{}', '{}', '{}');


$value = array(
	'n j' => 'test'
);
assert_encode_result($value,
	'{n j:test}',
	'{"n j":"test"}',
	'{n j:test}'
);


$value = array(
	'new' => 'false'
);
assert_encode_result($value,
	'{new:"false"}',
	'{"new":"false"}',
	'{new:"false"}'
);


$value = new stdClass();
$value->name = 'test';
assert_encode_result($value,
	'{name:test}',
	'{name:"test"}',
	'{name:test}'
);


$value = array(
	1  => '',
	'' => ''
);
assert_encode_result($value,
	'{1:,:}',
	'{1:"","":""}',
	'{1:,:}'
);

$value = new stdClass();
$value->k = 'v v';

$value_sub = new stdClass();
$value_sub = array(
	'k1' => 'ka'
);
$value->k1 = $value_sub;
$value->k2 = array(
	'k'    => 'v v',
	'k:'   => 'v}',
	'null' => 'v{',
	'new'  => 'false',
	'ip'   => '127.0.0.1',
	1      => null,
	''     => '',
	'n'    => 1,
	'sn'   => '2',
	'sn2'  => '02',
	'zh'   => '中文',
);


assert_encode_result($value,
	'{k:v v,k1:{k1:ka},k2:{k:v v,"k:":"v}","null":"v{",new:"false",ip:127.0.0.1,1:,:,n:1,sn:2,sn2:02,zh:中文}}',
	'{k:"v v",k1:{k1:"ka"},k2:{k:"v v","k:":"v}","null":"v{","new":"false",ip:"127.0.0.1",1:0,"":"",n:1,sn:2,sn2:"02",zh:"中文"}}',
	'{k:v v,k1:{k1:ka},k2:{k:v v,"k:":"v}","null":"v{",new:"false",ip:127.0.0.1,1:null,:,n:1,sn:"2",sn2:02,zh:中文}}'
);

echo '[success] object';
