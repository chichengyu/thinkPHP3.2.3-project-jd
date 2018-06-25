<?php
namespace Home\Controller;
use Think\Controller;
class CartController extends Controller{
    public function add(){
        if (IS_POST){
            $cartModel = D('cart');
            if ($cartModel->create(I('post.'),1)){
                if ($cartModel->add()){
                    $this->success('添加成功！',U('lst'));
                    exit;
                }
            }
            $this->error('添加失败！原因：'.$cartModel->getError());
        }
    }
    public function lst(){

        //获取购物车信息
        $cartModel = D('cart');
        $cartDate = $cartModel->cartList();

        $this->assign(array(
            'cartDate' => $cartDate,
        ));

        //设置页面信息
        $this->assign(array(
            '_page_title' =>'购物车列表',
            '_page_keywords' => '购物车列表',
            '_page_description' => '购物车列表'
        ));
       $this->display();
    }

    // ajax实时首页请求购物车列表
    public function ajaxCart(){
        //获取购物车信息
        $cartModel = D('cart');
        $cartDate = $cartModel->cartList();
        echo json_encode($cartDate);
    }

    //
}
?>