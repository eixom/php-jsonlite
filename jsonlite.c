/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2013 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  +----------------------------------------------------------------------+
*/

/* $Id$ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_jsonlite.h"
#include "jsonlite_encode.h"
#include "jsonlite_decode.h"


/* True global resources - no need for thread safety here */

static int le_jsonlite;

/* {{{ jsonlite_functions[]
 *
 * Every user visible function must have an entry in jsonlite_functions[].
 */
const zend_function_entry jsonlite_functions[] = {
        PHP_FE(jsonlite_encode, NULL)
        PHP_FE(jsonlite_decode, NULL)
        PHP_FE(jsonlite_get_trace, NULL)
        PHP_FE_END    /* Must be the last line in jsonlite_functions[] */
};
/* }}} */

/* {{{ jsonlite_module_entry
 */
zend_module_entry jsonlite_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
        STANDARD_MODULE_HEADER,
#endif
        "jsonlite",
        jsonlite_functions,
        PHP_MINIT(jsonlite),
        PHP_MSHUTDOWN(jsonlite),
        PHP_RINIT(jsonlite),        /* Replace with NULL if there's nothing to do at request start */
        PHP_RSHUTDOWN(jsonlite),    /* Replace with NULL if there's nothing to do at request end */
        PHP_MINFO(jsonlite),
#if ZEND_MODULE_API_NO >= 20010901
        PHP_JSONLITE_VERSION,
#endif
        STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_JSONLITE
ZEND_GET_MODULE(jsonlite)
#endif

/* {{{ PHP_INI
 */
/* Remove comments and fill if you need to have entries in php.ini
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("jsonlite.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_jsonlite_globals, jsonlite_globals)
    STD_PHP_INI_ENTRY("jsonlite.global_string", "foobar", PHP_INI_ALL, OnUpdateString, global_string, zend_jsonlite_globals, jsonlite_globals)
PHP_INI_END()
*/
/* }}} */

ZEND_DECLARE_MODULE_GLOBALS(jsonlite)

/* {{{ php_jsonlite_init_globals
 */
static void php_jsonlite_init_globals(zend_jsonlite_globals *jsonlite_globals) {
    jsonlite_globals->decoder = 0;
}
/* }}} */

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION (jsonlite) {
    /* If you have INI entries, uncomment these lines
    REGISTER_INI_ENTRIES();
    */
    ZEND_INIT_MODULE_GLOBALS(jsonlite, php_jsonlite_init_globals, NULL);
    REGISTER_LONG_CONSTANT("JSONLITE_TYPE_MIN", JSONLITE_TYPE_MIN, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("JSONLITE_TYPE_JS", JSONLITE_TYPE_JS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("JSONLITE_TYPE_STRICT", JSONLITE_TYPE_STRICT, CONST_CS | CONST_PERSISTENT);
    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION (jsonlite) {
    /* uncomment this line if you have INI entries
    UNREGISTER_INI_ENTRIES();
    */
    return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request start */
/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION (jsonlite) {
    return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request end */
/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION (jsonlite) {
    php_jsonlite_free(JSONLITE_G(decoder) /**/TSRMLS_CC);
    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION (jsonlite) {
    php_info_print_table_start();
    php_info_print_table_header(2, "jsonlite support", "enabled");
    php_info_print_table_end();

    /* Remove comments if you have entries in php.ini
    DISPLAY_INI_ENTRIES();
    */
}
/* }}} */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
