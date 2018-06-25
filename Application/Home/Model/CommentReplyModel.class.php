<?php
namespace Home\Model;
use Think\Model;

class CommentReplyModel extends Model{
    protected $_insertFields = 'comment_id,content';
    protected $_updateFields = 'comment_id,content';
    protected $_validate = array(
        array('comment_id', 'require', '参数错误！', 1),
        array('content', '1,200', '不能超过200字！', 1, 'length', 3),
    );
    //回复之评论前的钩子函数
    protected function _before_insert(&$data,$option){
        $member_id = session('m_id');

        $data['addtime'] = date('Y-m-d H:i:s');
        $data['member_id'] = $member_id;
    }
}
?>