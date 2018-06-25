<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller{
    // ajax实时请求用户登陆
    public function ajaxlogin(){
        if (session('m_id')){
            echo json_encode(array(
                'login' => 1,
                'username' => session('m_username')
            ));
        }else{
            echo json_encode(array(
                'login' => 0
            ));
        }
    }
    //注册
    public function regist(){
        if (IS_POST){
            $memberModel = D('member');
            if ($memberModel->create(I('post.'),1)){
                if (false !== $memberModel->add()){
                    $this->success('注册成功！',U('login'));
                    exit;
                }
            }
            $this->error($memberModel->getError());
        }
        $this->display();
    }
    //登陆
    public function login(){
        if (IS_POST){
            $memberModel = D('Member');
            //接收表单并验证
            if ($memberModel->validate($memberModel->_login_validate)->create()){
                if ($memberModel->login()){
                    //登陆成功,跳转
                    //默认跳转地址
                    $url = U('/');
                    if (session('returnUrl')){
                        $url = session('returnUrl');
                        session('returnUrl',null);
                    }

                    $this->success('登陆成功！'.session('m_username'),$url);
                    exit;
                }
            }
            $this->error($memberModel->getError());
        }
        $this->display();
    }
    //退出
    public function logout(){
        $memberModel = D('Member');
        $memberModel->logout();
        redirect('login');
    }
    //验证码
    public function verify(){
        $config = array(
            'imageW' => 150,
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