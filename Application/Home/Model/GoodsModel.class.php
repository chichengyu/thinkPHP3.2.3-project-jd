<?php
namespace Home\Model;
use Think\Model;
class GoodsModel extends Model{

    /**** ajax实时获取当前会员价格 ****/
    public function getMemberPrice($goodsId){
        //是否有促销价格
        $toDayTime = date('Y-m-d H:i:s');
        $promotePrice = $this->field('promote_price')
            ->where(array(
                'id' => array('eq',$goodsId),
                'promote_price' => array('gt',0),
                'promote_start_date' => array('elt',$toDayTime),
                'promote_end_date' => array('egt',$toDayTime)
            ))->find();

        //获取当前会员级别id 并 判断是否有会员级别id
        $levelId = session('level_id');
        if ($levelId){
            //有会员级别 就 返回会员价格
            $memberPriceModel = D('member_price');
            $goodsMemberPice = $memberPriceModel->field('price')
                ->where(array(
                'level_id' => array('eq',$levelId),
                'goods_id' => array('eq',$goodsId)
            ))->find();

            //判断当前商品是否设置了会员价格,没有就判断是否有促销价格,没有就返回本店价格
            if ($goodsMemberPice['price']){
                //判断是否有促销价格
                if ($promotePrice['promote_price']){
                    return min($goodsMemberPice['price'],$promotePrice['promote_price']);
                }else{
                    return $goodsMemberPice['price'];
                }
            }else{
                $goodsPrice = $this->field('shop_price')->find($goodsId);
                //判断是否有促销价格
                if ($promotePrice['promote_price']){
                    return min($promotePrice['promote_price'],$goodsPrice['shop_price']);
                }else{
                    return $goodsPrice['shop_price'];
                }
            }
        }else{
            //没有会员级别,就判断是否有促销价格,没有就返回本店价格
            $goodsPrice = $this->field('shop_price')->find($goodsId);

            //判断是否有促销价格
            if ($promotePrice['promote_price']){
                return min($promotePrice['promote_price'],(int)$goodsPrice['shop_price']);
            }else{
                return $goodsPrice['shop_price'];
            }
        }
    }
    /**** 获取正在促销的商品数据 ****/
    public function getPromoteGoods($limit=5){
        $date = date('Y-m-d H:i:s');
        $goodsPromoteDate = $this->field('id,goods_name,mid_logo,promote_price')
            ->where(array(
            'is_on_sale'=>array('eq','是'),
            'promote_price' => array('gt',0),
            'promote_start_date' => array('elt',$date),
            'promote_end_date' => array('egt',$date),
        ))
            ->limit($limit)
            ->order('sort_number ASC')
            ->select();
        return $goodsPromoteDate;
    }
    /**** 获取热卖、新品、精品的商品数据 ****/
    public function getRecGoods($type,$limit=5){
        $getDate = $this->field('id,goods_name,mid_logo,shop_price')
            ->where(array(
            'is_on_sale' => array('eq','是'),
            "$type" => array('eq','是')
        ))
            ->limit($limit)
            ->order('sort_number ASC')
            ->select();
        return $getDate;
    }
}

?>