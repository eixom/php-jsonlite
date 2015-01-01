
JSONLite is a lite version of JSON.

PHP 5.2 or later

[![Build Status](https://secure.travis-ci.org/eixom/php-jsonlite.png)](http://travis-ci.org/eixom/php-jsonlite)

## feature

* encode 
    * mode : js. Compatible with javascript 
    * mode : strict. Keep the data type
        * ex. 1.0 will be encode as "1.0"(without quote),and decode as 1.0 
    * encode mode : min. Reduce the data size, which is useful for logs.
* decode : Compatible with JSON
* Better error position brief and description
* Make errors more explicit


## examples

```php
$value = array(
	'code'   => '123',
	'msg'    => 'true str',
	'null'   => null,
	'new'    => '',
	'double' => 1.0,
);
// serialize
// js
echo jsonlite_encode($value);
// {code:123,msg:"true str","null":0,"new":"",double:1}

// min
echo jsonlite_encode($value, JSONLITE_MODE_MIN);
// {code:123,msg:true str,"null":,new:,double:1}

// strict
echo jsonlite_encode($value, JSONLITE_MODE_STRICT);
// {code:"123",msg:true str,"null":null,new:,double:1.0}


// unserialize
$jsonlite = '{code:123,msg:true str,"null":null,new:,double:1}';
$value = jsonlite_decode($jsonlite);
var_export($value);
/**
 * array (
 *     'code' => 123,
 *     'msg' => 'true str',
 *     'null' => NULL,
 *     'new' => '',
 *     'double' => 1,
 * )
 */
 
// work with json
$value = array(
	'code'   => '123',
	'msg'    => 'true str',
	'null'   => null,
	'new'    => '',
	'double' => 1.0,
);

$json = json_encode($value); // ATTENTION:encode with json
// {"code":"123","msg":"true str","null":null,"new":"","double":1}
$value = jsonlite_decode($json);
var_export($value);
/**
  * array (
  *     'code' => 123,
  *     'msg' => 'true str',
  *     'null' => NULL,
  *     'new' => '',
  *     'double' => 1,
  * )
  */
```


## version

* latest update: 2014-12-25
* latest version: 0.2

    
## install

```
    user$ git clone git://github.com/eixom/php-jsonlite.git
    user$ cd php-jsonlite
    user$ ~/your/php/bin/phpize
    user$ ./configure --with-php-config=~/your/php/bin/php-config
    user$ make
    user$ make install
```

### size

<table>
    <tr>
        <td>mode</td>
        <td>json</td>
        <td>jsonlite</td>
        <td>saving</td>
        <td>rate</td>
    </tr>
    <tr><td>array_js</td><td>92</td><td>92</td><td>0</td><td> 0.00%</td></tr>
    <tr><td>array_strict</td><td>92</td><td>74</td><td>-18</td><td>19.57%</td></tr>
    <tr><td>array_min</td><td>92</td><td>70</td><td>-22</td><td>23.91%</td></tr>
    <tr><td>map_js</td><td>111</td><td>97</td><td>-14</td><td>12.61%</td></tr>
    <tr><td>map_strict</td><td>111</td><td>83</td><td>-28</td><td>25.23%</td></tr>
    <tr><td>map_min</td><td>111</td><td>81</td><td>-30</td><td>27.03%</td></tr>
</table>

## forms

### map/object
<img src="https://raw.githubusercontent.com/eixom/php-jsonlite/master/diagram/map_or_object.png" />

### array
<img src="https://raw.githubusercontent.com/eixom/php-jsonlite/master/diagram/array.png" />

### value
<img src="https://raw.githubusercontent.com/eixom/php-jsonlite/master/diagram/value.png" />

### number
<img src="https://raw.githubusercontent.com/eixom/php-jsonlite/master/diagram/number.png" />

### string
<img src="https://raw.githubusercontent.com/eixom/php-jsonlite/master/diagram/string.png" />



## contact

email: system128/at/gmail/dot/com