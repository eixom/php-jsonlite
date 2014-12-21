dnl $Id$
dnl config.m4 for extension jsonlite

PHP_ARG_WITH(jsonlite, for jsonlite support,
[  --with-jsonlite             Include jsonlite support])

if test "$PHP_JSONLITE" != "no"; then


AC_DEFUN([PHP_JSONLITE_ADD_SOURCE], [
    PHP_JSONLITE_SOURCES="$PHP_JSONLITE_SOURCES $1"
])
    PHP_JSONLITE_ADD_SOURCE([jsonlite.c])
    PHP_JSONLITE_ADD_SOURCE([helper.c])
    PHP_JSONLITE_ADD_SOURCE([jsonlite_encode.c])
    PHP_JSONLITE_ADD_SOURCE([jsonlite_decode.c])

    PHP_NEW_EXTENSION(jsonlite, $PHP_JSONLITE_SOURCES,$ext_shared)

dnl    ifdef([PHP_ADD_EXTENDION_DEP],
dnl    [
dnl        PHP_ADD_EXTENSION_DEP(PDO, pcre, session, true)
dnl    ])

fi
# vim600: sts=4 sw=4 et
