<?php
/**
 * Created by PhpStorm.
 * User: 小池
 * Date: 2018/5/10
 * Time: 12:41
 */

namespace Admin\Controller;
use Think\Controller;

class LoginController extends Controller{

    /**** 登陆****/
    public function login(){
        /*** 登陆验证 ***/
        if (IS_POST){
            $adminModel = D('admin');
            //接收表单并验证
            if ($adminModel->validate($adminModel->_login_validate)->create()){
                if ($adminModel->login()){
                    $this->success('登陆成功！',U('Index/index'));
                    exit;
                }
            }
            $this->error($adminModel->getError());
        }
        $this->display();
    }
    /**** 退出登陆****/
    public function logout(){
        $adminModel = D('admin');
        $adminModel->logout();
        redirect('login');
    }
    //验证码
    public function verify(){
        $config = array(
            'imageW' => 146,
            'imageH' => 40,
            'fontSize' =>22,
            'length' => 4,
            'useCurve' => false,
        );
        $Verify = new \Think\Verify($config);
        // 开启验证码背景图片功能 随机使用 ThinkPHP/Library/Think/Verify/bgs 目录下面的图片
        //$Verify->useImgBg = true;
        $Verify->entry();
    }

}
?>