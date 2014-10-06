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

/**
 * jsonlite
 */
class JsonliteEncoder {
	/**
	 * @var mixed
	 */
	private $value;
	/**
	 * @var int
	 */
	private $code;
	/**
	 * @var string
	 */
	private $msg;
	/**
	 * @var string
	 */
	private $json;

	/**
	 * minimum
	 */
	const  TYPE_MIN = 1;
	/**
	 * work with js
	 */
	const  TYPE_JS = 2;
	/**
	 * strong type
	 */
	const  TYPE_STRICT = 3;

	const DEPTH_MAX = 512;
	private $depth;

	/**
	 * @param int
	 */
	private $type = self::TYPE_JS;

	function __construct($value, $type = self::TYPE_JS) {
		$this->value = $value;
		$this->json = null;
		$this->type = $type;
		$this->depth = 0;
	}

	private function  appendNull() {
		$this->json .= 'null';
	}

	private function  appendBool($value) {
		$this->json .= $value ? 'true' : 'false';
	}

	private function  appendInt($value) {
		$this->json .= $value;
	}

	private function  appendFloat($value) {
		$value = sprintf('%0.9g', $value);
		if ($this->type === self::TYPE_STRICT) {
			if (!is_float($value + 0)) {
				$value .= '.0';
			}
		}
		$this->json .= $value;
	}

	private function  appendArray($array, $depth) {
		$i = 0;
		$this->json .= '[';
		foreach ($array as $value) {
			if ($i !== 0) {
				$this->json .= ',';
			}

			if ($value === $array) {
				$this->mayRecursion();
				continue;
			}

			$depth++;
			$this->append($value, $depth);

			$i++;
		}

		if ($this->type !== self::TYPE_JS && $i === 1 && $value === '') {
			$this->json .= '""]';
		} else {
			$this->json .= ']';
		}
	}

	private function  appendMap($map, $depth) {
		$i = 0;
		$this->json .= '{';
		foreach ($map as $key => $value) {
			if ($i !== 0) {
				$this->json .= ',';
			}
			$this->appendString($key, true);
			$this->json .= ':';
			if ($value === $map) {
				$this->mayRecursion();
				continue;
			}

			$depth++;
			$this->append($value, $depth);
			$i++;
		}
		$this->json .= '}';
	}


	private function  isAssoc($array) {
		$i = 0;
		$isAssoc = false;
		foreach ($array as $key => $value) {
			if ($key !== $i) {
				$isAssoc = true;
			}
			$i++;
		}

		return $isAssoc;
	}

	private function isValueKeyword($value) {
		return strlen($value) < 6 && in_array(strtolower($value), array(
			'null', 'true', 'false'
		));
	}

	private function isKeyword($value) {
		/**
		 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Lexical_grammar#Keywords
		 */
		return strlen($value) < 11 && in_array(strtolower($value), array(
			'null', 'true', 'false', 'break', 'case',
			'class', 'catch', 'const', 'continue',
			'debugger', 'default', 'delete', 'do',
			'else', 'export', 'extends', 'finally',
			'for', 'function', 'if', 'import', 'in',
			'instanceof', 'let', 'new', 'return',
			'super', 'switch', 'this', 'throw', 'try',
			'typeof', 'var', 'void', 'while', 'with', 'yield'
		));
	}

