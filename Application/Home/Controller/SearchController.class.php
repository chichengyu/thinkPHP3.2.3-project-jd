<?php
namespace Home\Controller;

class SearchController extends NavController{

    //分类搜索
    public function cate_search(){
        //分类id
        $cate_id = I('get.cate_id');
        //分类下的商品筛选条件
        $categoryModel = D('category');
        //dump($cateSearch);die;
        //取出分类下的所有商品
        $cateSearchGoodsDate = $categoryModel->cateSearchGoods($cate_id);
        //根据上面的商品来计算筛选条件
        $cateSearch = $categoryModel->getCateidSearchGoods($cateSearchGoodsDate['goods_id']);

        $this->assign(array(
            'cateSearch' => $cateSearch,
            'cateSearchGoodsDate' => $cateSearchGoodsDate
        ));

        //设置页面信息
        $this->assign(array(
            '_show_nav' => 0,
            '_page_title' =>'个人中心-我的订单',
            '_page_keywords' => '个人中心',
            '_page_description' => '个人中心'
        ));
        $this->display();
    }
    //关键字搜索
    public function key_search(){
        $key = I('get.key');

        //关键字搜索
        //这里把关键字搜索写在home的category模型下,跟商品筛选条件搜索写在了一起,便于复习查看
        $categoryModel = D('category');
        //取出分类下的所有商品
        $cateSearchGoodsDate = $categoryModel->key_search($key);
        //根据上面的商品来计算筛选条件
        $cateSearch = $categoryModel->getCateidSearchGoods($cateSearchGoodsDate['goods_id']);

        //sphinx搜索
        require('./sphinxapi.php');
        $sph = new \SphinxClient();
        $sph->Setserver('localhost',9312);//服务器ip地址 sphinx的端口号
        //过滤掉被标记为修改的数据,只保留0未修改的
        $sph->SetFilter('is_updated',array(1));
        //key 接收到的关键字(搜索字)
        //goods 从那个索引文件查,默认是*所有
        $ids = $sph->Query($key,'goods');
        //注意：sphinx查询到返回的是主键id,然后再用id去查询数据库
        if ($ids['matches']){
            $ids = array_keys($ids['matches']);
            $godosModel = D('goods');
            $godosModel->where(array(
                'id' => array('in',$ids)
            ))->select();
        }


        $this->assign(array(
            'cateSearch' => $cateSearch,
            'cateSearchGoodsDate' => $cateSearchGoodsDate
        ));

        //设置页面信息
        $this->assign(array(
            '_show_nav' => 0,
            '_page_title' =>'关键字搜索页',
            '_page_keywords' => '关键字搜索页',
            '_page_description' => '关键字搜索页'
        ));
        $this->display();
    }
}
?>