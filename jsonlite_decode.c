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
#include "jsonlite_decode.h"

ZEND_EXTERN_MODULE_GLOBALS(jsonlite)

static zend_bool parse(jsonlite_decoder *self, zval **ret, const char *boundary);

static void stack_push(jsonlite_decoder *self, const char ch) {
    self->stack[self->stack_index] = ch;
    self->stack_index++;
}

static char stack_last(jsonlite_decoder *self) {
    char ch = 0;
    if (self->stack_index > 0) {
        ch = self->stack[self->stack_index - 1];
    }
    return ch;
}

static char stack_pop(jsonlite_decoder *self) {
    char ch = 0;
    if (self->stack_index > 0) {
        ch = self->stack[self->stack_index - 1];
        self->stack_index--;
        self->stack[self->stack_index] = 0;
    }
    return ch;
}

static void begin(jsonlite_decoder *self) {
    self->transactionIndex = self->index;
}


static void rollback(jsonlite_decoder *self) {
    self->index = self->transactionIndex;
}

static void trace(jsonlite_decoder *self, const char *msg, const char *detail) {
    zval *trace = NULL;

    MAKE_STD_ZVAL(trace);
    array_init(trace);
    add_next_index_string(trace, msg, 1);
    add_next_index_long(trace, self->transactionIndex);
    add_next_index_long(trace, self->index);

    if (detail != NULL) {
        add_next_index_string(trace, detail, 1);
    }

    add_next_index_zval(self->trace, trace);

}

static zval *get_trace(jsonlite_decoder *self) {
    zval_add_ref(&self->trace);
    return self->trace;
}

static zval *get_trace_detail(const jsonlite_decoder *self) {

    HashTable *array = NULL;
    HashPosition *pointer = NULL;

    zval *traces = NULL;
    zval *trace = NULL;
    zval **val = NULL;
    zval **msg = NULL;
    zval **range_start = NULL;
    zval **range_end = NULL;
    zval *range = NULL;
    char *str = NULL;
    zval *chars = NULL;
    zval **detail = NULL;
    int start = 0;
    int end = 0;

    array = Z_ARRVAL_P(self->trace);

    ALLOC_INIT_ZVAL(traces);
    array_init(traces);

    zend_hash_internal_pointer_reset_ex(array, pointer);
    while (zend_hash_has_more_elements_ex(array, pointer) == SUCCESS) {
        if (zend_hash_get_current_data_ex(array, (void **) &val, pointer) == SUCCESS) {

            if (val && Z_TYPE_PP(val) == IS_ARRAY) {

                zend_hash_index_find(Z_ARRVAL_PP(val), 0, (void **) &msg);
                zend_hash_index_find(Z_ARRVAL_PP(val), 1, (void **) &range_start);
                zend_hash_index_find(Z_ARRVAL_PP(val), 2, (void **) &range_end);

                if (zend_hash_index_exists(Z_ARRVAL_PP(val), 3) == 1) {
                    zend_hash_index_find(Z_ARRVAL_PP(val), 3, (void **) &detail);
                }

                ALLOC_INIT_ZVAL(trace);
                array_init(trace);

                zval_add_ref(msg);
                add_assoc_zval_ex(trace, ZEND_STRS("msg"), *msg);

                ALLOC_INIT_ZVAL(range);
                array_init(range);

                zval_add_ref(range_start);
                add_next_index_zval(range, *range_start);

                zval_add_ref(range_end);
                add_next_index_zval(range, *range_end);

                add_assoc_zval_ex(trace, ZEND_STRS("range"), range);
                start = Z_LVAL_PP(range_start);
                if (start > 0) {
                    start--;
                }
                end = Z_LVAL_PP(range_end);
                if (end < self->length) {
                    end++;
                }

                str = zend_strndup((self->jsonlite + start), end - start);
                add_assoc_string_ex(trace, ZEND_STRS("chars"), str, 1);
                free(str);

                if (detail != NULL) {
                    zval_add_ref(detail);
                    add_assoc_zval_ex(trace, ZEND_STRS("detail"), *detail);
                }

                add_next_index_zval(traces, trace);
                detail = NULL;
                trace = NULL;
            }
        }
        if (zend_hash_move_forward_ex(array, pointer) == FAILURE) {
            break;
        }
    }
    return traces;
}

static zend_bool parse_const(jsonlite_decoder *self, const char *constent, const char *boundary) {


    zend_bool pass = 1;
    char value[35] = {0};
    char ch = 0;
    int i = 0;

    begin(self);


    for (; self->index < self->length; self->index++, i++) {
        ch = self->jsonlite[self->index];

        if (strchr(constent, tolower(ch)) != NULL) {
            value[i] = ch;
            continue;
        }

        if ((boundary == NULL || strchr(boundary, ch) == NULL) && stack_last(self) != ch) {
            /**
            * string start with null/false/true...
            */
            pass = 0;
            // trace string
        }

        self->index--;
        break;
    }

    if (pass) {
        pass = zend_binary_strcasecmp(value, i, constent, strlen(constent)) == 0;
    }

    if (!pass) {
        rollback(self);
    }

    return pass;
}

