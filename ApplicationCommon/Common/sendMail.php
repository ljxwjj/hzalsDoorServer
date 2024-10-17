<?php
    Vendor('PHPMailer/class#phpmailer', COMMON_PATH . 'Vendor/', '.php');
    Vendor('PHPMailer/class#smtp', COMMON_PATH . 'Vendor/', '.php');
    header('Content-Type: text/html; charset=utf-8');

    function send_mail($tomail,$subject,$body,$ccmail) {   
        $mail = new PHPMailer();
        //$mail->Encoding = "base64"; 
        $mail->IsSMTP();					// 启用SMTP
        $mail->Host = C('MAIL_SMTP');			//SMTP服务器
        //$mail->SMTPSecure = "ssl";              // 目前规定必须使用ssl，非ssl已不支持了
        //$mail->Port = 465;                      // 端口号
        //$mail->SMTPDebug = 2;                   // 用于debug HTTPMailer信息
        $mail->SMTPAuth = true;					//开启SMTP认证
        $mail->Username = C('MAIL_LOGINNAME');			// SMTP用户名
        $mail->Password = C('MAIL_PASSWORD');			// SMTP密码
        
        $mail->From = C('MAIL_ADDRESS');			//发件人地址
        $mail->FromName = C('MAIL_SENDER');				//发件人别名
        $mail->AddAddress($tomail);	//添加收件人
        if(!empty($ccmail)){
            $mail->AddCC($ccmail);//抄送
        }
        //$mail->AddAddress("wlq.1203@163.com");//添加收件人,
        //$mail->AddReplyTo("邮件回复人地址", "邮件回复人名称");	//设置回复的收件人的地址
        $mail->WordWrap = 50;					//设置每行字符长度
        /** 附件设置
        $mail->AddAttachment("/var/tmp/file.tar.gz");		// 添加附件
        $mail->AddAttachment("/tmp/image.jpg", "new.jpg");	// 添加附件,并指定名称
        */
        $mail->IsHTML(true);					// 是否HTML格式邮件
        $mail->CharSet = "utf-8";// 这里指定字符集！
        
        $mail->Subject = $subject;			//邮件主题
        //$mail->MsgHTML('测试中文！哈哈！');                         // 设置邮件内容
        $mail->Body    = $body;		//邮件内容
        //$mail->AltBody = "This is the body in plain text for non-HTML mail clients";	//邮件正文不支持HTML的备用显示
        $info = '';
        if(!$mail->Send()) {
            $info = $mail->ErrorInfo;
        }else {
            $info = 1 .'';//邮件发送成功
        }
        return $info;
    } 
    //$result = send_mail('wlq.1203@163.com','775232737@qq.com','Here is the subject','测试中文！哈哈！','wlq.1203@163.com');
    //print_r($result);
	
	/**
	 * 发送email通知 *
	 *
	 */
	/*
	public function sendEmailMessage(){	
		load ( "@.sendMail" );
		if($this->receiverEmail){ //参数无效 异常处理			
			foreach($this->receiverEmail as $email){			
				$result = send_mail($email,$this->title,$this->content,'');
			}	
		}			
	}
	*/
?>  