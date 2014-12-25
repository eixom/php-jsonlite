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

#ifndef PHP_JSONLITE_H
#define PHP_JSONLITE_H


#include "jsonlite_decode.h"

#define PHP_JSONLITE_VERSION "0.1"

#define JSONLITE_ERROR_URL "https://github.com/zoeey/jsonlite/blob/master/doc/error/%s.md"

extern zend_module_entry jsonlite_module_entry;
#define phpext_jsonlite_ptr &jsonlite_module_entry

#ifdef PHP_WIN32
#	define PHP_JSONLITE_API __declspec(dllexport)
#elif defined(__GNUC__) && __GNUC__ >= 4
#	define PHP_JSONLITE_API __attribute__ ((visibility("default")))
#else
#	define PHP_JSONLITE_API
#endif

#ifndef PHP_FE_END
#define PHP_FE_END {NULL, NULL, NULL}
#endif PHP_FE_END

#ifdef ZTS
#include "TSRM.h"
#endif

PHP_MINIT_FUNCTION(jsonlite);
PHP_MSHUTDOWN_FUNCTION(jsonlite);
PHP_RINIT_FUNCTION(jsonlite);
PHP_RSHUTDOWN_FUNCTION(jsonlite);
PHP_MINFO_FUNCTION(jsonlite);


ZEND_BEGIN_MODULE_GLOBALS(jsonlite)
    jsonlite_decoder *decoder;
ZEND_END_MODULE_GLOBALS(jsonlite)


#ifdef ZTS
#define JSONLITE_G(v) TSRMG(jsonlite_globals_id, zend_jsonlite_globals *, v)
#else
#define JSONLITE_G(v) (jsonlite_globals.v)
#endif

#endif	/* PHP_JSONLITE_H */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
