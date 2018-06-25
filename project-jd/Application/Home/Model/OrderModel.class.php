<?php
namespace Home\Model;
use Think\Model;

class OrderModel extends Model{
    protected $insertFields = array('shr_name','shr_tel','shr_province','shr_city','shr_area','shr_address');
    protected $updateFields = array('shr_name','shr_tel','shr_province','shr_city','shr_area','shr_address');
    protected $_validate = array(
        array('shr_name','require','收货人姓名不能为空',1,'regex',3),
        array('shr_tel','require','收货人电话不能为空',1,'regex',3),
        array('shr_province','require','收货人所在省不能为空',1,'regex',3),
        array('shr_city','require','收货人所在城市不能为空',1,'regex',3),
        array('shr_area','require','收货人所在地区不能为空',1,'regex',3),
        array('shr_address','require','收货人所在详细地址不能为空',1,'regex',3),
    );

    /**** 分页取出当前用户所有订单数据 ****/
    public function search($pageSize = 5){
        $user_id = session('m_id');
        /**** 分页取出当前用户所有订单数据 ****/
        $where['a.member_id'] = array('eq',$user_id);
        $count = $this->alias('a')->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count,$pageSize);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $show = $Page->show();// 分页显示输出
        $list = $this->field('a.id,a.shr_name,a.total_price,a.addtime,a.pay_status,b.goods_id,c.sm_logo')
            ->alias('a')
            ->join('LEFT JOIN __ORDER_GOODS__ b ON a.id=b.order_id
                    LEFT JOIN __GOODS__ c ON b.goods_id=c.id')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        //统计未支付的订单数
        $noPayCount = $this->where(array(
            'member_id' => array('eq',$user_id),
            'pay_status' => array('eq','否')
        ))->count();

        $data = array(
            'show' => $show,
            'list' => $list,
            'noPayCount' => $noPayCount
        );

        return $data;
    }

    /**** 下订单之前 数据处理 数据添加到订单表****/
    public function _before_insert(&$data,&$option){
        /************** 下单的检查 ***************/
        //判断用户是否登陆
        $user_id = session('m_id');
        if (!$user_id){
            $this->errorr = '请先登陆！';
            return false;
        }

        //判断购物车中是否有的商品
        $cartModel = D('cart');
        //把购物车中的数据取出来并存到参数 &$option 中,但 $option 必须是引用传递
        $option['goods'] = $cartDate = $cartModel->cartList();
        if (!$cartDate){
            $this->error = '还没有选择商品！请先添加商品到购物车！';
            return false;
        }

        /**** 加锁【下单之前,读库存时加锁,这个锁一直持续到下单结束后才释放,防止高并发】 ****/
        $this->tp = fopen('./order.lock');
        flock($this->tp,LOCK_EX);

        //循环检查购物车中的商品库存量是否够并计算总价
        $goodsNumberModel = D('goods_number');
        $total_price = 0;//总价
        foreach ($cartDate as $k=>$v) {
            $goodsNumber = $goodsNumberModel->field('goods_number')
                ->where(array(
                'goods_id' => array('eq',$v['goods_id']),
                'goods_attr_id' => array('eq',$v['goods_attr_id'])
            ))->find();
            //检查库存量
            if ($goodsNumber['goods_number'] < $v['goods_number']){
                $this->error = "商品<strong>".$v['goods_name']."</strong>库存量不够！无法下单";
                return false;
            }
            //计算总价
            $total_price +=  $v['price']*$v['goods_number'];
        }

        $data['total_price'] = $total_price;
        $data['member_id'] = $user_id;
        $data['addtime'] = time();


        //【为了确定数据完整性,就是三张表的数据都能操作成功,订单基本信息表,订单商品表,库存量表】
        //tp框架 开启一个事物
        $this->startTrans();
    }

    /**** 下订单之后 数据添加到订单商品表****/
    public function _after_insert($data,$option){
        $orderGoddsModel = D('order_goods');
        $goodsNumberModel = D('goods_number');
        //取出购物车中的商品信息,循环插入到订单商品表中
        //并减库存
        foreach ($option['goods'] as $k=>$v){
            //插入数据
            $ret = $orderGoddsModel->add(array(
                'order_id' => $data['id'],
                'goods_id' => $v['goods_id'],
                'goods_attr_id' => $v['goods_attr_id'],
                'goods_number' => $v['goods_number']
            ));
            //只要有一个插入错误,就回滚整个事务
            if (!$ret){
                $this->rollback();
                return false;
            }

            //减库存
            $ret = $goodsNumberModel->where(array(
                'goods_id' => $v['goods_id'],
                'goods_attr_id_list' => $v['goods_attr_id'],
            ))->setDec('goods_number',$v['goods_number']);
            //只要有一个减库存错误,就回滚整个事务
            if (false === $ret){
                $this->rollback();
                return false;
            }
        }

        //所有操作成功后,提交事务
        $this->commit();

        //释放锁
        flock($this->tp,LOCK_UN);
        fclose($this->tp);

        //清空购物车
        $cartModel = D('cart');
        $cartModel->clear();
    }

    /**** 支付宝支付成功后,更新数据表的状态与用户积分 ****/
    public function setPaid($orderId){
        //支付成功后,会发送一个订单id  $orderId
        /***** 更新订单的支付状态 ****/
        $this->where(array(
            'id' => array('eq',$orderId)
        ))->save(array(
            'pay_status' => '是',
            'pay_time' => time()
        ));

        /***** 更新会员积分 ****/
        $tp = $this->field('member_id,total_price')->find($orderId);
        /*注意：
         * 由于此处更新数据,如果使用D函数new模型会调用钩子函数_before_update,而我们不需要调用,则使用M函数,则不会调用钩子函数
         * */
        $memberModel = M('member');
        $memberModel->where(array(
            'id' => array('eq',$tp['member_id'])
        ))->setInc('face',$tp['total_price']);
    }
}
?>