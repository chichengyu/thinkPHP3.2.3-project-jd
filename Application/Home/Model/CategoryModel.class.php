<?php
namespace Home\Model;
use Think\Model;

class CategoryModel extends Model{

    /**** 递归取出一个分类的所有上级分类 ****/
    public function parentCateId($cateId){
        static $res = array();
        $info = $this->field('id,cate_name,parent_id')->find($cateId);
        $res[] = $info;
        //判断当前分类是否有父级分类,有就递归查询父级分类
        if ($info['parent_id'] > 0){
            $this->parentCateId($info['parent_id']);
        }
        return $res;
    }

    /**** 获取导航条的数据 ****/
    public function getNavDate(){
        //获取缓存
        $cateDate = S('cateDate');
        if (!$cateDate){
            //取出所有分类
            $cateAllDate = $this->select();
            $res = array();
            //循环找出所有顶级分类
            foreach ($cateAllDate as $v){
                if ($v['parent_id'] == 0){
                    //循环找出所有顶级分类的子级分类
                    foreach ($cateAllDate as $v1){
                        if ($v1['parent_id'] == $v['id']){
                            //循环找出二级分类的子分类
                            foreach ($cateAllDate as $v2){
                                if ($v2['parent_id'] == $v1['id']){
                                    $v1['children'][] = $v2;
                                }
                            }
                            $v['children'][] = $v1;
                        }
                    }
                    $res[] = $v;
                }
            }
            //设置缓存
            S('cateDate',$res,86400);
            return $res;
        }else{
            return $cateDate;
        }
    }

    /**** 获取前台楼层的分类数据 ****/
    public function getFloorDate(){
        //获取缓存
        $floor = S('floor');
        if (!$floor){
            //取出推荐的顶级分类
            $floorDate = $this->where(array(
                'parent_id' => array('eq',0),
                'is_floor' => array('eq','是')
            ))->select();
            //调用后台模型的方法
            //返回的是当前被推荐的二级分类及其下的子级分类和扩展分类的商品分类id
            $adminGoodsModel = D('Admin/Goods');
            $homeGoodsModel = D('goods');

            //循环取出每个推荐的顶级分类的【未被】推荐的子级分类
            foreach ($floorDate as $k=>$v){
                //循环取出每个推荐的顶级分类下的未被推荐的子(二)级分类
                $floorDate[$k]['children'] = $this->where(array(
                    'parent_id' => array('eq',$v['id']),
                    'is_floor' => array('eq','否')
                ))->select();
                //循环取出每个推荐的顶级分类下的【被】推荐的子(二)级分类
                $floorDate[$k]['recchildren'] = $this->where(array(
                    'parent_id' => array('eq',$v['id']),
                    'is_floor' => array('eq','是')
                ))->select();
                //循环每个推荐的二级分类下的8件被推荐到楼层的商品
                /***** 【取出一个分类下所有商品的id，即考虑主分类也考虑扩展分类】 *****/
                //利用后台封装的取出分类子类和扩展分类的函数来取出这些分类下的商品
                foreach ($floorDate[$k]['recchildren'] as $k1=>$v1){
                    //取出这个分类下所有商品的id并返回一一维数组
                    $goods_id = $adminGoodsModel->getGoodsIdByCateId($v1['id']);

                    //根据商品id取出对应的推荐商品
                    $floorDate[$k]['recchildren'][$k1]['goods'] = $homeGoodsModel->field('id,goods_name,mid_logo,shop_price')
                        ->where(array(
                        'is_on_sale' => array('eq','是'),
                        'is_floor' => array('eq','是'),
                        'id' => array('IN',implode(',',$goods_id)),
                    ))
                        ->order('sort_number ASC')
                        ->limit(8)
                        ->select();
                }
                /**** 取分类下的所有商品品牌 ****/
                //拿到分类下的所有商品
                $goodsId = $adminGoodsModel->getGoodsIdByCateId($v['id']);

                $floorDate[$k]['brand'] = $homeGoodsModel->field('distinct a.brand_id,b.brand_name,b.logo')
                    ->alias('a')
                    ->join('LEFT JOIN __BRAND__ b ON a.brand_id=b.id')
                    ->where(array(
                    'a.id' => array('eq',implode(',',$goodsId)),
                    'a.brand_id' => array('neq',0)
                ))->select();
            }
            //设置缓存
            S('floor',$floorDate,36000);
            return $floorDate;
        }else{
            return $floor;
        }
    }

