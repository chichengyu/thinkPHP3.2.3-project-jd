<?php
namespace Home\Controller;
use Think\Controller;
class NavController extends Controller {
    public function __construct()
    {
        //先调用父类的构造函数
        parent::__construct();
        $NavModel = D('category');
        $NavDate = $NavModel->getNavDate();
        $this->assign('cateDate',$NavDate);
    }
}