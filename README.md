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
### 输出
<table>
    <tr>
        <td>JSON</td>
    </tr>
    <tr>
        <td>{"k":"v v","k1":{"k1":"ka"},"k2":{"k":"v v","k:":"v}","null":"v{","new":"false","1":null,"":"","n":1,"sn":"2"}}</td>
    </tr>
    <tr>
       <td>JSONLite Js兼容模式</td>
    </tr>
    <tr>
       <td>{k:"v v",k1:{k1:"ka"},k2:{k:"v v","k:":"v}","null":"v{","new":"false","1":null,"":"",n:1,sn:"2"}}</td>
    </tr>

    <tr>
       <td>JSONLite Strict强类型模式</td>
    </tr>
    <tr>
       <td>{k:v v,k1:{k1:ka},k2:{k:v v,"k:":"v}","null":"v{",new:"false",1:null,:,n:1,sn:"2"}}</td>
    </tr>

    <tr>
       <td>JSONLite Min最小化模式</td>
    </tr>
    <tr>
       <td>{k:v v,k1:{k1:ka},k2:{k:v v,"k:":"v}","null":"v{",new:"false",1:null,:,n:1,sn:2}}</td>
    </tr>
</table>

<table>
    <tr>
        <td>JSON</td>
    </tr>
    <tr>
        <td>["",1,2,"",["",1,2,"null test",null,"null","","new",""],"null test",null,"null","","new",""]</td>
    </tr>
    <tr>
       <td>JSONLite Js兼容模式</td>
    </tr>
    <tr>
       <td>["",1,2,"",["",1,2,"null test",null,"null","","new",""],"null test",null,"null","","new",""]</td>
    </tr>

    <tr>
       <td>JSONLite Strict强类型模式</td>
    </tr>
    <tr>
       <td>[,1.0,2,,[,1.0,2,null test,null,"null",,new,],null test,null,"null",,new,]</td>
    </tr>

    <tr>
       <td>JSONLite Min最小化模式</td>
    </tr>
    <tr>
       <td>[,1,2,,[,1,2,null test,null,"null",,new,],null test,null,"null",,new,]</td>
    </tr>
</table>

### 体积对比
根据测试数据计算，实际情况请另行估算。
<table>
    <tr>
        <td>模式</td>
        <td>JSON</td>
        <td>JSONLite</td>
        <td>变化量</td>
        <td>变化率</td>
    </tr>
    <tr><td>array_js</td><td>92</td><td>92</td><td>0</td><td> 0.00%</td></tr>
    <tr><td>array_strict</td><td>92</td><td>74</td><td>-18</td><td>19.57%</td></tr>
    <tr><td>array_min</td><td>92</td><td>70</td><td>-22</td><td>23.91%</td></tr>
    <tr><td>map_js</td><td>111</td><td>97</td><td>-14</td><td>12.61%</td></tr>
    <tr><td>map_strict</td><td>111</td><td>83</td><td>-28</td><td>25.23%</td></tr>
    <tr><td>map_min</td><td>111</td><td>81</td><td>-30</td><td>27.03%</td></tr>
</table>


## 版本信息

* 最后更新：2014-10-07
* 最新版本：0.1

    
## 下载

目前仅提供PHP版，请直接进入 php/ 目录进行下载。


## 联系作者

email: system128 at gmail dot com

qq: 59.43.59.0
