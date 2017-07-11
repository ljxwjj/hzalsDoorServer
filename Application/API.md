#阿里桑门禁系统API文档

接口地址：http://http://139.196.97.237/door/api.php

管理后台地址：http://http://139.196.97.237/door/index.php

帐号：18957125011   密码：123456 

接口分类：用户授权接口、非用户授权接口

接口验证方式：

* 用户授权接口： token + sign
* 非用户授权接口： sign
* 请求方式：post
* token 为登录成功后返回
* sign 是把post参数通过sign加密算法计算，获得的一个加密字符串

接口响应方式：

* 响应格式：json
* 通用响应结构
    ```javascript
      {
        code  : 200,
        message : "操作成功",
        data : {name: "张三"}
      }
    ```
* code: 200 （成功）、0（失败）、1、2、3（参见接口详情）
* data: 接口获取的数据都放在这里

#非用户授权接口

* 登录 /public/login
    * 参数
        * account     帐号
        * password    密码
    * 反回值
        * data        User
        * User        id、token、account、company、email、mobile、nickname、sex

* 注册 /Public/register
    * 参数
        * account
        * password
        * sms_code 短信验证码
    * 返回值
        * data        null
        
* 找回密码 /Public/findPassword
    * 参数
        * account
        * password
        * sms_code
    * 返回值
        * data        null

* 发送短信验证码 ／Public/sendSMS
    * 参数
        * mobile
        * operation 【register | findPassword】
    * 返回值
        * data        null

#用户授权接口
公共参数： token、sign

##门禁管理

* 门禁列表

* 门禁详情

##个人信息

* 个人信息

* 修改密码

##用户管理

* 用户列表

#sign加密算法

* 准备好所有post参数构成的键值对集合 dictionary
* 对dictionary按key名进行升序排序
* 按顺序取出dictionary中的值拼接成一个字符串 paramsStr
* 把paramsStr倒序、再拼接上一个约定字符串
* 最后把拼接好的字符串md5生成最后的sign




