<?php

/*
  +----------------------------------------------------------------------+
  | jsonlite                                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 2014 moxie(system128@gmail.com)                        |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
 */
error_reporting(E_ALL);
ini_set('display_errors', 'on');
require_once 'jsonlite.php';


/**
 * encode
 */
echo PHP_EOL, 'encode:', PHP_EOL;

/**
 * null
 */
$value = null;
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === "null", 'incorrect:null');

/**
 * boolean
 */
$value = true;
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === "true", 'incorrect:true');
$value = false;
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === "false", 'incorrect:true');

/**
 * number
 */
$value = 1;
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === "$value", 'incorrect:int');

$value = 1.000;
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === '1', 'incorrect:double');

$value = 1.000;
$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_STRICT);
assert($encoder->encode() === '1.0', 'incorrect:double');


$value = 1.010;
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === '1.01', 'incorrect:double');


/**
 * string
 */
$value = 'test str';
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === "\"$value\"", 'incorrect_js:string contains quote');

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
assert($encoder->encode() === "$value", 'incorrect:string contains quote');

$value = 'false';
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === "\"$value\"", 'incorrect:keyword string');

$value = 'test"str';
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === '"test\\"str"', 'incorrect_js:string contains quote');

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
assert($encoder->encode() === 'test\\"str', 'incorrect:string contains quote');

$value = 'test {key:string}';
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === '"test {key:string}"', 'incorrect:complex string');

$value = '123';
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === '"123"', 'incorrect:numeric string');


$value = '0123';
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === '"0123"', 'incorrect:numeric string');

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
assert($encoder->encode() === '0123', 'incorrect:numeric string');


$value = '';
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === '""', 'incorrect_js:empty string');

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_STRICT);
assert($encoder->encode() === '', 'incorrect_strict:empty string');

$value = '';
$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
assert($encoder->encode() === '', 'incorrect_min:empty string');


$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
assert($encoder->encode() === "", 'incorrect:empty string');


function print_result($name, $result, $json) {
	$result_len = strlen($result);
	$json_len = strlen($json);
//	echo sprintf('% 12s: %s', 'json', $json), PHP_EOL;
//	echo sprintf('% 12s: %s', $name, $result), PHP_EOL;
	echo sprintf('% 12s: %3d %3d %3d % 5.2f%%', $name, $json_len, $result_len, $result_len - $json_len, ($json_len - $result_len) / $json_len * 100), PHP_EOL;
}

/**
 * array
 */
$value = array();
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === "[]", 'incorrect:empty array');

$value = array('');
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === '[""]', 'incorrect_encode_js:only empty string in array');

$value = array('');
$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_STRICT);
assert($encoder->encode() === '[""]', 'incorrect_encode_strict:only empty string in array');

$value = array('');
$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
assert($encoder->encode() === '[""]', 'incorrect_encode_min:only empty string in array');


$value = array('', 1.0, 2, 'null test', null, 'null', '', 'new', '');

$json = json_encode($value);

$encoder = new JsonliteEncoder($value);
$result = $encoder->encode();

assert($result === '["",1,2,"null test",null,"null","","new",""]', 'incorrect_encode_js: multi type array');

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_STRICT);
$result = $encoder->encode();
assert($result === '[,1.0,2,null test,null,"null",,new,]', 'incorrect_encode_strict: multi type array');

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
$result = $encoder->encode();

assert($result === '[,1,2,null test,null,"null",,new,]', 'incorrect_encode_min:multi type array');


$value = array('', 1.0, 2, '', array('', 1.0, 2, 'null test', null, 'null', '', 'new', ''), 'null test', null, 'null', '', 'new', '');
$json = json_encode($value);

$encoder = new JsonliteEncoder($value);
$result = $encoder->encode();

assert($result === '["",1,2,"",["",1,2,"null test",null,"null","","new",""],"null test",null,"null","","new",""]', 'incorrect_encode_js: complex array');
print_result('array_js', $result, $json);

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_STRICT);
$result = $encoder->encode();


assert($result === '[,1.0,2,,[,1.0,2,null test,null,"null",,new,],null test,null,"null",,new,]', 'incorrect_encode_strict: complex array');
print_result('array_strict', $result, $json);

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
$result = $encoder->encode();

assert($result === '[,1,2,,[,1,2,null test,null,"null",,new,],null test,null,"null",,new,]', 'incorrect_encode_min:complex array');
print_result('array_min', $result, $json);


/**
 * object
 */
$value = new stdClass();
$encoder = new JsonliteEncoder($value);
assert($encoder->encode() === "{}", 'incorrect:empty object');

$value = new stdClass();
$value->name = 'test';
$encoder = new JsonliteEncoder($value);
$result = $encoder->encode();
assert($result === '{name:"test"}', 'incorrect_js:simple object');

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
$result = $encoder->encode();
assert($result === "{name:test}", 'incorrect:simple object');


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
	1      => null,
	''     => '',
	'n'    => 1,
	'sn'   => '2',
);
$json = json_encode($value);
$encoder = new JsonliteEncoder($value);
$result = $encoder->encode();
assert($result === '{k:"v v",k1:{k1:"ka"},k2:{k:"v v","k:":"v}","null":"v{","new":"false","1":null,"":"",n:1,sn:"2"}}', 'incorrect_encode_js:complex object');
print_result('map_js', $result, $json);