    /**** 根据当前搜索出来的商品【来计算取出筛选条件 ****/
    //【下面cateSearchGoods方法返回的goods_id】
    public function getCateidSearchGoods($goods_id){
        //返回数据的数组
        $ret = array();

        if (!$goods_id){
            return false;
        }

        //分类下的所有商品id
        //$adminGoodsModel = D('Admin/Goods');
        //$cateGoodsId = $adminGoodsModel->getGoodsIdByCateId($cateId);

        /******************** 品牌 ********************/
        //根据商品id,取出品牌
        $goodsModel = D('goods');
        $ret['brand'] = $goodsModel->field('distinct brand_id,b.brand_name,b.logo')
            ->alias('a')
            ->join('__BRAND__ b ON a.brand_id=b.id')
            ->where(array(
                'a.id' => array('in',$goods_id),
                'a.brand_id' => array('neq',0)
            ))
            ->select();

        /******************** 价格区间段 ********************/
        $goodsPice = $goodsModel->field('max(shop_price) max_price,min(shop_price) min_price')
            ->where(array(
                'id' => array('in',$goods_id)
            ))->find();
        //计算最高价格与最低价格的差,来计算分几段区间
        $priceSection = $goodsPice['max_price'] - $goodsPice['min_price'];
        //统计分类下商品的数量
        $goodsCount = count($goods_id);
        $section = 0;//段数
        //商品数量大于6个 才分段
        if ($goodsCount > 6){
            if ($priceSection < 100){
                $section = 2;
            }elseif ($priceSection < 1000){
                $section = 4;
            }elseif ($priceSection < 10000){
                $section = 6;
            }else{
                $section = 7;
            }
            //每段的开始价格
            $firstPrice = 0;
            //每段结束价格
            $endPrice = 0;
            //根据段数分段
            $pricePerSection = ceil($priceSection / $section);//每段的范围
            $price = array();//存放分段数
            for ($i=0; $i < $section; $i++){
                $endPrice = $firstPrice + $pricePerSection;
                //对结束价格进行取整
                $endPrice = ceil($endPrice/100)*100 - 1;
                $price[] = $firstPrice.'-'.$endPrice;
                //计算下一段的开始价格
                $firstPrice = $endPrice + 1;
            }
            $ret['price'] = $price;
        }

        /******************** 商品属性 ********************/
        $goodsAttrModel = D('goods_attr');
        $goodsAttrDate = $goodsAttrModel->field('DISTINCT a.attr_id,a.attr_values,b.attr_name')
            ->alias('a')
            ->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')
            ->where(array(
                'a.goods_id' => array('in',$goods_id),
        ))->select();

        //把所有的
        $arr = array();
        foreach ($goodsAttrDate as $k=>$v) {
            $arr[$v['attr_name']][] = $v;
        }
        $ret['goods_attr'] = $arr;
        return $ret;
    }
    /**** 分类搜索商品 ****/
    public function cateSearchGoods($cateId,$pageSize = 8){
        //返回数组
        $ret = array();

        /*********************** 搜索 *************************/
        //根据分类id取出分类下的所有商品id
        $adminGoodsModel = D('Admin/Goods');
        $cateGoodsId = $adminGoodsModel->getGoodsIdByCateId($cateId);
        if (!$cateGoodsId){
            return false;
        }
        $homeGoodsModel = D('goods');
        //分类下的所有商品
        $where['a.id'] = array('in',$cateGoodsId);
        //品牌搜索商品
        $brand_id = I('get.brand_id');
        if ($brand_id){
            $where['a.brand_id'] = array('eq',(int)$brand_id);
        }
        //价格搜索商品
        $price = I('get.price');
        if ($price){
            $price = explode('-',$price);
            $where['a.shop_price'] = array('between',$price);
        }
        //属性搜索商品
        $goodsAttrModel = D('goods_attr');
        $attrGoodsId = null;//根据每个属性搜索出来的商品
        foreach ($_GET as $k=>$v){
            //如果是以attr_开头的,说明是一个属性的查询
            //格式：attr_1/红色-颜色/attr_2/32G-内存
            if (strpos($k,'attr_') === 0){
                //属性id
                $attr_id = str_replace('attr_','',$k);// attr_3  => 3
                //属性值
                $attrName = strrchr($v,'-');
                $attr_value = str_replace($attrName,'',$v);
                //根据属性id与属性值查询出这个属性值下的所有商品id, 返回一个字符串格式的商品id  如：1,2,3,4
                $goodsId = $goodsAttrModel->field('group_concat(goods_id) goods_id')->where(array(
                    'attr_id' => array('eq',$attr_id),
                    'attr_values' => array('eq',$attr_value)
                ))->find();
                //判断是否有商品
                if ($goodsId){
                    $goodsId['goods_id'] = explode(',',$goodsId['goods_id']);
                    //判断是一个属性搜索还是两个属性搜索
                    if ($attrGoodsId === null){
                        //一个属性搜索 如：attr_1/红色-颜色
                        $attrGoodsId = $goodsId['goods_id'];
                    }else{
                        //两个或几个属性同时搜索  如：attr_1/红色-颜色/attr_2/32G-内存
                        //取两个数组的交集
                        $attrGoodsId = array_intersect($attrGoodsId,$goodsId['goods_id']);
                        //判断是否有满足条件的商品,没有就不用考虑下一个属性了
                        if(empty($attrGoodsId)){
                            $where['a.id'] = array('eq',0);
                            break;
                        }
                    }
                }else{
                    //清空前几次的交集结果
                    $attrGoodsId = array();
                    //如果这个属性下没有商品,就应该向where中添加一个不可能满足的条件,这样后面就查询不出来
                    $where['a.id'] = array('eq',0);
                    //并跳出循环
                    break;
                }
            }
        }
        //判断循环之后这个数组还不为空,说明这些就是满足条件的所有商品
        if ($attrGoodsId){
            $where['a.id'] = array('IN',$attrGoodsId);
        }

        /*********************** 分页 *************************/
        //取出总的记录数,以及所有商品id的字符串
        $count = $homeGoodsModel->field('count(a.id) goods_count,group_concat(a.id) goods_id')->alias('a')->where($where)->find();// 查询满足要求的总记录数
        //把商品id返回出去
        $ret['goods_id'] = explode(',',$count['goods_id']);

        $Page = new \Think\Page($count['goods_count'],$pageSize);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $show = $Page->show();// 分页显示输出

        /*********************** 排序 *************************/
        $orderby = 'xl';
        $orderway = 'desc';
        $order = I('get.order');
        if ($order){
            if ($order == 'xl'){
                $orderby = 'xl';
            }
            if ($order == 'price'){
                $orderby = 'a.shop_price';
            }
            if ($order == 'addtime'){
                $orderby = 'a.addtime';
            }
        }

        /*********************** 取数据 *************************/
        //默认按销量排序
        //注意：取时,必须是完成订单支付的,才算一件销量
        $goodsList = $homeGoodsModel->field('a.id,goods_name,a.mid_logo,a.shop_price,SUM(b.goods_number) xl')
            ->alias('a')
            ->join('LEFT JOIN __ORDER_GOODS__ b ON (a.id=b.goods_id AND b.order_id IN(select id from __ORDER__ where pay_status="是"))')
            ->where($where)
            ->group('a.id')
            ->order("$orderby $orderway")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();

        $ret['page'] = $show;
        $ret['goodsList'] = $goodsList;
        return $ret;
    }

