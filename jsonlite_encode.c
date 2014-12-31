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


#include "php.h"
#include "ext/standard/php_smart_str.h"


#include "php_jsonlite.h"
#include "helper.h"
#include "jsonlite_encode.h"

static void append_string(jsonlite_encoder *self, smart_str *buffer, char *str, int len, zend_bool is_map_key TSRMLS_DC);

static void append(jsonlite_encoder *self, smart_str *buffer, zval *val TSRMLS_DC);

static void append_null(jsonlite_encoder *self, smart_str *buffer) {
    switch (self->type) {
        case JSONLITE_MODE_MIN:
            break;
        case JSONLITE_MODE_JS:
            smart_str_appendl(buffer, "0", 1);
            break;
        case JSONLITE_MODE_STRICT:
        default:
            smart_str_appendl(buffer, "null", 4);

    }
}

static void append_bool(jsonlite_encoder *self, smart_str *buffer, zend_bool val) {
    switch (self->type) {
        case JSONLITE_MODE_MIN:
            if (val) {
                smart_str_appendc(buffer, '1');
            }
            break;
        case JSONLITE_MODE_JS:
            if (val) {
                smart_str_appendc(buffer, '1');
            } else {
                smart_str_appendc(buffer, '0');
            }
            break;
        case JSONLITE_MODE_STRICT:
        default:
            if (val) {
                smart_str_appendl(buffer, "true", 4);
            } else {
                smart_str_appendl(buffer, "false", 5);
            }

    }
}


static void append_long(smart_str *buffer, long val) {
    smart_str_append_long(buffer, val);
}

static void append_double(jsonlite_encoder *self, smart_str *buffer, double val TSRMLS_DC) {
    char *str = NULL;
    int len = 0;

    if (!zend_isinf(val) && !zend_isnan(val)) {
        len = spprintf(&str, 0, "%.*G", (int) EG(precision), val);
        smart_str_appendl(buffer, str, len);

        if (self->type == JSONLITE_MODE_STRICT) {
            if (strchr(str, '.') == NULL) {
                smart_str_appendl(buffer, ".0", 2);
            }
        }
        efree(str);
    } else {
        php_error(E_WARNING, "double.not.conform.json.spec.encode.as.0:%.9g", val);
        smart_str_appendc(buffer, '0');
    }
}


static zend_bool is_assoc(jsonlite_encoder *self, HashTable *array TSRMLS_DC) {
    zend_bool is_assoc = 0;
    HashPosition pointer = NULL;
    char *key = NULL;
    uint key_size = 0;
    int key_type = 0;
    ulong index = 0;
    ulong i = 0;

//    zend_hash_apply_with_arguments(myht, (apply_func_args_t) php_array_element_export, 2, level, buf);

    zend_hash_internal_pointer_reset_ex(array, &pointer);
    while (zend_hash_has_more_elements_ex(array, &pointer) == SUCCESS) {

        key_type = zend_hash_get_current_key_ex(array, &key, &key_size, &index, 0, &pointer);

#if PHP_DEBUG
//        php_printf("plen:%u,key_type:%d,index:%u,i:%u\n", pointer->nKeyLength, key_type, index, i);
#endif
        /**
        *
        */
        if (key_type == HASH_KEY_IS_STRING) {
            is_assoc = 1;
            break;
        }

        if (i != index) {
            is_assoc = 1;
            break;
        }
        i++;
        if (zend_hash_move_forward_ex(array, &pointer) == FAILURE) {
            break;
        }
    }

    if (!is_assoc && i == 0) {
        is_assoc = self->cast_as_map;
    }

#if PHP_DEBUG
//    php_printf("is_assoc:%d\n", is_assoc);
#endif

    return is_assoc;
}


static void append_array(jsonlite_encoder *self, smart_str *buffer, HashTable *array TSRMLS_DC) {

    zend_bool is_assoc = 0;
    HashPosition pointer = NULL;
    zval **value;
    ulong i = 0;
    smart_str_appendc(buffer, '[');

    if (array && array->nApplyCount > 0) {
        smart_str_appendl(buffer, "null", 4);
        php_error(E_WARNING, "circular.references");
        return;
    }
    array->nApplyCount++;
    zend_hash_internal_pointer_reset_ex(array, &pointer);
    while (zend_hash_has_more_elements_ex(array, &pointer) == SUCCESS) {

        if (i != 0) {
            smart_str_appendc(buffer, ',');
        }

        if (zend_hash_get_current_data_ex(array, (void **) &value, &pointer) == SUCCESS) {
            self->depth++;
            (void) append(self, buffer, *value TSRMLS_CC);
        }
        i++;
        if (zend_hash_move_forward_ex(array, &pointer) == FAILURE) {
            break;
        }
    }

    if (i == 1) {
        if (self->type != JSONLITE_MODE_JS
                && Z_TYPE_PP(value) == IS_STRING && Z_STRLEN_PP(value) == 0) {
            smart_str_appendl(buffer, "\"\"", 2);
        }

        if (self->type == JSONLITE_MODE_MIN
                && Z_TYPE_PP(value) == IS_NULL) {
            smart_str_appendl(buffer, "\"\"", 2);
        }
    }
    array->nApplyCount--;
    smart_str_appendc(buffer, ']');
}

