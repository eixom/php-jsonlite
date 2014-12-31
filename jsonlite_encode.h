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



#ifndef JSONLITE_ENCODE_H
#define JSONLITE_ENCODE_H

#include "ext/standard/php_smart_str.h"


#define JSONLITE_MODE_MIN 1
#define JSONLITE_MODE_JS 2
#define JSONLITE_MODE_STRICT 3

typedef struct {
    zend_uchar type;
    zend_bool cast_as_map;
    uint depth;
} jsonlite_encoder;


void php_jsonlite_encode(jsonlite_encoder *encoder, smart_str *buffer, zval *value TSRMLS_DC);

PHP_FUNCTION (jsonlite_encode);

#endif
/*
 * Local Variables:
 * c-basic-offset: 4
 * tab-width: 4
 * End:
 * vim600: fdm=marker
 * vim: noet sw=4 ts=4
 */