$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_STRICT);
$result = $encoder->encode();
assert($result === '{k:v v,k1:{k1:ka},k2:{k:v v,"k:":"v}","null":"v{",new:"false",1:null,:,n:1,sn:"2"}}', 'incorrect:strict complex object');
print_result('map_strict', $result, $json);
$encoder = new JsonliteEncoder($value, JsonliteEncoder::TYPE_MIN);
$result = $encoder->encode();
assert($result === '{k:v v,k1:{k1:ka},k2:{k:v v,"k:":"v}","null":"v{",new:"false",1:null,:,n:1,sn:2}}', 'incorrect:min complex object');
print_result('map_min', $result, $json);
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
assert($error_count == 1, 'warning.trigger.fail');

unset($value);
$value = array('key' => &$value);
$encoder = new JsonliteEncoder($value);
$result = $encoder->encode();
assert($error_count == 2, 'warning.trigger.fail');
restore_error_handler();


/**
 * decode
 */
echo PHP_EOL, 'decode:', PHP_EOL;

/**
 * null
 */
$value = null;
$jsonlite = jsonlite_encode($value);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_js: null');
/**
 * true
 */
$value = true;
$jsonlite = jsonlite_encode($value);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result == $value, 'incorrect_decode_js: true');

/**
 * false
 */
$value = false;
$jsonlite = jsonlite_encode($value);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_js: false ');
/**
 * number
 */
$value = 1.0;
$jsonlite = jsonlite_encode($value);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_js: double');

$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_strict: double ');

$value = 2;
$jsonlite = jsonlite_encode($value);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_js: int');
/**
 * number
 */

$value = 'test str';
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_strict: simple string');


$value = 'false';
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_strict: keyword string');

$value = 'test"str';
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict: string contain quote ');

$value = 'test {key:string}';
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:complex string');

$value = '123';
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:numeric string');

$value = '0123';
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:numeric string');

$value = '';
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:empty string');

$value = 'null test';
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:string start with keyword');


$value = array();
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_strict:empty array');

$value = array('', 1.0, 2, 'null test', null, '', 'new', '');

$jsonlite = jsonlite_encode($value);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:multi type array');


$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_strict:multi type array');

$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_MIN);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_min:multi type array');


$value = array();
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_JS);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_js:empty array');

$value = array('');
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_JS);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_js: only empty string in array');


$value = array();
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_strict:empty array');

$value = array('');
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === $value, 'incorrect_decode_strict: only empty string in array');


$value = array();
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_MIN);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_min:empty array');

$value = array('');
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_MIN);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result === $value, 'incorrect_decode_min: only empty string in array');


date_default_timezone_set('asia/shanghai');
$value = array('date:' => date('Y-m-d H:i:s'));
$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_MIN);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:complex array');


$value = array('', 1.0, 2, '', array('', 1.0, 2, 'null test', null, 'null', '', 'new', ''), 'null test', null, 'null', '', 'new', '');
$jsonlite = jsonlite_encode($value);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:complex array');

$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_strict:complex array');


$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_MIN);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == $value, 'incorrect_decode_min:complex array');


$value = new stdClass();
$value->k = 'v v';

$value_sub = new stdClass();
$value_sub = array(
	's1' => 'ka',
	's2' => ''
);
$value->k1 = $value_sub;
$value->k2 = array(
	'k'       => 'v v',
	'k:'      => 'v}',
	'null'    => 'v{',
	'new'     => 'false',
	'new str' => 'false str',
	'no str'  => '1 str',
	1         => null,
	''        => '',
	'n'       => 1,
	'sn'      => '2',
	'zh-cn'   => '中文',
	'0pre'    => '0123',
	'0sub'    => '1230',
);

$jsonlite = jsonlite_encode($value);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == (array)$value, 'incorrect_decode_js:complex map');

$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_STRICT);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == (array)$value, 'incorrect_decode_strict:complex map');


$jsonlite = jsonlite_encode($value, JSONLITE_TYPE_MIN);
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == (array)$value, 'incorrect_decode_min:complex map');


$jsonlite = '{:}';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
$traces = $decoder->getTrace(true);
assert(empty($traces), __LINE__);
assert($result == array(
		'' => '',
	), 'incorrect_decode_example:map example');


/**
 * exception
 */

echo PHP_EOL, 'exception:', PHP_EOL;

$jsonlite = '{k:v]';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null, __LINE__);
$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'   => 'map.terminal',
			'range' =>
				array(3, 6),
			'chars' => ':v]'
		),
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(3, 3),
			'chars'  => ':v]',
			'detail' => '}'
		)
	), var_export($traces, true));


$jsonlite = '[true';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null, __LINE__);
$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(1, 1),
			'chars'  => '[t',
			'detail' => ']'
		)
	), var_export($traces, true));

$jsonlite = ']';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null, __LINE__);
$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'   => 'parse.char',
			'range' =>
				array(0, 0),
			'chars' => ']'
		)
	), var_export($traces, true));

$jsonlite = '[,';
$decoder = new JsonliteDecoder($jsonlite);
$result = $decoder->decode();
assert($result === null, __LINE__);
$traces = $decoder->getTrace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(0, 0),
			'chars'  => ',',
			'detail' => ']'
		)
	), var_export($traces, true));


$jsonlite = '[,';

$result = jsonlite_decode($jsonlite, true);
assert($result === null, __LINE__);
$traces = jsonlite_get_trace(true);
assert($traces === array(
		array(
			'msg'    => 'brackets.match',
			'range'  =>
				array(0, 0),
			'chars'  => ',',
			'detail' => ']'
		)
	), var_export($traces, true));

echo PHP_EOL, 'done', PHP_EOL;

