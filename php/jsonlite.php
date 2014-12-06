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
 * just like json but more lighter
 */
class JSONLiteEncoder {
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
	/**
	 * @var int
	 */
	private $depth;

	/**
	 * @param int
	 */
	private $type;
	/**
	 * @var bool
	 */
	private $castAsMap;

	/**
	 *
	 * @param mixed $value data
	 * @param int $type work with log/js/strong type
	 */
	function __construct($value, $type = self::TYPE_JS) {
		$this->value = $value;
		$this->json = null;
		$this->type = $type;
		$this->depth = 0;
		$this->castAsMap = false;
	}

	/**
	 * @param boolean $castAsMap
	 */
	public function setCastAsMap($cast = true) {
		$this->castAsMap = $cast;
	}

	/**
	 * encode null
	 */
	private function  appendNull() {
		switch ($this->type) {
			case self::TYPE_MIN:
				$this->json .= '';
				break;
			case self::TYPE_JS:
				$this->json .= '0';
				break;
			case self::TYPE_STRICT:
			default:
				$this->json .= 'null';
		}
	}

	/**
	 * encode bool
	 *
	 * @param $value
	 */
	private function  appendBool($value) {

		switch ($this->type) {
			case self::TYPE_MIN:
				$this->json .= $value ? '1' : '';
				break;
			case self::TYPE_JS:
				$this->json .= $value ? '1' : '0';
				break;
			case self::TYPE_STRICT:
			default:
				$this->json .= $value ? 'true' : 'false';
		}

	}

	/**
	 * encode int
	 *
	 * @param $value
	 */
	private function  appendInt($value) {
		$this->json .= $value;
	}

	/**
	 * encode float
	 *
	 * @param $value
	 */
	private function  appendFloat($value) {
		$value = sprintf('%0.9g', $value);
		if ($this->type === self::TYPE_STRICT) {
			if (!is_float($value + 0)) {
				$value .= '.0';
			}
		}
		$this->json .= $value;
	}

	/**
	 * encode array/map
	 *
	 * @param $array
	 * @param $depth
	 */
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

		if ($i === 1) {
			if ($this->type !== self::TYPE_JS && $value === '') {
				$this->json .= '""';
			}

			if ($this->type === self::TYPE_MIN && $value === null) {
				$this->json .= '""';
			}
		}

		$this->json .= ']';

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

