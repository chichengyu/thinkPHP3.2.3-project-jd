<?php
namespace Admin\Model;
use function Sodium\add;
use Think\Model;
class AdminModel extends Model 
{
	protected $insertFields = array('username','password','cpassword','checkcode');
	protected $updateFields = array('id','username','password','cpassword','checkcode');
	protected $_validate = array(
		array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
		array('username', '1,30', '用户名的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		array('username', 'unique', '用户名已存在！', 1, 'unique', 3),
		array('cpassword', 'password', '两次输入密码不一致！', 1, 'confirm', 3),
	);
	//为登陆的表单定义一个验证规则,【模型里定义必须是public】
	public $_login_validate = array(
        array('username', 'require', '用户名不能为空！', 1,),
        array('password', 'require', '密码不能为空！', 1,),
        array('checkcode', 'require', '验证码不能为空！！', 1,),
        //如果是function验证验证码,就必须写在common/common/function.php函数文件中
        //array('checkcode', 'check', '验证码输入错误！', 1, 'function'),
        array('checkcode', 'check_verify', '验证码输入错误！', 1, 'callback'),
    );
    /*********检测输入的验证码是否正确，$code为用户输入的验证码字符串*********/
    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        $verify->reset = false;
        return $verify->check($code, $id);
    }
    /*** 登陆 ***/
    public function login(){
        /*** 从模型中获取提交的用户与密码 ***/
        $username = $this->username;//相当于 I('post.username')
        $password = $this->password;

        //先查询用户名是否存在
        $user = $this->where(array(
            'username' => array('eq',$username)
        ))->find();
        if ($user){
            //验证密码
            if ($user['password'] == md5($password)){
                //登陆成功存session
                session('id', $user['id']);
                session('username',$user['username']);
                return true;
            }else{
                $this->error = '密码不正确';
                return false;
            }
        }else{
            $this->error = '用户名不存在！';
            return false;
        }
    }
    /*** 推出登陆 ***/
    public function logout(){
        session('id', null);
        session('username',null);
    }

	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($username = I('get.username'))
			$where['username'] = array('like', "%$username%");
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->field('a.username,a.id,group_concat(c.role_name separator"、") role_name')
            ->alias('a')
            ->join('LEFT JOIN __ADMIN_ROLE__ b ON a.id=b.admin_id
                    LEFT JOIN __ROLE__ c ON b.role_id=c.id')
            ->where($where)
            ->group('a.id')
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option)
	{
	    $data['password'] = md5($data['password']);
	}
	//添加后
    protected function _after_insert(&$data,$option)
    {
	    /***** 添加角色与管理员id到管理员角色表 *****/
        $admin_id = $data['id'];
        $role_id = I('post.role_id');
        $adminRoleModel = D('admin_role');
        foreach ($role_id as $v){
            $adminRoleModel->add(array(
                'admin_id' => $admin_id,
                'role_id' => $v
            ));
        }
    }
	// 修改前
	protected function _before_update(&$data, $option)
	{
	    $admin_id = $option['where']['id'];
	    /******* 修改管理员角色 *******/
	    $role_id = I('post.role_id');
	    $adminRoleModel = D('admin_role');
	    //先管理员删除原来的角色
        $adminRoleModel->where(array(
            'admin_id' => array('eq',$admin_id)
        ))->delete();
        //在添加的角色
        foreach ($role_id as $v) {
            $adminRoleModel->add(array(
                'admin_id' => $admin_id,
                'role_id' => $v
            ));
	    }

	    /******** 判断是否设置了新密码 ********/
	    if ($data['password']){
            $data['password'] = md5($data['password']);
        }else{
            unset($data['password']);//从表单中删除这个字段就不会修改这个字段了
        }
	}
	// 删除前
	protected function _before_delete($option)
	{
        /******* 先删除管理员角色表中与管理员相关的角色 *******/
        $admin_id = $option['where']['id'];
        $adminRoleModel = D('admin_role');
        $adminRoleModel->where(array(
            'admin_id' => array('eq',$admin_id)
        ))->delete();

		if($option['where']['id'] == 1)
		{
			$this->error = '超级管理员无法删除！';
			return FALSE;
		}
	}
	/************************************ 其他方法 ********************************************/
}