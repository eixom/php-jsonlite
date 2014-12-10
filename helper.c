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

#ifndef PHP_JSONLITE_HELPER_H
#define PHP_JSONLITE_HELPER_H

#include "php.h"
#include "php_jsonlite.h"
#include "ctype.h"


/** {{{ ze_error
*/
void jsonlite_error(int type TSRMLS_DC, const char *name, ...) {
    va_list ap;
    int len = 0;
    char *buf = NULL;
    char *msg = NULL;

    if (EG(exception)) {
        zend_throw_exception_object(EG(exception)/**/TSRMLS_CC);
        return;
    }

    va_start(ap, name);
    len = vspprintf(&buf, 0, name, ap);
    va_end(ap);
    spprintf(&msg, 0, INI_STR("errors_doc_url"), buf);

    php_error(type, msg);
    efree(msg);
    efree(buf);
}

/* }}} */

zend_bool is_in_list(char **haystack, zend_uint size, const char *needle) {
    zend_bool is_exists = 0;
    int i = 0;
    for (i = 0; i < size; i++) {
        if (strcmp(needle, haystack[i]) == 0) {
            is_exists = 1;
            break;
        }
    }
    return is_exists;
}

#endif /* ZE_HELPER_C */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
