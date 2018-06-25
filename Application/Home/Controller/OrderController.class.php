<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends Controller{
    //接收支付宝支付成功发送的消息
    public function receive(){
        //接收并验证支付宝发来的消息
        require('./alipay/notify_url.php');
    }

    public function add(){
        //判断用户是否登陆
        $user_id = session('m_id');
        if (!$user_id){
            session('returnUrl',U('Order/add'));
            redirect(U('member/login'));
        }

        if (IS_POST){
            $orderModel = D('order');
            if ($orderModel->create(I('post.'),1)){
                if ($order_id = $orderModel->add()){
                    $this->success('下单成功！',U('order_success?order_id='.$order_id));
                    exit;
                }
            }
            $this->error('下单失败！'.$orderModel->getError());
        }

        //获取购物车信息
        $cartModel = D('cart');
        $cartDate = $cartModel->cartList();

        //设置页面信息
        $this->assign(array(
            'cartDate' => $cartDate,
            '_page_title' =>'订单确定页',
            '_page_keywords' => '订单确定页',
            '_page_description' => '订单确定页'
        ));
        $this->display();
    }
    //下单成功
    public function order_success(){
        //生成支付宝支付的按钮
        $aliplayBtn = makeAlipayBtn(I('get.order_id'));

        //设置页面信息
        $this->assign(array(
            'aliplayBtn' => $aliplayBtn,
            '_page_title' =>'下单成功',
            '_page_keywords' => '下单成功',
            '_page_description' => '下单成功'
        ));
        $this->display();
    }
}
?>