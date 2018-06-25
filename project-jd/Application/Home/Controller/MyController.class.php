<?php
namespace Home\Controller;

class MyController extends NavController{
    public function __construct(){
        parent::__construct();
        //判断是否登陆
        $user_id = session('m_id');
        if (!$user_id){
            session('returnUrl',U('my/'.ACTION_NAME));
            redirect(U('member/login'));
        }
    }
    //取出我的所有订单
    public function order(){
        $orderModel = D('order');
        $orderDate = $orderModel->search();

        //设置页面信息
        $this->assign(array(
            'orderDate' => $orderDate,
            '_show_nav' => 0,
            '_page_title' =>'个人中心-我的订单',
            '_page_keywords' => '个人中心',
            '_page_description' => '个人中心'
        ));
        $this->display();
    }
}
?>