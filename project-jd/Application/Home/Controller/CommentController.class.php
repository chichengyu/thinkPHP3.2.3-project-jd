<?php
namespace Home\Controller;
use Think\Controller;
class CommentController extends Controller{
    /****** ajax处理回复评论 ******/
    public function replay(){
        if (IS_POST){
            $commentReplyModel = D('comment_reply');
            if ($commentReplyModel->create(I('post.'),1)){
                if ($commentReplyModel->add()){
                    $arr = array(
                        'face' => session('face'),
                        'content' => I('post.content'),
                        'addtime' => date('Y-m-d H:i:s'),
                        'username' => session('m_username'),
                    );
                    $this->success($arr,'',true);
                    exit;
                }
            }
            $this->error('回复失败！'.$commentReplyModel->getError(),'',true);
        }
    }
    /****** ajax请求评论数据 ******/
    public function ajaxGetPl(){
        //当前商品id
        $goods_id = I('get.goods_id');
        //当前是那一页
        $actionPage = I('get.page');
        $commentModel = D('comment');
        $commentPageDate = $commentModel->search($goods_id,$actionPage);
        echo json_encode($commentPageDate);
    }
    //添加评论
    public function add(){
        if (IS_POST){
            $commentModel = D('Comment');
            if ($commentModel->create(I('post.'),1)){
                if ($comment_id = $commentModel->add()){
                    $arr = array(
                        'username' => session('m_username'),
                        'face' => session('face'),
                        'content' => I('post.content'),
                        'star' => I('post.star'),
                        'addtime' => date('Y-m-d H:i:s'),
                        'comment_id' => $comment_id
                    );
                    //success error的第三个参数为数字表示时间,为布尔值表示是否ajax并返回一个json对象
                    $this->success($arr,'',true);
                    exit;
                }
            }
            $this->error('评论失败！'.$commentModel->getError(),'',true);

        }
    }
}
?>