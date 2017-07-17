<?php
namespace Udp\Controller;
use Think\Controller;
class IndexController extends Controller\RestController {
    public function index($ip = '', $data = ''){
        echo "收到信息：ip: $ip data: $data";


    }

}