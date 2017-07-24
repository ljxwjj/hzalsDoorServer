# hzalsDoorServer
访问地址
http://ip/door/api/usercenter/login

技术参考文件
http://document.thinkphp.cn/manual_3_2.html#insert_data

OneinStack
https://oneinstack.com/install/
service httpd {start|restart|stop}

ssh -l root 139.196.97.237
php workermanServer.php restart -d
tail -f log/20170718-udp.log       //查看UDP接收日志
tail -f log/stdout.log             //查看UDP输出日志
tail -f ApplicationRuntime/Logs/Api/17_07_18.log
tail -f ApplicationRuntime/Logs/Home/17_07_18.log
tail -f ApplicationRuntime/Logs/Udp/17_07_18.log

错误日志
