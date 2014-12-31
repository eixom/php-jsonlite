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

require_once dirname(__FILE__) . '/../jsonlite.php';

function show_encode_error($msg, $expected, $actual) {
	$info = debug_backtrace();
	$info = end($info);

	printf("[FAIL]%s:%d %s \nexpected %s %s\n  actual %s %s\n\n",
		basename($info['file']), $info['line'], $msg,
		gettype($expected), $expected,
		gettype($actual), $actual
	);
}


function show_decode_error($msg, $expected, $actual) {
	$info = debug_backtrace();
	$info = end($info);

	printf("[FAIL]%s:%d %s \nexpected %s %s\n  actual %s %s\n\n",
		basename($info['file']), $info['line'], $msg,
		gettype($expected), var_export($expected, 1),
		gettype($actual), var_export($actual, 1)
	);
}

function assert_encode_result($value, $expected_min, $expected_js, $expected_strict, $cast = false) {
	$actual = jsonlite_encode($value, JSONLITE_MODE_MIN, $cast);
	if ($actual !== $expected_min) {
		show_encode_error('min', $expected_min, $actual);
	}
	$actual = jsonlite_encode($value, JSONLITE_MODE_JS, $cast);
	if ($actual !== $expected_js) {
		show_encode_error('js', $expected_js, $actual);
	}
	$actual = jsonlite_encode($value, JSONLITE_MODE_STRICT, $cast);
	if ($actual !== $expected_strict) {
		show_encode_error('strict', $expected_js, $actual);
	}
}

function assert_decode_result($value, $expected_min, $expected_js, $expected_strict) {
	/**
	 * min
	 */
	$jsonlite = jsonlite_encode($value, JSONLITE_MODE_MIN);
	$decoder = new JSONLiteDecoder($jsonlite);
	$result = $decoder->decode();
	$traces = $decoder->getTrace(true);

	if ($result !== $expected_min) {
		show_decode_error('min', $expected_min, $result);
	}
	if ($traces !== array()) {
		show_decode_error('min_trace', array(), $traces);
	}
	/**
	 * js
	 */
	$jsonlite = jsonlite_encode($value, JSONLITE_MODE_JS);
	$decoder = new JSONLiteDecoder($jsonlite);
	$result = $decoder->decode();
	$traces = $decoder->getTrace(true);

	if ($result !== $expected_js) {
		show_decode_error('js', $expected_js, $result);
	}
	if ($traces !== array()) {
		show_decode_error('js_trace', array(), $traces);
	}

	/**
	 * strict
	 */
	$jsonlite = jsonlite_encode($value, JSONLITE_MODE_STRICT);
	$decoder = new JSONLiteDecoder($jsonlite);
	$result = $decoder->decode();
	$traces = $decoder->getTrace(true);

	if ($result !== $expected_strict) {
		show_decode_error('strict', $expected_strict, $result);
	}

	if ($traces !== array()) {
		show_decode_error('strict_trace', array(), $traces);
	}


}
