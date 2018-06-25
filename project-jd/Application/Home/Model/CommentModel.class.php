<?php
namespace Home\Model;
use Think\Model;

class CommentModel extends Model{
    protected $_insertFields = 'id,goods_id,member_id,content,addtime,star,click_count';
    protected $_updateFields = 'id,goods_id,member_id,content,addtime,star,click_count';
    protected $_validate = array(
        array('goods_id','require','参数错误！',1),
        array('content','1,200','不能超过200字！',1,'length',3),
        array('star','1,2,3,4,5','分值只能是1-5之间的数字！',1,'in'),
    );
    //添加之前的钩子函数
    public function _before_insert(&$data,$option){
        //判断是否登陆
        $user_id = session('m_id');
        if (!$user_id){
            $this->error = '必须先登陆';
            return false;
        }
        $data['member_id'] = $user_id;
        $data['addtime'] = date('Y-m-d H:i:s');

        /****** 处理印象的数据 ******/
        $yxModel = D('yinxiang');
        //选择已有印象
        $yx_id = I('yx_id');
        if ($yx_id){
            foreach ($yx_id as $v) {
                $yxModel->where(array('id'=> $v))->setInc('yx_count');
            }
        }

        //添加新印象
        $yx_name = I('post.yx_name');
        $yx_name = str_replace('，',',',$yx_name);
        $yx_name = explode(',',$yx_name);
        foreach ($yx_name as $v) {
            $v = trim($v);
            if (empty($v)){
                continue;
            }
            //查询表中是否有这个印象名了
            $has = $yxModel->where(array(
                'goods_id' => $data['goods_id'],
                'yx_name' => $v
            ))->find();
            if ($has){
                $yxModel->where(array(
                    'goods_id' => $data['goods_id'],
                    'yx_name' => $v
                ))->setInc('yx_count');
            }else{
                $yxModel->add(array(
                    'goods_id' => $data['goods_id'],
                    'yx_name' => $v,
                    'yx_count' => 1
                ));
            }
        }
    }
    // 取一件商品的所有评论
    /* @param $goods_id     商品id
     * @param $actionPage   当前是那一页
     * @param $page         每页显示几个评论
     * */
    public function search($goods_id,$actionPage,$pageSize = 5){
        // 统计总的记录数
        $count = $this->alias('a')
            ->where(array(
            'a.goods_id' => array('eq',$goods_id)
        ))->count();
        //计算一件商品的总页数
        $pageCount = ceil($count / $pageSize);
        // 当前是那一页
        $actionPage = max(1,(int)$actionPage);
        // 计算每页开始的评论的下标
        $offset = ($actionPage - 1)*$pageSize;

        /****** 计算好评率 ******/
        //如果是获取第一页的评论,就计算好评率与取出印象数据
        if ($actionPage == 1){
            $stars = $this->field('star')->where(array('goods_id',$goods_id))->select();
            $hao = $zhong = $cha = 0;
            foreach ($stars as $v) {
                if ($v['star'] == 3){
                    $zhong++;
                }elseif ($v['star'] > 3){
                    $hao++;
                }else{
                    $cha++;
                }
            }
            //计算好评率
            $total = $hao +$zhong + $cha;
            $hao = round($hao/$total*100,2);
            $zhong = round($zhong/$total*100,2);
            $cha = round($cha/$total*100,2);
            /***** 取印象数据 *****/
            $yxModel = D('yinxiang');
            $yxDate = $yxModel->field('id,yx_name,yx_count')->where(array('goods_id',$goods_id))->select();
        }

        /****** 取评论区数据 ******/
        $pageDate = $this->field('a.id,a.content,a.addtime,a.star,a.click_count,b.username,b.face,count(c.id) reply_count')
            ->alias('a')
            ->join('LEFT JOIN __MEMBER__ b ON a.member_id=b.id
                    LEFT JOIN __COMMENT_REPLY__ c ON a.id=c.comment_id')
            ->group('a.id')
            ->order('a.id desc')
            ->limit("$offset,$pageSize")
            ->select();

        /****** 取回复评论数据 ******/
        //循环每条评论的回复数据
        $commentReplyModel = D('comment_reply');
        foreach ($pageDate as $k=>$v) {
            $pageDate[$k]['reply'] = $commentReplyModel->field('a.content,a.addtime,b.username,b.face')
                                                ->alias('a')
                                                ->join('LEFT JOIN __MEMBER__ b ON a.member_id=b.id')
                                                ->where(array(
                                                'a.comment_id' => $v['id']
                                            ))
                                                ->order('a.addtime desc')
                                                ->limit(5)
                                                ->select();
        }

        return array(
            'pageCount' =>$pageCount,
            'pageDate' => $pageDate,
            'hao' => $hao,
            'zhong' => $zhong,
            'cha' => $cha,
            'yxDate' => $yxDate,
            'member_id' =>(int)session('m_id')//返回当前登陆的用户id,用于ajax请求时判断是否登陆
        );
    }
}
?>