	private function isKey($value) {
		return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $value);
	}

	private function  appendString($str, $isKey = false) {
		$isQuote = false;
		if (strpos($str, ',') !== false
			|| strpos($str, '[') !== false
			|| strpos($str, ']') !== false
			|| strpos($str, '{') !== false
			|| strpos($str, '}') !== false
			|| ($isKey && strpos($str, ':') !== false)
			|| $this->isValueKeyword($str) // true false null
			|| ($this->type === self::TYPE_JS && $this->isKeyword($str)) // function new with
			|| ($isKey && $this->type === self::TYPE_JS && !$this->isKey($str))
			|| (!$isKey && $this->type === self::TYPE_JS)
			|| (!$isKey && $this->type === self::TYPE_STRICT && is_numeric($str))
		) {
			$isQuote = true;
		}

		$literal = null;
		if ($isQuote) {
			$literal .= '"';
		}

		$char = null;
		$str = (string)$str;
		$len = strlen($str);

		for ($i = 0; $i < $len; $i++) {
			$char = $str[$i];
			switch ($char) {
				case '"':
					$literal .= '\"';
					break;
				case '\\':
					$literal .= '\\';
					break;
				case "\b":
					$literal .= '\b';
					break;
				case "\f":
					$literal .= '\f';
					break;
				case "\n":
					$literal .= '\n';
					break;
				case "\r":
					$literal .= '\r';
					break;
				case "\t":
					$literal .= '\t';
					break;
				default:
					$literal .= $char;
					break;
			}
		}

		if ($isQuote) {
			$literal .= '"';
		}
		$this->json .= $literal;
	}

	private function append($value, $depth) {
		if ($depth >= self::DEPTH_MAX) {
			$this->mayRecursion();

			return;
		}
		do {
			if (is_null($value)) {
				$this->appendNull();
				break;
			}

			if (is_bool($value)) {
				$this->appendBool($value);
				break;
			}

			if (is_int($value)) {
				$this->appendInt($value);
				break;
			}

			if (is_float($value)) {
				$this->appendFloat($value);
				break;
			}

			if (is_string($value)) {
				$this->appendString($value);
				break;
			}


			if (is_array($value)) {
				if ($this->isAssoc($value)) {
					$this->appendMap($value, $depth);
					break;
				}
				$this->appendArray($value, $depth);
				break;
			}

			if (is_object($value)) {
				$this->appendMap($value, $depth);
				break;
			}

		} while (false);
	}

	private function mayRecursion() {
		trigger_error('may.recursion', E_USER_WARNING);
	}

	public function encode() {
		$this->append($this->value, 0);

		return $this->json;
	}


}

class JsonliteDecoder {
	private $jsonlite;
	private $index;
	private $length;
	private $transactionIndex;
	private $trace;
	private $stack;

	function __construct($jsonlite) {
		$this->jsonlite = $jsonlite;
		$this->index = 0;
		$this->transactionIndex = 0;
		$this->length = strlen($jsonlite);
		$this->trace = array();
		$this->stack = array();
	}


	private function begin() {
		$this->transactionIndex = $this->index;
	}

	private function rollback() {
		$this->index = $this->transactionIndex;
	}

	private function trace($msg, $detail = null) {
		$trace = array($msg, $this->transactionIndex, $this->index);
		if ($detail) {
			$trace[] = $detail;
		}
		$this->trace[] = $trace;
	}

	public function getTrace($detail = false) {
		$traces = $this->trace;
		if ($detail) {

			foreach ($traces as $key => $trace) {
				$traces[$key] = array(
					'msg'   => $trace[0],
					'range' => array($trace[1], $trace[2]),
					'chars' => substr($this->jsonlite, $trace[1] - 1, $trace[2] + 1),
				);
				if (isset($trace[3])) {
					$traces[$key]['detail'] = $trace[3];
				}
			}
		}

		return $traces;
	}


	private function parseConst($const, $boundary) {
		$this->begin();
		$pass = true;
		$value = null;

		$haystack = strtolower($const) . strtoupper($const);

		for (; $this->index < $this->length; $this->index++) {
			$char = $this->jsonlite[$this->index];

			if (strpos($haystack, $char) !== false) {
				$value .= $char;
				continue;
			}

			if ((!$boundary || strpos($boundary, $char) === false) && end($this->stack) !== $char) {
				/**
				 * string start with null
				 */
				$pass = false;
				// trace string
			}
			$this->index--;
			break;
		}
		if ($pass) {
			$pass = strcasecmp($value, $const) === 0;
		}

		if (!$pass) {
			$this->rollback();
		}

		return $pass;
	}