static zend_bool parse_number(jsonlite_decoder *self, zval **ret, const char *boundary) {
    zval *value = NULL;
    zend_bool pass = 0;
    int dotCount = 0;
    char ch = 0;
    smart_str value_buf = {NULL, 0, 0};
    long long_val = 0;
    double double_val = 0;
    zend_uchar type = 0;

    begin(self);

    if (self->jsonlite[self->index] == '+') {
        self->index++;
    } else if (self->jsonlite[self->index] == '-') {
        self->index++;
        smart_str_appendc(&value_buf, '-');
    }

    for (; self->index < self->length; self->index++) {
        ch = self->jsonlite[self->index];
        if (ch == '.') {
            dotCount++;
            if (dotCount > 1) {
                pass = 0;
                break;
            }
        }

        if (('0' <= ch && ch <= '9') || ch == '.') {
            smart_str_appendc(&value_buf, ch);
            pass = 1;
            continue;
        }


        if ((boundary == NULL || strchr(boundary, ch) == NULL) && stack_last(self) != ch) {
            /**
            * string start with number
            */
            pass = 0;
            // trace string
        }
        self->index--;
        break;
    }

    smart_str_0(&value_buf);

    type = is_numeric_string(value_buf.c, value_buf.len, &long_val, &double_val, 0);


    if (!type) {
        pass = 0;
    }

    if (pass) {
        do {
            if (dotCount) {
                type = IS_DOUBLE;
                break;
            }

            /**
            * 0 number
            * 0.1 number
            * 01 string
            * 00 string
            */
            if (strcasecmp(value_buf.c, "0") == 0 || strchr(value_buf.c, '0') == 0) {
                type = IS_LONG;
                break;
            }

            pass = 0;
        } while (0);
    }

    if (!pass) {
        rollback(self);
    } else {
        if (type == IS_LONG) {
            ALLOC_INIT_ZVAL(value);
            ZVAL_LONG(value, long_val)
        } else if (type == IS_DOUBLE) {
            ALLOC_INIT_ZVAL(value);
            ZVAL_DOUBLE(value, double_val)
        }
    }

    smart_str_free(&value_buf);

    if (pass) {
        *ret = value;
    }

    return pass;
}


static zend_bool parse_string(jsonlite_decoder *self, zval **ret, const char *boundary) {

    zend_bool pass = 0;
    zval *value = NULL;
    smart_str value_buf = {NULL, 0, 0};
    zend_bool is_quote = 0;
    zend_bool is_escape = 0;
    char terminal = 0;
    char ch = 0;
    value = NULL;
    begin(self);


    if (self->jsonlite[self->index] == '"') {
        is_quote = 1;
        self->index++;
    }

    for (; self->index < self->length; self->index++) {
        ch = self->jsonlite[self->index];

        if (!is_quote) {
            if (boundary != NULL && strchr(boundary, ch) != NULL) {
                terminal = ch;
            } else if (stack_last(self) == ch) {
                terminal = ch;
            }
        }

        if (terminal != 0) {
            pass = 1;

            if ((boundary == NULL || strchr(boundary, ch) == NULL) && stack_last(self) != ch) {
                pass = 0;
                trace(self, "string.boundary", NULL);
            }

            self->index--;
            break;
        }

        if (is_escape) {
            is_escape = 0;
            switch (ch) {
                case '"':
                    smart_str_appendc(&value_buf, '"');
                    break;
                case '\\':
                    smart_str_appendc(&value_buf, '\\');
                    break;
                case 'b':
                    smart_str_appendc(&value_buf, '\b');
                    break;
                case 'f':
                    smart_str_appendc(&value_buf, '\f');
                    break;
                case 'n':
                    smart_str_appendc(&value_buf, '\n');
                    break;
                case 'r':
                    smart_str_appendc(&value_buf, '\r');
                    break;
                case 't':
                    smart_str_appendc(&value_buf, '\t');
                    break;
                default:
                    smart_str_appendc(&value_buf, ch);
                    break;
            }
            continue;
        }

        if (ch == '\\') {
            is_escape = 1;
            continue;
        }

        if (is_quote && ch == '"') {
            terminal = ch;
            continue;
        }
        smart_str_appendc(&value_buf, ch);
    }

    if (terminal && ch == '"') {
        pass = 1;
    }

    if (!terminal && self->index == self->length) {
        pass = 1;
    }

    if (!pass) {
        rollback(self);
    }

    smart_str_0(&value_buf);

    if (pass) {
        ALLOC_INIT_ZVAL(value)
        ZVAL_STRINGL(value, value_buf.c, value_buf.len, 1);
        *ret = value;
    } else {
        if (value) {
            zval_dtor(value);
            value = NULL;
        }
    }


    smart_str_free(&value_buf);

    return pass;
}

