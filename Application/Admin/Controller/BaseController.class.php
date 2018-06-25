<?php
/**
 * Created by PhpStorm.
 * User: 小池
 * Date: 2018/5/10
 * Time: 19:04
 */
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller{
    public function __construct()
    {
        //必须先调用父类的构造方法(进行初始化)
        parent::__construct();
        //判断登陆
        if (!session('id')){
            $this->error('必须先登陆后台！',U('login/login'));
        }
        //查询管理员权限,chkPri方法在privilege权限模型中
        //所有管理员都可以进入后台的首页
        if (CONTROLLER_NAME == 'Index'){
            return true;
        }
        $priModel = D('privilege');
        if (!$priModel->chkPri()){
            $this->error('无权访问！');
        }
    }
}
?>