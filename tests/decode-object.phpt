--TEST--
check decoding: object
--FILE--
<?php
require_once dirname(__FILE__) . '/helper.php';


/**
 * object
 */

$value = array('date:' => '2014-12-07 01:01:20');
assert_decode_result($value,
	array('date:' => '2014-12-07 01:01:20'),
	array('date:' => '2014-12-07 01:01:20'),
	array('date:' => '2014-12-07 01:01:20')
);

$value = new stdClass();
assert_decode_result($value,
	array(),
	array(),
	array()
);


$value = array(
	'n j' => 'test'
);

assert_decode_result($value,
	array(
		'n j' => 'test'
	),
	array(
		'n j' => 'test'
	),
	array(
		'n j' => 'test'
	)
);


$value = array(
	'new' => 'false'
);
assert_decode_result($value,
	array(
		'new' => 'false'
	),
	array(
		'new' => 'false'
	),
	array(
		'new' => 'false'
	)
);


$value = array(
	'new' => false
);
assert_decode_result($value,
	array(
		'new' => ''
	),
	array(
		'new' => 0
	),
	array(
		'new' => false
	)
);


$value = array(
	'' => null
);
assert_decode_result($value,
	array(
		'' => ''
	),
	array(
		'' => 0
	),
	array(
		'' => null
	)
);

$value = array(
	1  => '',
	'' => ''
);
assert_decode_result($value,
	array(
		'1' => '',
		''  => '',
	),
	array(
		'1' => '',
		''  => '',
	),
	array(
		'1' => '',
		''  => '',
	)
);

$value = array(
	'' => '',
	1  => ''
);
assert_decode_result($value,
	array(

		''  => '',
		'1' => '',
	),
	array(
		''  => '',
		'1' => '',
	),
	array(
		''  => '',
		'1' => '',
	)
);


$value = array(
	''   => '',
	null => '1' // WARNING: min/js type, NULL key is duplicate with ''
);
assert_decode_result($value,
	array(
		'' => 1,
	),
	array(
		'' => 1,
	),
	array(
		''   => '',
		null => '1',
	)
);

$value = new stdClass();
$value->name = 'test';
assert_decode_result($value,
	array(
		'name' => 'test'
	),
	array(
		'name' => 'test'
	),
	array(
		'name' => 'test'
	)
);

$value = array(
	1  => '',
	'' => ''
);
assert_decode_result($value,
	array(
		'1' => '',
		''  => '',
	),
	array(
		'1' => '',
		''  => '',
	),
	array(
		'1' => '',
		''  => '',
	)
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

assert_decode_result($value,
	array(
		'k'  => 'v v',
		'k1' =>
			array(
				'k1' => 'ka',
			),
		'k2' =>
			array(
				'k'    => 'v v',
				'k:'   => 'v}',
				'null' => 'v{',
				'new'  => 'false',
				'ip'   => '127.0.0.1',
				1      => '',
				''     => '',
				'n'    => 1,
				'sn'   => 2,
				'sn2'  => '02',
				'zh'   => '中文',
			),
	),
	array(
		'k'  => 'v v',
		'k1' =>
			array(
				'k1' => 'ka',
			),
		'k2' =>
			array(
				'k'    => 'v v',
				'k:'   => 'v}',
				'null' => 'v{',
				'new'  => 'false',
				'ip'   => '127.0.0.1',
				1      => 0,
				''     => '',
				'n'    => 1,
				'sn'   => 2,
				'sn2'  => '02',
				'zh'   => '中文',
			),
	),
	array(
		'k'  => 'v v',
		'k1' =>
			array(
				'k1' => 'ka',
			),
		'k2' =>
			array(
				'k'    => 'v v',
				'k:'   => 'v}',
				'null' => 'v{',
				'new'  => 'false',
				'ip'   => '127.0.0.1',
				1      => NULL,
				''     => '',
				'n'    => 1,
				'sn'   => '2',
				'sn2'  => '02',
				'zh'   => '中文',
			),
	)
);

echo '[success] object';
?>
--EXPECT--
[success] object