static zend_bool parse_list(jsonlite_decoder *self, zval **ret) {
    zend_bool pass = 1;
    char sep = '[';
    zval *item = NULL;
    char ch = 0;
    zval *value = NULL;

    begin(self);
    stack_push(self, ']');
    self->index++;

    ALLOC_INIT_ZVAL(value);
    array_init(value);

    for (; self->index < self->length; self->index++) {
        ch = self->jsonlite[self->index];

        if ((sep == ',' || sep == '[') && ch == ',') {
            add_next_index_string(value, "", 1);
            sep = ',';
            continue;
        }

        if (ch == ',') {
            sep = ',';
            continue;
        }

        if (ch == ']') {
            if (sep == ',') {

                add_next_index_string(value, "", 1);
            }
            break;
        }

        sep = 0;
        pass = parse(self, &item, ",");

        if (pass) {
            add_next_index_zval(value, item);
        }
    }

    pass = pass && ch == ']';

    if (!pass) {
        rollback(self);
        if (value) {
            zval_ptr_dtor(&value);
            value = NULL;
        }
        if (item) {
            zval_ptr_dtor(&item);
            value = NULL;
        }


    } else {
        if (stack_last(self) == ']') {
            stack_pop(self);
        }
    }

    if (pass) {
        *ret = value;
    }
    return pass;
}


static zend_bool parse_map(jsonlite_decoder *self, zval **ret) {
    zend_bool pass = 1;
    zval *value = NULL;
    char sep = '{';
    zval *item = NULL;
    char ch = 0;
    zend_bool is_key = 1; // key 1, value 0
    zval *key = NULL;
    zend_bool is_break = 0;


    begin(self);
    stack_push(self, '}');
    self->index++;

    ALLOC_INIT_ZVAL(value);
    array_init(value);

    for (; self->index < self->length; self->index++) {
        ch = self->jsonlite[self->index];
        /**
        * {:123}
        * {key:value,:123}
        */
        if (ch == ':') {
            is_key = 0;
            if (sep) {
                sep = ':';
                ALLOC_INIT_ZVAL(key);
                ZVAL_EMPTY_STRING(key);
            } else {
                sep = ':';
            }
            continue;
        }
        /**
        * {key:,key2:value}
        */
        if (ch == ',') {
            if (sep == ':') {
                sep = ',';
                ALLOC_INIT_ZVAL(item);
                ZVAL_EMPTY_STRING(item);
            } else {
                sep = ',';
                continue;
            }
        }
        /**
        * {key:}
        */
        if (ch == '}') {
            if (sep == ':') {
                ALLOC_INIT_ZVAL(item);
                ZVAL_EMPTY_STRING(item);
                is_break = 1;
            } else {
                break;
            }
        }


        if (is_key) {
            pass = parse_string(self, &key, ":");
            sep = 0;
            if (!pass) {
                if (key) {
                    zval_dtor(key);
                    key = NULL;
                }
                if (item) {
                    zval_dtor(key);
                    key = NULL;
                }

                break;
            }
        } else {
            is_key = 1;
            if (item == NULL) {
                pass = parse(self, &item, ",");
                sep = 0;
                if (!pass) {
                    if (key) {
                        zval_dtor(key);
                        key = NULL;
                    }

                    if (item) {
                        zval_dtor(item);
                        item = NULL;
                    }
                    break;
                }
            }
            add_assoc_zval_ex(value, Z_STRVAL_P(key), Z_STRLEN_P(key) + 1, item);
            zval_ptr_dtor(&key);

            key = NULL;
            item = NULL;
        }

        if (is_break) {
            break;
        }
    }
    if (pass && ch != '}') {
        pass = 0;
        trace(self, "map.terminal", NULL);
    } else {
        if (stack_last(self) == '}') {
            stack_pop(self);
        }
    }

    if (pass) {
        *ret = value;
    } else {

        if (key) {
            zval_dtor(key);
            key = NULL;
        }

        if (item) {
            zval_dtor(item);
            item = NULL;
        }

        if (value) {
            zval_ptr_dtor(&value);
            value = NULL;
        }

        rollback(self);
    }

    return pass;
}


