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



#ifndef JSONLITE_DECODE_H
#define JSONLITE_DECODE_H

#include "php.h"


#define JSONLITE_PARSER_MAX_DEPTH 512

typedef struct {
    char *jsonlite; // jsonlite string
    uint index; // offset
    uint length; // jsonlite string length
    uint transactionIndex; // transaction start index
    zval *trace; // error trace
    char stack[JSONLITE_PARSER_MAX_DEPTH]; // brackets stack
    int stack_index;
} jsonlite_decoder;

PHPAPI zend_bool php_jsonlite_decode(char *jsonlite, int jsonlite_len, zval **value,
        jsonlite_decoder **decoder TSRMLS_DC);

PHPAPI void php_jsonlite_free(jsonlite_decoder *decoder TSRMLS_DC);

PHP_FUNCTION (jsonlite_decode);

PHP_FUNCTION (jsonlite_get_trace);

#endif
/*
 * Local Variables:
 * c-basic-offset: 4
 * tab-width: 4
 * End:
 * vim600: fdm=marker
 * vim: noet sw=4 ts=4
 */
