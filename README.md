## 介绍

JSONLite 是 JSON 的简化版。减少字符输出的同时，仍保持数据有效性。

建议PHP版本 >= 5.2.0 。

## 特性

* Js 兼容模式，兼容Js语法。取消了不必要的双引号。
* Strict 强类型模式，提供强类型输出与解析，可用于与强类型语言通讯。
  * 如 1.0 序列化和解序列后的类型均为 double，不会转换为 int 1。
* Min 最小化模式，最小化输出数据，可用于日志打印。
* 较为精确的错误位置和信息提示。
* 解析时更为显性的暴漏格式错误


## 实例

```php
require_once 'jsonlite.php';
$value = array(
	'code'   => '123',
	'msg'    => 'true str',
	'null'   => null,
	'new'    => '',
	'double' => 1.0,
);
// serialize
// js
$encoder = new JSONLiteEncoder($value);
echo $encoder->encode(), PHP_EOL;
// {code:"123",msg:"true str","null":null,"new":"",double:1}

// strict
$encoder = new JSONLiteEncoder($value, JSONLiteEncoder::TYPE_STRICT);
echo $encoder->encode(), PHP_EOL;
// {code:"123",msg:true str,"null":null,new:,double:1.0}

// min
$encoder = new JSONLiteEncoder($value, JSONLiteEncoder::TYPE_MIN);
echo $encoder->encode(), PHP_EOL;
// {code:123,msg:true str,"null":null,new:,double:1}

// unserialize
$jsonlite = '{code:123,msg:true str,"null":null,new:,double:1}';
$encoder = new JSONLiteDecoder($jsonlite);
var_export($encoder->decode());
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
### 尺寸比较

<table>
    <tr>
        <td>模式</td>
        <td>json</td>
        <td>jsonlite</td>
        <td>节约</td>
        <td>变化率</td>
    </tr>
    <tr><td>array_js</td><td>92</td><td>92</td><td>0</td><td> 0.00%</td></tr>
    <tr><td>array_strict</td><td>92</td><td>74</td><td>-18</td><td>19.57%</td></tr>
    <tr><td>array_min</td><td>92</td><td>70</td><td>-22</td><td>23.91%</td></tr>
    <tr><td>map_js</td><td>111</td><td>97</td><td>-14</td><td>12.61%</td></tr>
    <tr><td>map_strict</td><td>111</td><td>83</td><td>-28</td><td>25.23%</td></tr>
    <tr><td>map_min</td><td>111</td><td>81</td><td>-30</td><td>27.03%</td></tr>
</table>

## 版本

* 最后更新：2014-12-25
* 最新版本： 0.2

    
## 下载安装

```
    user$ git clone git://github.com/eixom/php-jsonlite.git
    user$ cd php-jsonlite
    user$ ~/your/php/bin/phpize
    user$ ./configure --with-php-config=~/your/php/bin/php-config
    user$ make
    user$ make install
```



## 联系

email: system128 at gmail dot com

qq: 59.43.59.0
