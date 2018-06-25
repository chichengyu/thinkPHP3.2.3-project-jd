<?php
namespace Home\Controller;

class IndexController extends NavController {
    /**** ajax实时获取当前会员价格 ****/
    public function ajaxGetMemberPrice(){
        $goods_id = I('get.goods_id');
        $goodsModel = D('goods');
        $goodsPrice = $goodsModel->getMemberPrice($goods_id);
        echo  $goodsPrice;
    }
    //cookie处理浏览历史
    public function displayHistory(){
        $goods_id = I('get.id');
        //先从cookie中取出浏览历史的ID数组
        $data = isset($_COOKIE['display_history'])?unserialize($_COOKIE['display_history']):array();
        //把最新浏览的这件商品放到数组中的第一个位置
        array_unshift($data,$goods_id);
        //数组去重
        $data = array_unique($data);
        //只取数组中的前6个
        if (count($data) > 6){
            $data = array_slice($data,0,6);
        }
        //存cookie
        setcookie('display_history',serialize($data),time()+30*86400,'/');
        //在根据商品id取出商品相关信息
        $goodsModel = D('goods');
        $data = implode(',',$data);
        $goodsDate = $goodsModel->field('id,goods_name,mid_logo')
            ->where(array(
            'id' => array('in',$data),
            'is_on_sale' => array('eq','是')
        ))
            ->order("field(id,$data)")
            ->select();
        echo json_encode($goodsDate);
    }
    public function index(){
        //测试加锁,防止雪崩情况
        //$file = uniqid();
        //file_put_contents('./piao/'.$file,'abc');

        // 1.取出疯狂抢购(促销)的商品数据
        $goods_promote = D('goods');
        $goods_promote_Date = $goods_promote->getPromoteGoods();
        // 获取热卖、新品、精品的商品数据
        $goods_hot_Date = $goods_promote->getRecGoods('is_hot');
        $goods_new_Date = $goods_promote->getRecGoods('is_new');
        $goods_best_Date = $goods_promote->getRecGoods('is_best');

        // 2.取首页楼层的分类数据
        $cateModel = D('Category');
        $floorDate = $cateModel->getFloorDate();

        $this->assign(array(
            'goodsPromote' => $goods_promote_Date,
            'goodsHot' => $goods_hot_Date,
            'goodsNew' => $goods_new_Date,
            'goodsBest' => $goods_best_Date,
            'floorDate' => $floorDate,
        ));

        //设置页面信息
        $this->assign(array(
            '_show_nav' => 1,
            '_page_title' =>'首页',
            '_page_keywords' => '首页',
            '_page_description' => '首页'
        ));

        $this->display();
    }
    public function goods(){
        //接收当前商品的id
        $goods_id = I('id');
        //根据商品id找到对应的商品分类id在找出分类    用于做当前位置的面包屑导航
        $goodsModel = D('goods');
        $goodsDate = $goodsModel->find($goods_id);
        $homeCateModel = D('category');
        $goodsCateDate = $homeCateModel->parentCateId($goodsDate['cate_id']);
        //将数组倒序排列
        $goodsCateDate = array_reverse($goodsCateDate);

        //取出相册图片
        $goodsPicModel = D('goods_pic');
        $goodsPicDate = $goodsPicModel->where(array(
            'gppds_id' => array('eq',$goods_id)
        ))->select();

        //取出商品的详细信息与属性(包括唯一与可选)
        $goodsAttrModel = D('goods_attr');
        $goodsAttrDate = $goodsAttrModel->field('a.*,b.attr_name,b.attr_type')
            ->alias('a')
            ->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')
            ->where(array(
            'a.goods_id' => array('eq',$goods_id)
        ))->select();
        //处理得到的属性数组
        $uniAttr = array();//唯一属性
        $mulAttr = array();//可选属性
        foreach ($goodsAttrDate as $k=>$v) {
            if ($v['attr_type'] == '唯一'){
                $uniAttr[] = $v;
            }else{
                //把同一个属性放到一起  三维
                $mulAttr[$v['attr_name']][] = $v;
            }
        }

        //取出该商品会员价格
        $memberPriceModel = D('member_price');
        $memberPriceDate = $memberPriceModel->field('a.price,b.level_name')
            ->alias('a')
            ->join('LEFT JOIN __MEMBER_LEVEL__ b ON a.level_id=b.id')
            ->where(array(
                'goods_id' => array('eq',$goods_id)
            ))
            ->select();

        //读取配置文件图片路径
        $config = C('IMAGE_CONFIG');
        $this->assign(array(
            'goodsPicDate' => $goodsPicDate,
            'goodsDate' => $goodsDate,
            'goodsCateDate' => $goodsCateDate,
            'viewPath' => $config['viewPath'],
            'uniAttr' => $uniAttr,
            'mulAttr' => $mulAttr,
            'memberPriceDate' => $memberPriceDate,
        ));

        //设置页面信息
        $this->assign(array(
            '_show_nav' => 0,
            '_page_title' =>'商品详情页',
            '_page_keywords' => '商品详情页',
            '_page_description' => '商品详情页'
        ));
        $this->display();
    }
    public function cateNav(){
        $Model = D('Category');
        $Model->getNavDate();
    }
}