    /**** 关键字查询 ****/
    //【这里最好用 Sphinx全文索引引擎搜索】
    public function key_search($key,$pageSize = 8){
        //返回数组
        $ret = array();

        /*********************** 搜索 *************************/
        $homeGoodsModel = D('goods');

        //根据关键字搜索商品 【这里最好用 Sphinx全文索引引擎搜索】
        $keyGoodsId =  $homeGoodsModel->field('group_concat(distinct a.id) goods_id')
            ->alias('a')
            ->join('LEFT JOIN __GOODS_ATTR__ b ON a.id=b.goods_id')
            ->where(array(
            'a.is_on_sale' => array('eq','是'),
            'a.goods_name' => array('exp',"like '%$key%' or a.goods_desc like '%$key%' or b.attr_values like '%$key%'")
        ))->find();

        $keyGoodsId = explode(',',$keyGoodsId['goods_id']);

        if (!$keyGoodsId){
            return false;
        }
        //分类下的所有商品
        $where['a.id'] = array('in',$keyGoodsId);
        //品牌搜索商品
        $brand_id = I('get.brand_id');
        if ($brand_id){
            $where['a.brand_id'] = array('eq',(int)$brand_id);
        }
        //价格搜索商品
        $price = I('get.price');
        if ($price){
            $price = explode('-',$price);
            $where['a.shop_price'] = array('between',$price);
        }
        //属性搜索商品
        $goodsAttrModel = D('goods_attr');
        $attrGoodsId = null;//根据每个属性搜索出来的商品
        foreach ($_GET as $k=>$v){
            //如果是以attr_开头的,说明是一个属性的查询
            //格式：attr_1/红色-颜色/attr_2/32G-内存
            if (strpos($k,'attr_') === 0){
                //属性id
                $attr_id = str_replace('attr_','',$k);// attr_3  => 3
                //属性值
                $attrName = strrchr($v,'-');
                $attr_value = str_replace($attrName,'',$v);
                //根据属性id与属性值查询出这个属性值下的所有商品id, 返回一个字符串格式的商品id  如：1,2,3,4
                $goodsId = $goodsAttrModel->field('group_concat(goods_id) goods_id')->where(array(
                    'attr_id' => array('eq',$attr_id),
                    'attr_values' => array('eq',$attr_value)
                ))->find();
                //判断是否有商品
                if ($goodsId){
                    $goodsId['goods_id'] = explode(',',$goodsId['goods_id']);
                    //判断是一个属性搜索还是两个属性搜索
                    if ($attrGoodsId === null){
                        //一个属性搜索 如：attr_1/红色-颜色
                        $attrGoodsId = $goodsId['goods_id'];
                    }else{
                        //两个或几个属性同时搜索  如：attr_1/红色-颜色/attr_2/32G-内存
                        //取两个数组的交集
                        $attrGoodsId = array_intersect($attrGoodsId,$goodsId['goods_id']);
                        //判断是否有满足条件的商品,没有就不用考虑下一个属性了
                        if(empty($attrGoodsId)){
                            $where['a.id'] = array('eq',0);
                            break;
                        }
                    }
                }else{
                    //清空前几次的交集结果
                    $attrGoodsId = array();
                    //如果这个属性下没有商品,就应该向where中添加一个不可能满足的条件,这样后面就查询不出来
                    $where['a.id'] = array('eq',0);
                    //并跳出循环
                    break;
                }
            }
        }
        //判断循环之后这个数组还不为空,说明这些就是满足条件的所有商品
        if ($attrGoodsId){
            $where['a.id'] = array('IN',$attrGoodsId);
        }

        /*********************** 分页 *************************/
        //取出总的记录数,以及所有商品id的字符串
        $count = $homeGoodsModel->field('count(a.id) goods_count,group_concat(a.id) goods_id')->alias('a')->where($where)->find();// 查询满足要求的总记录数
        //把商品id返回出去
        $ret['goods_id'] = explode(',',$count['goods_id']);

        $Page = new \Think\Page($count['goods_count'],$pageSize);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $show = $Page->show();// 分页显示输出

        /*********************** 排序 *************************/
        $orderby = 'xl';
        $orderway = 'desc';
        $order = I('get.order');
        if ($order){
            if ($order == 'xl'){
                $orderby = 'xl';
            }
            if ($order == 'price'){
                $orderby = 'a.shop_price';
            }
            if ($order == 'addtime'){
                $orderby = 'a.addtime';
            }
        }

        /*********************** 取数据 *************************/
        //默认按销量排序
        //注意：取时,必须是完成订单支付的,才算一件销量
        $goodsList = $homeGoodsModel->field('a.id,goods_name,a.mid_logo,a.shop_price,SUM(b.goods_number) xl')
            ->alias('a')
            ->join('LEFT JOIN __ORDER_GOODS__ b ON (a.id=b.goods_id AND b.order_id IN(select id from __ORDER__ where pay_status="是"))')
            ->where($where)
            ->group('a.id')
            ->order("$orderby $orderway")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();

        $ret['page'] = $show;
        $ret['goodsList'] = $goodsList;
        return $ret;
    }
}
?>















