<?php
namespace Demo\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->display();
    }

    public function add() {
        $this->display();
    }

    public function lists() {
        $this->display();
    }
}