static void append_map(jsonlite_encoder *self, smart_str *buffer, HashTable *array TSRMLS_DC) {
    zend_bool is_assoc = 0;
    HashPosition pointer = NULL;
    char *key = NULL;
    uint key_size = 0;
    int key_type = 0;
    zval **value;
    ulong index;
    ulong i = 0;

    smart_str_appendc(buffer, '{');

    if (array && array->nApplyCount > 0) {
        smart_str_appendl(buffer, "null", 4);
        php_error(E_WARNING, "circular.references");
        return;
    }
    array->nApplyCount++;
    zend_hash_internal_pointer_reset_ex(array, &pointer);

    while (zend_hash_has_more_elements_ex(array, &pointer) == SUCCESS) {

        if (i != 0) {
            smart_str_appendc(buffer, ',');
        }
        key_type = zend_hash_get_current_key_ex(array, &key, &key_size, &index, 0, &pointer);

        if (key_type != HASH_KEY_NON_EXISTANT) {
            if (zend_hash_get_current_data_ex(array, (void **) &value, &pointer) == SUCCESS) {
                self->depth++;

                if (key_type == HASH_KEY_IS_STRING) {
                    append_string(self, buffer, key, key_size - 1, 1 TSRMLS_CC);
                } else {
                    append_long(buffer, index);
                }

                smart_str_appendc(buffer, ':');
                (void) append(self, buffer, *value TSRMLS_CC);
            }
        }

        i++;
        if (zend_hash_move_forward_ex(array, &pointer) == FAILURE) {
            break;
        }
    }
    array->nApplyCount--;

    smart_str_appendc(buffer, '}');
}

static zend_bool is_value_keyword(const char *value, const uint len) {
    char lower[6] = {0};
    zend_bool is_keyword = 0;
    char *keywords[3] = {"null", "true", "false"};

    if (len < 6) {
        zend_str_tolower_copy(lower, value, len);
        if (is_in_list(keywords, 3, lower)) {
            is_keyword = 1;
        }
    }
    return is_keyword;
}

static zend_bool is_keyword(const char *value, const uint len) {
    /**
    * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Lexical_grammar#Keywords
    */

    zend_bool is_keyword = 0;
    char lower[11] = {0};
    int value_len = 0;

    char *keywords[37] = {
            "null", "true", "false", "break", "case",
            "class", "catch", "const", "continue",
            "debugger", "default", "delete", "do",
            "else", "export", "extends", "finally",
            "for", "function", "if", "import", "in",
            "instanceof", "let", "new", "return",
            "super", "switch", "this", "throw", "try",
            "typeof", "var", "void", "while", "with", "yield"
    };

    if (strlen(value) < 11) {
        zend_str_tolower_copy(lower, value, len);
        if (is_in_list(keywords, 37, lower)) {
            is_keyword = 1;
        }
    }
    return is_keyword;
}


static zend_bool is_key(const char *value, const uint len) {
    zend_bool is_key = 0;
    int i = 0;
    char ch = 0;
    do {
        if (len < 1) {
            break;
        }
        is_key = 1;
        /**
        * match /^[a-zA-Z_][a-zA-Z0-9_]*$/
        */
        while ((ch = *(value + i)) != 0) {
            i++;

            if (i == 1) {
                if (isalpha(ch) == 0 && ch != '_') {
                    is_key = 0;
                    break;
                }
                continue;
            }

            if (isalnum(ch) == 0 && ch != '_') {
                is_key = 0;
                break;
            }
        }

    } while (0);

    return is_key;
}

static zend_bool is_quote(jsonlite_encoder *self, smart_str *buffer, char *str, int len, zend_bool is_map_key TSRMLS_DC) {
    zend_bool quote = 0;
    do {
        /**
        * special character
        */
        if (strpbrk(str, ",[]{}") != NULL) {
            quote = 1;
            break;
        }
        /**
        * array('key:' => value);
        */
        if (is_map_key && strchr(str, ':') != NULL) {
            quote = 1;
            break;
        }
        /**
        * 'value' keyword
        * true false null
        */
        if (is_value_keyword(str, len)) {
            quote = 1;
            break;
        }

        if (self->type == JSONLITE_MODE_JS) {
            if (is_keyword(str, len)) {
                quote = 1;
                break;
            }

            if (is_map_key) {
                if (len == 0) {
                    quote = 1;
                    break;
                }

                if (strchr(str, ':') != NULL) {
                    quote = 1;
                    break;
                }

                if (!is_key(str, len) && is_numeric_string(str, len, NULL, NULL, 0) <= 0) {
                    quote = 1;
                    break;
                }
            } else {
                /**
                * 0 0
                * 0.1 0.1
                * 01 "01"
                * 00 "00"
                */
                if (is_numeric_string(str, len, NULL, NULL, 0) <= 0) {
                    quote = 1;
                    break;
                }

                if (strchr(str, '.') == NULL && str[0] == '0') {
                    quote = 1;
                    break;
                }
            }

        }

        if (self->type == JSONLITE_MODE_STRICT) {
            if (!is_map_key) {
                /**
                * 0 "0"
                * 0.1 "0.1"
                * 01 01
                * 00 00
                */
                if (is_numeric_string(str, len, NULL, NULL, 0) > 0) {
                    quote = 1;

                    if (strchr(str, '.') == NULL) {
                        if (str[0] == '0') {
                            quote = 0;
                        }
                    }
                    break;
                }
            }

        }

    } while (0);

    return quote;
}

