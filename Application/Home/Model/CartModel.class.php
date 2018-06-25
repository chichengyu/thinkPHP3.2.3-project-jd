<?php
namespace Home\Model;
use Think\Model;
class CartModel extends Model{
    protected $insertFields = array('goods_id','goods_attr_id','goods_number');
    protected $updateFields = array('goods_id','goods_attr_id','goods_number');
    protected $_validate = array(
        array('goods_id','require','必须选择商品',1),
        array('goods_number','checkGoodsNumber','库存量不足',1,'callback')
    );
    /**** 添加购物车前验证库存量是否够 ****/
    // $goodsNumber 商品数量
    public function checkGoodsNumber($goodsNumber){
        //商品id
        $goods_id = I('post.goods_id');
        //商品属性id
        $goods_attr_id = I('post.goods_attr_id');
        sort($goods_attr_id,SORT_NUMERIC);//升序排序
        $goods_attr_id = (string)implode(',',$goods_attr_id);
        //查询库存量
        $goodsNumberModel = D('goods_number');
        $gn = $goodsNumberModel->field('goods_number')->where(array(
            'goods_id' => array('eq',$goods_id),
            'goods_attr_id_list' => array('eq',$goods_attr_id)
        ))->find();
        return $gn['goods_number'] > $goodsNumber;
    }

    /**** 重写父类add方法 ****/
    public function add(){
        //商品属性id
        $goods_attr_id = $this->goods_attr_id;
        sort($goods_attr_id,SORT_NUMERIC);//升序排序
        $goods_attr_id = (string)implode(',',$goods_attr_id);
        //获取购买数量
        $goods_number = $this->goods_number;//否则下面先查购物车表时的find方法会把当前模型中的数据覆盖掉,就获取不到购买数量了
        //获取用户id,判断是否登陆
        $user_id = session('m_id');
        if ($user_id){
            //先查购物车表中是否存在这些信息  根据 商品id 商品属性id 当前登陆用户id
            $has = $this->field('id')->where(array(
                'goods_id' => $this->goods_id,
                'goods_attr_id' => $goods_attr_id,
                'number_id' => $user_id
            ))->find();

            if ($has){
                //存在就在该条数据上进行累加购买数量
                $this->where(array(
                    'id' => $has['id']
                ))->setInc('goods_number',$goods_number);
            }else{
                /**** 如果不存在,就调用父类的add方法添加数据到数据库 ****/
                $arr = array(
                    'goods_id' => $this->goods_id,
                    'goods_attr_id' => $goods_attr_id,
                    'goods_number' => $goods_number,
                    'member_id' => $user_id
                );
                parent::add($arr);
            }
        }else{
            //获取cookie中存的数据
            $cart = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
            //用户没登陆,数据存cookie
            //格式： array('商品id-属性id_list' => 购买数量)
            $key = $this->goods_id.'-'.$goods_attr_id;
            //判断cookie中有没有这个值
            if ($cart[$key]){
                //有 就让购买数据累加
                $cart[$key] += $goods_number;
            }else{
                $cart[$key] = $goods_number;
            }

            //把一维数组存进cookie
            setcookie('cart',serialize($cart),time()+24*3600,'/');
        }
        return true;
    }

    /**** 登陆成功,将cookie中的购物车数据添加到数据库 ****/
    // 登陆成功的时候调用
    public function moveCart(){
        $user_id = session('m_id');
        if ($user_id){
            // 获取cookie中存的购物车信息
            $cart = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
            //循环cookie中每件商品添加到数据库
            foreach ($cart as $k => $v) {
                //获取cookie中存放的数组的下标
                //格式： array('商品id-属性id_list' => 购买数量)
                $k = explode('-',$k);//分割成数组

                //先查购物车表中是否存在这些信息  根据 商品id 商品属性id 当前登陆用户id
                $has = $this->field('id')->where(array(
                    'goods_id' => $k[0],
                    'goods_attr_id' => $k[1],
                    'number_id' => $user_id
                ))->find();
                //判断表中是否存在该条数据
                if ($has){
                    //存在就在该条数据上进行累加购买数量
                    $this->where(array(
                        'id' => $has['id']
                    ))->setInc('goods_number',$v);
                }else{
                    /**** 如果不存在,就调用父类的add方法添加数据到数据库 ****/
                    $arr = array(
                        'goods_id' => $k[0],
                        'goods_attr_id' => $k[1],
                        'goods_number' => $v,
                        'member_id' => $user_id
                    );
                    parent::add($arr);
                }
            }
            //添加完成,清空cookie
            setcookie('cart','',time(),'/');
        }
    }

    /**** 从数据库取出购物车详细信息 ****/
    public function cartList(){
        /****** 先从购物车取出商品id ******/
        $user_id = session('m_id');
        // 判断是否登陆
        if ($user_id){
            $cartDate = $this->where(array(
                'member_id' => array('eq',$user_id)
            ))->select();
        }else{
            //获取cookie中存放的每件购物车商品一维数组
            //格式： array('商品id-属性id_list' => 购买数量)
            $cart_Date = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
            //一维数组转二维,转成与购物车表取出的二维数组一样的结构
            $cartDate = array();
            foreach ($cart_Date as $k=>$v){
                $k = explode('-',$k);
                $cartDate[] = array(
                    'goods_id' =>$k[0],
                    'goods_attr_id' => $k[1],
                    'goods_number' => $v
                );
            }
        }
        /****** 再根据商品id取出商品详细信息 ******/
        //循环取出每件商品的详细信息
        $goodsModel = D('goods');
        $goodsAttrModel = D('goods_attr');
        foreach ($cartDate as $k=>$v) {
            //取出商品名称和logo
            $goodsInfo = $goodsModel->field('goods_name,mid_logo')
                ->where(array(
                'id' => array('eq',$v['goods_id'])
            ))->find();
            //再存回到这个二维数组中
            $cartDate[$k]['goods_name'] = $goodsInfo['goods_name'];
            $cartDate[$k]['mid_logo'] = $goodsInfo['mid_logo'];

            //计算商品实际的购买价格(昨天自定义封装好了这个方法)
            $cartDate[$k]['price'] = $goodsModel->getMemberPrice($v['goods_id']);

            //取出商品的属性
            if ($v['goods_attr_id']){
                $cartDate[$k]['goods_attr'] = $goodsAttrModel->field('a.attr_values,b.attr_name')
                    ->alias('a')
                    ->join('__ATTRIBUTE__ b ON a.attr_id=b.id')
                    ->where(array(
                        'a.id' => array('in',$v['goods_attr_id']),
                        'a.goods_id' => array('eq',$v['goods_id'])
                    ))->select();
            }
        }
        return  $cartDate;
    }

    /**** 清空购物车 ****/
    public function clear(){
        $this->where(array(
            'member_id' => array('eq',session('m_id'))
        ))->delete();
    }
}
?>