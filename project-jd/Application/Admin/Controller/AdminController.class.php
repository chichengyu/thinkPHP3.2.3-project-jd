<?php
namespace Admin\Controller;
class AdminController extends BaseController
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Admin');
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

    	/**** 查询所有角色 ****/
        $roleModel = D('role');
        $roleDate = $roleModel->select();

		// 设置页面中的信息
		$this->assign(array(
		    'roleDate' => $roleDate,
			'_page_title' => '添加用户',
			'_page_btn_name' => '用户列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function edit()
    {
    	$id = I('get.id');
    	if(IS_POST)
    	{
    		$model = D('Admin');
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
    	$model = M('Admin');
    	$data = $model->find($id);
    	$this->assign('data', $data);

        /**** 查询所有角色 ****/
        $roleModel = D('role');
        $roleDate = $roleModel->select();

        /**** 查询当前管理员拥有的角色 ****/
        $adminRoleModel = D('admin_role');
        $adminRoleDate = $adminRoleModel->field('group_concat(role_id) role_id')
            ->where(array(
            'admin_id' => array('eq',$id)
        ))->find();

		// 设置页面中的信息
		$this->assign(array(
		    'adminRole_id' => $adminRoleDate['role_id'],
		    'roleDate' => $roleDate,
			'_page_title' => '修改用户',
			'_page_btn_name' => '用户列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Admin');
    	if($model->delete(I('get.id', 0)) !== FALSE)
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
    	$model = D('Admin');
    	$data = $model->search();
        //dump($data);die;
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '用户列表',
			'_page_btn_name' => '添加用户',
			'_page_btn_link' => U('add'),
		));
    	$this->display();
    }
}