static zend_bool parse(jsonlite_decoder *self, zval **ret, const char *boundary) {
    zend_bool pass = 0;
    char ch = 0;
    zval *value = NULL;

    if (self->length == 0) {
        pass = 1;
        ALLOC_INIT_ZVAL(value);
        ZVAL_EMPTY_STRING(value);
    } else {

        ch = self->jsonlite[self->index];


        switch (ch) {
            case '"':
                pass = parse_string(self, &value, boundary);
                break;
            case '[':
                pass = parse_list(self, &value);
                break;
            case '{':
                pass = parse_map(self, &value);
                break;
            case 'n':
            case 'N':
                pass = parse_const(self, "null", boundary);
                if (pass) {
                    ALLOC_INIT_ZVAL(value);
                    ZVAL_NULL(value);
                } else {
                    pass = parse_string(self, &value, boundary);
                }
                break;
            case 't':
            case 'T':
                pass = parse_const(self, "true", boundary);
                if (pass) {
                    ALLOC_INIT_ZVAL(value);
                    ZVAL_TRUE(value);
                } else {
                    pass = parse_string(self, &value, boundary);
                }
                break;
            case 'f':
            case 'F':
                pass = parse_const(self, "false", boundary);
                if (pass) {
                    ALLOC_INIT_ZVAL(value);
                    ZVAL_FALSE(value);
                } else {

                    pass = parse_string(self, &value, boundary);
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
                pass = parse_number(self, &value, boundary);
                if (!pass) {
                    if (value) {
                        zval_dtor(value);
                        value = NULL;
                    }
                    pass = parse_string(self, &value, boundary);
                }

                break;
            case '}':
            case ']':
            case ',':
            case ':':
                pass = 0;
                trace(self, "parse.char", NULL);
                break;
            default:
                pass = parse_string(self, &value, boundary);
        }
    }

    if (pass) {
        *ret = value;
    } else {
        if (value) {
            zval_dtor(value);
            value = NULL;
        }
    }
    return pass;
}

static zend_bool decode(jsonlite_decoder *self, zval **ret) {
    zend_bool pass = 0;
    char ch = 0;
    zval *value = NULL;

    pass = parse(self, &value, NULL);

    if (strlen(self->stack) > 0) {
        pass = 0;
        trace(self, "brackets.match", self->stack);
    }

    if (pass) {
        *ret = value;
    } else {
        if (value) {
            zval_dtor(value);
            value = NULL;
        }
    }
    return pass;
}


PHPAPI zend_bool php_jsonlite_decode(char *jsonlite, int jsonlite_len, zval **value,
        jsonlite_decoder **decoder TSRMLS_DC) {
    zend_bool pass = 0;
    jsonlite_decoder *self = NULL;
    self = emalloc(sizeof(*self));
    memset(self, 0, sizeof(*self));

    self->jsonlite = jsonlite;
    self->index = 0;
    self->length = jsonlite_len;
    self->transactionIndex = 0;
    self->trace = NULL;

    MAKE_STD_ZVAL(self->trace);
    array_init(self->trace);


    self->stack_index = 0;

    pass = decode(self, value);

    *decoder = self;

    return pass;
}

PHPAPI void php_jsonlite_free(jsonlite_decoder *decoder TSRMLS_DC) {
    if (decoder) {
        zval_ptr_dtor(&decoder->trace);
        decoder->trace = NULL;

        efree(decoder);
    }
    decoder = NULL;
}


/* {{{ proto string jsonlite_decode(mixed value, [string type, [cast_as_map = 0]])
   Reads a line */
PHP_FUNCTION (jsonlite_decode) {
    zend_bool pass = 0;
    char *jsonlite = NULL;
    int length = 0;
    zval *value = NULL;
    jsonlite_decoder *decoder = NULL;

    if (zend_parse_parameters(ZEND_NUM_ARGS()TSRMLS_CC, "s",
            &jsonlite,
            &length
    ) == FAILURE) {
        WRONG_PARAM_COUNT;
    }

    pass = php_jsonlite_decode(jsonlite, length, &value, &decoder TSRMLS_CC);

    php_jsonlite_free(JSONLITE_G(decoder)/**/TSRMLS_CC);
    JSONLITE_G(decoder) = decoder;

    if (pass) {
        RETURN_ZVAL(value, 0, 1);
    } else {
        RETURN_NULL();
    }
}
/* }}} */

/* {{{ proto string jsonlite_get_trace([bool detail])
   Reads a line */
PHP_FUNCTION (jsonlite_get_trace) {
    zval *value = NULL;
    zend_bool detail = 0;
    jsonlite_decoder *decoder = JSONLITE_G(decoder);

    if (zend_parse_parameters(ZEND_NUM_ARGS()TSRMLS_CC, "|b",
            &detail
    ) == FAILURE) {
        WRONG_PARAM_COUNT;
    }

    if (decoder != NULL) {
        if (detail) {
            value = get_trace_detail(decoder);
        } else {
            value = get_trace(decoder);
        }

        RETURN_ZVAL(value, 0, 1);
    }
    RETURN_NULL();
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
