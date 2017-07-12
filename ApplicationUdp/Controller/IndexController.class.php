<?php
namespace Udp\Controller;
use Think\Controller;
class IndexController extends Controller\RestController {
    public function index($data = ''){
        echo "收到信息：$data";
    }

}