static void append_string(jsonlite_encoder *self, smart_str *buffer, char *str, int len, zend_bool is_map_key TSRMLS_DC) {
    char ch = 0;
    uint i = 0;
    zend_bool quote = 0;

    quote = is_quote(self, buffer, str, len, is_map_key TSRMLS_CC);

    if (quote) {
        smart_str_appendc(buffer, '"');
    }

    for (i = 0; i < len; i++) {
        ch = *(str + i);
        switch (ch) {
            case '"':
                if (quote) {
                    smart_str_appendl(buffer, "\\\"", 2);
                } else {
                    smart_str_appendc(buffer, '"');
                }
                break;
            case '\\':
                smart_str_appendl(buffer, "\\\\", 2);
                break;
            case '\b':
                /**
                * WARINING: "\b" expressed as "\" and "b"
                */
                smart_str_appendl(buffer, "\\b", 2);
                break;
            case '\f':
                smart_str_appendl(buffer, "\\f", 2);
                break;
            case '\n':
                smart_str_appendl(buffer, "\\n", 2);
                break;
            case '\r':
                smart_str_appendl(buffer, "\\r", 2);
                break;
            case '\t':
                smart_str_appendl(buffer, "\\t", 2);
                break;
            default:
                smart_str_appendc(buffer, ch);
                break;
        }

    }
    if (quote) {
        smart_str_appendc(buffer, '"');
    }
}


static void append(jsonlite_encoder *self, smart_str *buffer, zval *val TSRMLS_DC) {

    zend_uchar type = Z_TYPE_P(val);

    do {
        if (type == IS_NULL) {
            append_null(self, buffer);
            break;
        }

        if (type == IS_BOOL) {
            append_bool(self, buffer, Z_BVAL_P(val));
            break;
        }

        if (type == IS_LONG) {
            append_long(buffer, Z_LVAL_P(val));
            break;
        }

        if (type == IS_DOUBLE) {
            append_double(self, buffer, Z_DVAL_P(val)/* */TSRMLS_CC);
            break;
        }

        if (type == IS_STRING) {
            append_string(self, buffer, Z_STRVAL_P(val), Z_STRLEN_P(val),/*is_map_key*/ 0 TSRMLS_CC);
            break;
        }

        if (type == IS_ARRAY) {

            if (is_assoc(self, Z_ARRVAL_P(val)/**/TSRMLS_CC)) {
                append_map(self, buffer, Z_ARRVAL_P(val) /**/TSRMLS_CC);
                break;
            }
            append_array(self, buffer, Z_ARRVAL_P(val)/**/ TSRMLS_CC);
            break;
        }

        if (type == IS_OBJECT && Z_OBJ_HT_P(val)->get_properties != NULL) {
            append_map(self, buffer, Z_OBJ_HT_P(val)->get_properties(val TSRMLS_CC) /**/TSRMLS_CC);
            break;
        }
        php_error(E_WARNING, "unsupported.type.encode.as.null");
        smart_str_appendl(buffer, "null", 4);
    } while (0);
}

void php_jsonlite_encode(jsonlite_encoder *encoder, smart_str *jsonlite, zval *value TSRMLS_DC) {
    append(encoder, jsonlite, value TSRMLS_CC);
    smart_str_0(jsonlite);
}

/* {{{ proto string jsonlite_encode(mixed value, [string type, [cast_as_map = false]])
   Reads a line */
PHP_FUNCTION (jsonlite_encode) {
    zval *value = NULL;
    jsonlite_encoder self = {/*type*/0, /*cast_as_map*/0, /*depth*/0};
    uint encode_type = JSONLITE_MODE_JS;
    zend_bool cast_as_map = 0;
    smart_str jsonlite = {NULL, 0, 0};
    char *str = NULL;


    if (zend_parse_parameters(ZEND_NUM_ARGS()TSRMLS_CC, "z|lb",
            &value,
            &encode_type,
            &cast_as_map
    ) == FAILURE) {
        WRONG_PARAM_COUNT;
    }

    self.type = (zend_uchar) encode_type;
    self.cast_as_map = cast_as_map;

    php_jsonlite_encode(&self, &jsonlite, value TSRMLS_CC);

    if (jsonlite.len) {
        spprintf(&str, jsonlite.len, "%s", jsonlite.c);
        smart_str_free(&jsonlite);
        RETURN_STRING(str, 0);
    } else {
        smart_str_free(&jsonlite);
        RETURN_EMPTY_STRING();
    }
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