	/**
	 * is map or array
	 *
	 * @param $array
	 * @return bool
	 */
	private function  isAssoc($array) {
		$i = 0;
		$isAssoc = false;
		foreach ($array as $key => $value) {
			if ($key !== $i) {
				$isAssoc = true;
			}
			$i++;
		}
		if ($i == 0) {
			$isAssoc = $this->castAsMap;
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

	private function isKey($str) {
		$isKey = 0;
		$len = strlen($str);
		do {
			if ($len < 1) {
				break;
			}
			$isKey = 1;

			for ($i = 0; $i < $len; $i++) {
				$char = $str[$i];

				if ($i == 0) {
					if (!ctype_alpha($char) && $char !== '_') {
						$isKey = 0;
						break;
					}
					continue;
				}
				if (!ctype_alnum($char) && $char !== '_') {
					$isKey = 0;
					break;
				}
			}

		} while (0);

		return $isKey;
	}

	/**
	 * finds whether or not to quote
	 *
	 * @param string $str string data
	 * @param bool $isMapKey is map key
	 * @return bool
	 */
	private function isQuote($str, $isMapKey = false) {
		$isQuote = false;
		do {
			/**
			 * special character
			 */
			if (strpbrk($str, ',[]{}') !== false) {
				$isQuote = true;
				break;
			}
			/**
			 * array('key:' => value);
			 */
			if ($isMapKey && strpos($str, ':') !== false) {
				$isQuote = true;
				break;
			}
			/**
			 * 'value' keyword
			 * true false null
			 */
			if ($this->isValueKeyword($str)) {
				$isQuote = true;
				break;
			}

			if ($this->type === self::TYPE_JS) {
				if ($this->isKeyword($str)) {
					$isQuote = true;
					break;
				}

				if ($isMapKey) {
					if ($str === '') {
						$isQuote = true;
						break;
					}

					if (strpos($str, ':') !== false) {
						$isQuote = true;
						break;
					}

					if (!$this->isKey($str) && !is_numeric($str)) {
						$isQuote = true;
						break;
					}
				} else {
					/**
					 * 0 0
					 * 0.1 0.1
					 * 01 "01"
					 * 00 "00"
					 */
					if (!is_numeric($str)) {
						$isQuote = true;
						break;
					}

					if (strpos($str, '.') === false && $str[0] === '0') {
						$isQuote = true;
						break;
					}
				}

			}

			if ($this->type === self::TYPE_STRICT) {
				if (!$isMapKey) {
					/**
					 * 0 "0"
					 * 0.1 "0.1"
					 * 01 01
					 * 00 00
					 */
					if (is_numeric($str)) {
						$isQuote = true;

						if (strpos($str, '.') === false) {
							if ($str[0] === '0') {
								$isQuote = false;
							}
						}
						break;
					}
				}

			}

		} while (false);

		return $isQuote;
	}

	/**
	 * encode string
	 *
	 * @param string $str
	 * @param bool $isMapKey is map key
	 */
	private function  appendString($str, $isMapKey = false) {
		$isQuote = $this->isQuote($str, $isMapKey);

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

	/**
	 * encode mixed data
	 *
	 * @param $value
	 * @param $depth
	 */
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

	/**
	 * trigger an 'recursion' error
	 */
	private function mayRecursion() {
		trigger_error('may.recursion', E_USER_WARNING);
	}

	/**
	 * encode data
	 * @return string/null jsonlite,return NULL on error
	 */
	public function encode() {
		$this->append($this->value, 0);

		return $this->json;
	}


}

/**
 * decode a JSONLite String
 */
class JSONLiteDecoder {
	/**
	 * @var string jsonlite string
	 */
	private $jsonlite;
	/**
	 * @var int offset
	 */
	private $index;
	/**
	 * @var int jsonlite string length
	 */
	private $length;
	/**
	 * @var int transaction start index
	 */
	private $transactionIndex;
	/**
	 * @var array error trace
	 */
	private $trace;
	/**
	 * @var array brackets stack
	 */
	private $stack;

	/**
	 * @param string $jsonlite jsonlite string
	 */
	function __construct($jsonlite) {
		$this->jsonlite = $jsonlite;
		$this->index = 0;
		$this->transactionIndex = 0;
		$this->length = strlen($jsonlite);
		$this->trace = array();
		$this->stack = array();
	}

	/**
	 * start a parse transaction
	 */
	private function begin() {
		$this->transactionIndex = $this->index;
	}

	/**
	 * rollback a parse transaction
	 */
	private function rollback() {
		$this->index = $this->transactionIndex;
	}

	/**
	 * log an error
	 *
	 * @param $msg error message
	 * @param string $detail error detail
	 */
	private function trace($msg, $detail = null) {
		$trace = array($msg, $this->transactionIndex, $this->index);
		if ($detail) {
			$trace[] = $detail;
		}
		$this->trace[] = $trace;
	}

	/**
	 * get errors
	 *
	 * @param bool $detail return detail description of error
	 * @return array errors
	 */
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

	/**
	 * decode null,false/true
	 *
	 * @param $const lower case const
	 * @param $boundary boundary characters
	 * @return bool as expect or not
	 */
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

	/**
	 * decode number
	 *
	 * @param $boundary boundary characters
	 * @return array pass, value
	 */
	private function parseNumber($boundary) {
		$this->begin();
		$pass = false;
		$value = null;
		$dotCount = 0;
		if ($this->jsonlite[$this->index] === '+') {
			$this->index++;
		} else if ($this->jsonlite[$this->index] == '-') {
			$this->index++;
			$value = '-';
		}
		$charDot = ord('.');
		$charZero = ord('0');
		$charNine = ord('9');
		for (; $this->index < $this->length; $this->index++) {
			$char = $this->jsonlite[$this->index];
			$byte = ord($char);
			if ($byte === $charDot) {
				$dotCount++;
				if ($dotCount > 1) {
					$pass = false;
					break;
				}
			}

			if ($charZero <= $byte && $byte <= $charNine || $byte === $charDot) {
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

		if (!is_numeric($value)) {
			$pass = false;
		}
		if ($pass) {
			do {
				if ($dotCount) {
					$value = (double)$value;
					break;
				}

				/**
				 * 0 number
				 * 0.1 number
				 * 01 string
				 * 00 string
				 */
				if ($value === '0' || $value[0] !== '0') {
					$value = (int)$value;
					break;
				}

				$pass = false;
			} while (0);
		}

		if (!$pass) {
			$value = null;
			$this->rollback();
		}

		return array($pass, $value);
	}

	/**
	 * decode string
	 *
	 * @param string $boundary boundary characters
	 * @return array pass, value
	 */
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

	/**
	 * decode list
	 *
	 * @return array pass,value
	 */
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

			if (($sep === ',' || $sep === '[') && $char === ',') {
				$value[] = '';
				$sep = ',';
				continue;
			}

			if ($char == ',') {
				$sep = ',';
				continue;
			}

			if ($char === ']') {
				if ($sep === ',') {
					$value[] = '';
				}
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

	/**
	 * decode map/object/associative array
	 *
	 * @return array
	 */
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
			/**
			 * {:123}
			 * {key:value,:123}
			 */
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
			/**
			 * {key:,key2:value}
			 */
			if ($char === ',') {
				if ($sep === ':') {
					$sep = ',';
					$item = '';
				} else {
					$sep = ',';
					continue;
				}
			}
			/**
			 * {key:}
			 */
			if ($char === '}') {
				if ($sep === ':') {
					$item = '';
					$needBreak = true;
				} else {
					break;
				}
			}


			if ($isKey) {
				list($pass, $key) = $this->parseString(':');
				$sep = null;
				if (!$pass) {
					break;
				}
			} else {
				$isKey = true;
				if ($item === null) {
					list($pass, $item) = $this->parse(',');
					$sep = null;
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

	/**
	 * parse mixed value
	 *
	 * @param string $boundary boundary characters
	 * @return array pass,value
	 */
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

	/**
	 * decode
	 *
	 * @return mixed
	 */
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
/**
 * work with log
 */
define('JSONLITE_TYPE_MIN', JsonliteEncoder::TYPE_MIN);
/**
 * work with js
 */
define('JSONLITE_TYPE_JS', JsonliteEncoder::TYPE_JS);
/**
 * work with strong type program language
 */
define('JSONLITE_TYPE_STRICT', JsonliteEncoder::TYPE_STRICT);
/**
 * get the JSONLite representation of a value
 *
 * @param mixed $value
 * @param int $type
 * @return string
 */
function jsonlite_encode($value, $type = JSONLITE_TYPE_JS, $castAsMap = false) {
	$encoder = new JSONLiteEncoder($value, $type);
	$encoder->setCastAsMap($castAsMap);

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
	$decoder = new JSONLiteDecoder($jsonlite);
	if ($trace) {
		$GLOBALS[JSONLITE_TRACE_G] = $decoder;
	}

	return $decoder->decode();
}

/**
 * get errors
 *
 * @param bool $detail return detail description of error
 * @return array
 */
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

?>