	private function parseNumber($boundary) {
		$this->begin();
		$pass = false;
		$value = null;
		if ($this->jsonlite[$this->index] === '+') {
			$this->index++;
		} else if ($this->jsonlite[$this->index] == '-') {
			$this->index++;
			$value = '-';
		}

		for (; $this->index < $this->length; $this->index++) {
			$char = $this->jsonlite[$this->index];
			$byte = ord($char);

			if (ord('0') <= $byte && $byte <= ord('9') || $char === '.') {
				$value .= $char;
				$pass = true;
				continue;
			}


			if ((!$boundary || strpos($boundary, $char) === false) && end($this->stack) !== $char) {
				/**
				 * string start with number
				 */
				$pass = false;
				// trace string
			}
			$this->index--;
			break;
		}

		if (!$pass) {
			$this->rollback();
		}

		return array($pass, $value + 0);
	}


	private function parseString($boundary = null) {
		$this->begin();
		$pass = false;
		$value = null;
		$isQuote = false;
		$isEscape = false;
		$terminal = null;

		if ($this->jsonlite[$this->index] === '"') {
			$isQuote = true;
			$value = '';
			$this->index++;
		}

		for (; $this->index < $this->length; $this->index++) {
			$char = $this->jsonlite[$this->index];

			if (!$isQuote) {
				if ($boundary && strpos($boundary, $char) !== false) {
					$terminal = $char;
				} else if (end($this->stack) === $char) {
					$terminal = $char;
				}
			}

			if ($terminal) {
				$pass = true;

				if ((!$boundary || strpos($boundary, $char) === false) && end($this->stack) !== $char) {
					$pass = false;
					$this->trace('string.boundary');
				}

				$this->index--;
				break;
			}

			if ($isEscape) {
				$isEscape = false;
				switch ($char) {
					case '"':
						$value .= '"';
						break;
					case '\\':
						$value .= '\\';
						break;
					case 'b':
						$value .= "\b";
						break;
					case 'f':
						$value .= "\f";
						break;
					case 'n':
						$value .= "\n";
						break;
					case 'r':
						$value .= "\r";
						break;
					case 't':
						$value .= "\t";
						break;
					default:
						$value .= $char;
						break;
				}
				continue;
			}

			if ($char == '\\') {
				$isEscape = true;
				continue;
			}

			if ($isQuote && $char == '"') {
				$terminal = $char;
				continue;
			}
			$value .= $char;
		}

		if ($terminal && $char == '"') {
			$pass = true;
		}

		if (!$terminal && $this->index = $this->length) {
			$pass = true;
		}

		if (!$pass) {
			$this->rollback();
			$value = null;
		}

		return array($pass, $value);
	}


	private function parseList() {
		$this->begin();
		$pass = true;
		$value = array();
		$sep = '[';
		$this->stack[] = ']';
		$this->index++;
		$item = null;
		$char = null;

		for (; $this->index < $this->length; $this->index++) {
			$char = $this->jsonlite[$this->index];

			if ($sep && $char === ',') {
				$value[] = '';
				$sep = null;
				continue;
			}

			if ($char == ',') {
				$sep = ',';
				continue;
			}

			if ($sep == ',' && $char == ']') {
				$value[] = '';
				$sep = null;
			}

			if ($char == ']') {
				break;
			}
			$sep = false;
			list($pass, $item) = $this->parse(',');
			if ($pass) {
				$value[] = $item;
			}
		}

		$pass = $pass && $char === ']';

		if (!$pass) {
			$this->rollback();
			$value = null;
		} else {
			if (end($this->stack) === ']') {
				array_pop($this->stack);
			}
		}

		return array($pass, $value);
	}

