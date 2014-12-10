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

#ifndef JSONLITE_HELPER_H
#define JSONLITE_HELPER_H

void jsonlite_error(int type TSRMLS_DC, const char *name, ...);

zend_bool is_in_list(char **haystack, zend_uint size, const char *needle);

#endif    /* JSONLITE_HELPER_H */

