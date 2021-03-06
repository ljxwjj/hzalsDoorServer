#阿里桑门禁系统API文档

接口地址：http://api.hzals.com/door/index.php

管理后台地址：http://admin.hzals.com/door/index.php

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

* 发送短信验证码 /Public/sendSMS
    * 参数
        * mobile
        * operation 【register | findPassword】
    * 返回值
        * data        null

* 生成二维码 /Public/qrcode
    * 参数
        * text
    * 返回值
        * image file  stream

* 添加报修 /Public/addRepair

    * 参数
        * company_name  公司名称
        * phone         联系电话
        * address       地址
        * describe_text 故障描述
        * image_file    图片
    
    * 返回值
        * data        成功/失败
        
        
##提供第三方接口

* 生成开门二维码 /SmartDoor/qrcodeBySecret
    * 参数
        * serial_number   门禁序列号
        * door_id         门编号
        * secret_key      用户秘钥
        * validity_time   二维码有效期（秒）
    * 返回值
        * image file  stream
        
#用户授权接口
公共参数： user_id、 account、 token、sign

##门禁管理

* 门禁列表 /DoorController/lists

    * 参数
        * search\_company_id (可选)  公司ID
        * page  (可选)                         页数（从1开始）
    * 返回值
        * data        array[DoorController]
        
* 门列表 /DoorController/doorLists

    * 参数
        * page  (可选)                         页数（从1开始）
    * 返回值
        * data        array[Door]
        

>_search\_company\_id not null（当前用户拥有管理页限）,可获得该公司下所有控制器列表_    
>_csearch\_ompany\_id is  null 可获得当前用户所在公司下所有控制器列表_

* 门禁详情 /DoorController/detail

    * 参数 
        * door\_controller\_id   门禁ID
    * 返回值
        * data        DoorController

* 添加门禁 /DoorController/add

    * 参数 
        * door[name...]   门禁信息
    * 返回值
        * data        null

* 删除门禁 /DoorController/del

    * 参数 
        * door\_controller\_id   门禁ID
    * 返回值
        * data        null

* 修改门禁 /DoorController/edit

    * 参数 
        * door[name...]   门禁信息
    * 返回值
        * data        null
        
* 开门 /DoorController/openDoor

    * 参数 
        * controller\_id   控制器ID
        * door\_id         门ID
    * 返回值
        * data        id: 开门请求标识

* 开门(延时3秒关门) /DoorController/openDoorBySecret

    * 参数 
        * serial\_number   控制器ID
        * door\_id         门ID
        * secret\_key      用户秘钥
    * 返回值
        * data        id: 开门请求标识
        
* 开门(不延时关门) /DoorController/openDoorKeepBySecret

    * 参数 
        * serial\_number   控制器ID
        * door\_id         门ID
        * secret\_key      用户秘钥
    * 返回值
        * data        id: 开门请求标识
        
>返回值 code    
>   0： 开门请求失败    
>   201： 一代门开门成功    
>   200： 二代门请求开门 data中将包含id   
        
* 开门反馈 /DoorController/openDoorFeedBack

    * 参数 
        * id          开门请求标识
    * 返回值
        * data        code 200：开门成功 1：等待门禁响应  0：非法请求
             
* 开门反馈 /DoorController/openDoorFeedBackBySecret

    * 参数 
        * id          开门请求标识
        * secret\_key      用户秘钥
    * 返回值
        * data        code 200：开门成功 1：等待门禁响应  0：非法请求
        
* 关门 /DoorController/closeDoorBySecret

    * 参数 
        * serial\_number   控制器ID
        * door\_id         门ID
        * secret\_key      用户秘钥
    * 返回值
        * data        id: 关门请求标识
        
* 关门反馈 /DoorController/closeDoorFeedBackBySecret

    * 参数 
        * id          关门请求标识
        * secret\_key      用户秘钥
    * 返回值
        * data        code 200：关门成功 1：等待门禁响应  0：非法请求
        
* 获取摄像头列表 /DoorController/cameras

    * 参数 
        * controller_id        
        * door_id             
    * 返回值
        * data        array[Camera]
        
* 获取所有摄像头列表 /DoorController/allCameras

    * 参数 
        * page  (可选)                         页数（从1开始）
        * company_id (可选)                    公司ID
    * 返回值
        * data        array[Camera]
        
* 获取门状态 /DoorController/getDoorStatus

    * 参数 
        * controller\_id   控制器ID
    * 返回值
        * code        200：查询成功 1：离线  0：获取超时
        * data        array[String]   0：关 1：开
        
* 获取门状态 /DoorController/getOpenedDoors

    * 参数 
        * controller\_id   控制器ID
    * 返回值
        * code        200：查询成功 1：离线  0：获取超时
        * data        array[Door]
        
                