	private function parseMap() {
		$this->begin();
		$pass = true;
		$value = array();
		$sep = '{';
		$this->stack[] = '}';
		$this->index++;
		$isKey = true; // key true, value false
		$key = null;
		$item = null;
		$needBreak = false;
		$char = null;

		for (; $this->index < $this->length; $this->index++) {
			$char = $this->jsonlite[$this->index];

			if ($char === ':') {
				$isKey = false;
				if ($sep) {
					$sep = ':';
					$key = '';
				} else {
					$sep = ':';
				}
				continue;
			}

			if ($char === ',') {
				if ($sep === ':') {
					$sep = ',';
					$item = '';
				} else {
					$sep = ',';
					continue;
				}
			}

			if ($char === '}') {
				if ($sep === ':') {
					$item = '';
					$needBreak = true;
				} else {
					break;
				}
			}

			$sep = false;

			if ($isKey) {
				list($pass, $key) = $this->parseString(':,');

				if (!$pass) {
					break;
				}
			} else {
				$isKey = true;
				if ($item === null) {
					list($pass, $item) = $this->parse(',:');

					if (!$pass) {
						break;
					}
				}
				$value[$key] = $item;
				$item = null;
			}

			if ($needBreak) {
				break;
			}
		}
		if ($pass && $char !== '}') {
			$pass = false;
			$this->trace('map.terminal');
		} else {
			if (end($this->stack) === '}') {
				array_pop($this->stack);
			}
		}

		if (!$pass) {
			$this->rollback();
			$value = null;
		}

		return array($pass, $value);
	}

	private function parse($boundary = null) {
		$pass = false;
		$value = null;

		if (is_string($this->jsonlite) && $this->length == 0) {
			$pass = true;
			$value = '';
		} else {


			$char = $this->jsonlite[$this->index];


			switch ($char) {
				case '"':
					list($pass, $value) = $this->parseString($boundary);
					break;
				case '[':
					list($pass, $value) = $this->parseList();
					break;
				case '{':
					list($pass, $value) = $this->parseMap();
					break;
				case 'n':
				case 'N':
					$pass = $this->parseConst('null', $boundary);
					if ($pass) {
						$value = null;
					} else {
						list($pass, $value) = $this->parseString($boundary);

					}
					break;
				case 't':
				case 'T':
					$pass = $this->parseConst('true', $boundary);
					if ($pass) {
						$value = true;
					} else {
						list($pass, $value) = $this->parseString($boundary);
					}
					break;
				case 'f':
				case 'F':
					$pass = $this->parseConst('false', $boundary);
					if ($pass) {
						$value = false;
					} else {
						list($pass, $value) = $this->parseString($boundary);
					}
					break;
				case '0':
				case '1':
				case '2':
				case '3':
				case '4':
				case '5':
				case '6':
				case '7':
				case '8':
				case '9':
				case '-':
				case '+':
				case '.':
					list($pass, $value) = $this->parseNumber($boundary);
					if (!$pass) {
						list($pass, $value) = $this->parseString($boundary);
					}
					break;
				case '}':
				case ']':
				case ',':
				case ':':
					$pass = false;
					$this->trace('parse.char');
					break;
				default:
					list($pass, $value) = $this->parseString($boundary);
			}
		}


		return array($pass, $value);
	}

	public function decode() {
		list($pass, $value) = $this->parse();
		if ($this->stack) {
			$pass = false;
			$this->trace('brackets.match', join(',', $this->stack));
		}

		return $pass ? $value : null;
	}
}

define('JSONLITE_TRACE_G', '__jsonlite_trace_g');
define('JSONLITE_TYPE_MIN', JsonliteEncoder::TYPE_MIN);
define('JSONLITE_TYPE_JS', JsonliteEncoder::TYPE_JS);
define('JSONLITE_TYPE_STRICT', JsonliteEncoder::TYPE_STRICT);
/**
 * get the jsonlite of a value
 *
 * @param mixed $value
 * @param int $type
 * @return string
 */
function jsonlite_encode($value, $type = JSONLITE_TYPE_JS) {
	$encoder = new JsonliteEncoder($value, $type);

	return $encoder->encode();
}

/**
 * decode a jsonlite string
 *
 * @param string $jsonlite
 * @return mixed
 */
function jsonlite_decode($jsonlite, $trace = false) {
	unset($GLOBALS[JSONLITE_TRACE_G]);
	$decoder = new JsonliteDecoder($jsonlite);
	if ($trace) {
		$GLOBALS[JSONLITE_TRACE_G] = $decoder;
	}

	return $decoder->decode();
}

function jsonlite_get_trace($detail = false) {
	/**
	 * @var $decoder JsonliteDecoder
	 */
	$decoder = $GLOBALS[JSONLITE_TRACE_G];
	$trace = array();
	if ($decoder) {
		$trace = $decoder->getTrace($detail);
	}

	return $trace;
}

