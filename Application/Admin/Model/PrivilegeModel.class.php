<?php
namespace Admin\Model;
use Think\Model;
class PrivilegeModel extends Model 
{
	protected $insertFields = array('pri_name','module_name','controller_name','action_name','parent_id');
	protected $updateFields = array('id','pri_name','module_name','controller_name','action_name','parent_id');
	protected $_validate = array(
		array('pri_name', 'require', '权限名称不能为空！', 1, 'regex', 3),
		array('pri_name', '1,30', '权限名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('module_name', '1,30', '模块名称的值最长不能超过 30 个字符！', 2, 'length', 3),
		array('controller_name', '1,30', '控制器名称的值最长不能超过 30 个字符！', 2, 'length', 3),
		array('action_name', '1,30', '方法名称的值最长不能超过 30 个字符！', 2, 'length', 3),
		array('parent_id', 'number', '上级权限id必须是一个整数！', 2, 'regex', 3),
	);

    /****** 验证管理员权限 *****/
    public function chkPri(){
        //获取当前管理员发访问的模型名称、控制器名称、方法名称
        // MODULE_NAME CONTROLLER_NAME ACTION_NAME
        $admin_id = session('id');

        //如果是超级管理员,直接true
        if ($admin_id == 1){
            return true;
        }
        //先根据管理员id查询角色id,在根据角色id查询权限id,再根据权限的 模块/控制器/方法名称与当前访问的 模块/控制器/方法进行对比,统计数量有没有
        $adminRoleModel = D('admin_role');
        $priDate = $adminRoleModel->alias('a')
            ->join('LEFT JOIN __ROLE_PRI__ b ON a.role_id=b.role_id
                    LEFT JOIN __PRIVILEGE__ c ON b.pri_id=c.id')
            ->where(array(
                'a.admin_id' =>array('eq',$admin_id),
                'c.module_name' => array('eq',MODULE_NAME),
                'c.controller_name' => array('eq',CONTROLLER_NAME),
                'c.action_name' => array('eq',ACTION_NAME),
            ))
            ->count();
        return ($priDate>0);
    }
    /******** 获取当前管理员所有的两级权限(生成左侧菜单) *********/
    public function getBtns(){
        /******** 查询管理员所拥有的权限 ********/
        //获取当前用户id
        $admin_id = session('id');
        if ($admin_id == 1){
            //超级管理员
            $priModel = D('privilege');
            $priDate = $priModel->select();
        }else{
            //获取当前管理员所在角色 所在权限
            $adminRoleModel = D('admin_role');
            $priDate = $adminRoleModel->field('distinct c.id,c.*')
                ->alias('a')
                ->join('LEFT JOIN __ROLE_PRI__ b ON a.role_id=b.role_id
                        LEFT JOIN __PRIVILEGE__ c ON b.pri_id=c.id')
                ->where(array(
                'a.admin_id' => array('eq',$admin_id)
            ))->select();
        }
        /******** 从所有权限中挑出前两级 ********/
        $btns = array();//前两级权限
        foreach ($priDate as $v){
            if ($v['parent_id'] == 0){
                foreach ($priDate as $v1) {
                    if ($v1['parent_id'] == $v['id']){
                        $v['children'][] = $v1;
                    }
                }
                $btns[] = $v;
            }
        }
        return $btns;
    }

	/************************************* 递归相关方法 *************************************/
	public function getTree()
	{
		$data = $this->select();
		return $this->_reSort($data);
	}
	private function _reSort($data, $parent_id=0, $level=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$v['level'] = $level;
				$ret[] = $v;
				$this->_reSort($data, $v['id'], $level+1, FALSE);
			}
		}
		return $ret;
	}
	public function getChildren($id)
	{
		$data = $this->select();
		return $this->_children($data, $id);
	}
	private function _children($data, $parent_id=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$ret[] = $v['id'];
				$this->_children($data, $v['id'], FALSE);
			}
		}
		return $ret;
	}
	/************************************ 其他方法 ********************************************/
	protected function _before_update(&$data,$option){
	    //$id = $data['id'];
	    //$parent_id = I('post.parent_id');
        //dump($_POST);die;
    }
	public function _before_delete($option)
	{
        /******* 先删除角色权限中间表相关权限的角色 *******/
        $pri_id = $option['where']['id'];
        $rolePriModel = D('role_pri');
        $rolePriModel->where(array(
            'pri_id' =>array('eq',$pri_id)
        ))->delete();

		// 先找出所有的子分类
		$children = $this->getChildren($option['where']['id']);
		// 如果有子分类都删除掉
		if($children)
		{
			$this->error = '有下级数据无法删除';
			return FALSE;
		}
	}
}