##个人信息 

* 个人信息  /Ucenter/detail

    * 参数
        * null           无
    * 返回值
        * data        User

* 二维码 /Ucenter/qrCode

    * 参数
         * null           无
     * 返回值
         * data        二维码链接

* 分享开门二维码 /Ucenter/shareQRCode

    * 参数
         * validity_time           二维码有效期（秒）[300|600|1200]
         * controller_id  (可选)   授权控制器ID
         * door_id  (可选)      授权门ID
    * 返回值
         * data        二维码链接

* 我的授权  /Ucenter/authAccess

    * 参数
        * null           无
    * 返回值
        * data        Access

* 修改密码 /Ucenter/modifyPassword

    * 参数
        * password           原密码
        * new_password       新密码
    * 返回值
        * data        null
        
* 编辑个人信息 /Ucenter/edit
    
    * 参数
        * head_image    头像文件
    * 返回值
        * data        null
        
* 注册极光推送 /Ucenter/jpushRegisterId

    * 参数
        * register_id 极光推送的用户标识
        * device_type string:"android"|"ios"
        
* 退出登录 /Ucenter/logout

    * 参数
        * null           无
    * 返回值
        * data        null
        
* 闪屏页图片 /Ucenter/splashImage
                    
    * 参数 无
    
    * 返回值
        * data        image_url
        
##用户管理

* 用户列表 /User/lists

    * 参数
        * search\_company\_id (可选)  公司ID
        * page  (可选)                         页数（从1开始）
    * 返回值
        * data        array[User]
   
> _search\_company\_id not null（当前用户拥有管理页限）,可获得该公司下所有用户列表_   
> _search\_company\_id is  null 可获得当前用户所在公司下所有用户列表_

* 用户详情 /User/detail

    * 参数
        * id     用户ID
    * 返回值
        * data        User

* 用户编辑 /User/edit

    * 参数
        * user[id, ...]     用户信息
    * 返回值
        * null

* 用户离职   /User/forbid

    * 参数
        * id     用户ID
    * 返回值
        * null

* 恢复离职用户   /User/resume

    * 参数
        * id     用户ID
    * 返回值
        * null

* 删除离职用户 /User/del

    * 参数
        * id     用户ID
    * 返回值
        * null
        
##出入记录

* 用户出入记录 /OpenRecord/lists

    * 参数
        * search\_user_id (可选)             用户ID
        * search\_door\_controller\_id (可选)  控制器ID
        * search\_door\_id (可选)              门编号
        * search\_open\_time\_start (可选)     开门时间
        * search\_open\_time\_end (可选)       开门时间
        * page  (可选)                         页数（从1开始）
    * 返回值
        * data        array[OpenRecord]
   
> _search\_user\_id not null（当前用户拥有管理页限）,可获得指定用户的出入记录_   
> _search\_user\_id is  null 可获得当前用户的出入记录_

* 考勤列表 /OpenRecord/attendance

    * 参数
        * search\_user\_ids (可选)       用户ID
        * search\_time\_start            起始时间
        * search\_time\_end              结束时间
    * 返回值
        * data        array[Attendance]
        
> search\_user\_ids is null 查询公司所有人的考勤记录

* 员工考勤详情 /OpenRecord/attendanceDetail

    * 参数
        * id                             用户ID
        * search\_time\_start            起始时间
        * search\_time\_end              结束时间
    * 返回值
        * data        array[Attendance]

##APP配置

* 闪屏页图片 /AppSetting/splashImage
    
    * 参数 无
    
    * 返回值
        * data        image_url
        
* 轮播页图片 /AppSetting/lunboImages
    
    * 参数 无
    
    * 返回值
        * data        array[image_url]
        
* 检查版本更新 /AppSetting/checkNewVersion
    
    * 参数 
        * version    当前版本号
    
    * 返回值
        * data        ApkVersion

##推送消息历史

* 推送列表 /PushRecord/lists

    * 参数
        * page  页数
        
    * 返回值
        * data  array[PushRecord]

##系统报修

* 添加报修 /RepairRecord/add

    * 参数
        * company_name  公司名称
        * phone         联系电话
        * address       地址
        * describe_text 故障描述
        * image_file    图片
    
    * 返回值
        * data        成功/失败

#sign加密算法

* 准备好所有post参数构成的键值对集合 dictionary
* 对dictionary按key名进行升序排序
* 按顺序取出dictionary中的值拼接成一个字符串 paramsStr
* 把paramsStr倒序、再拼接上一个约定字符串
* 最后把拼接好的字符串md5生成最后的sign




