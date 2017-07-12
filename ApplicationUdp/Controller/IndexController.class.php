<?php
namespace Udp\Controller;
use Think\Controller;
class IndexController extends Controller\RestController {
    public function index($data = ''){
        $this->response("收到信息：$data", "html");
    }

}