<?php
namespace Admin\Controller;
class MemberPriceController extends BaseController
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('MemberPrice');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('lst?p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '添加商品会员价格',
			'_page_btn_name' => '商品会员价格列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function edit()
    {
    	$id = I('get.id');
    	if(IS_POST)
    	{
    		$model = D('MemberPrice');
    		if($model->create(I('post.'), 2))
    		{
    			if($model->save() !== FALSE)
    			{
    				$this->success('修改成功！', U('lst', array('p' => I('get.p', 1))));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	$model = M('MemberPrice');
    	$data = $model->find($id);
    	$this->assign('data', $data);

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '修改商品会员价格',
			'_page_btn_name' => '商品会员价格列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function delete()
    {
    	$model = D('MemberPrice');
    	$leveId = I('get.id');
    	$goods_id = I('get.goods_id');
    	$where = array(
    	    'level_id' =>array('eq',$leveId),
            'goods_id' =>array('eq',$goods_id)
        );
    	if($model->where($where)->delete() !== FALSE)
    	{
    		$this->success('删除成功！', U('lst', array('p' => I('get.p', 1))));
    		exit;
    	}
    	else 
    	{
    		$this->error($model->getError());
    	}
    }
    public function lst()
    {
    	$model = D('MemberPrice');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '商品会员价格列表',
			'_page_btn_name' => '添加商品会员价格',
			'_page_btn_link' => U('add'),
		));
    	$this->display();
    }
}