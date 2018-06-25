<?php
namespace Home\Model;
use Think\Model;
class MemberModel extends Model{
    protected $insertFields = array('username','password','cpassword','checkcode','must_click','checked');
    protected $updateFields = array('id','username','password','cpassword','checkcode','must_click','checked');
    protected $_validate = array(
        array('must_click', 'require', '必须同意用户注册协议！', 1, 'regex', 3),
        array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
        array('username', '1,30', '用户名为 1-30 个字符！', 1, 'length', 3),
        array('password', 'require', '密码不能为空！', 1, 'regex', 1),
        array('password', '6,20', '密码为 6-20 个字符！', 1, 'length', 3),
        array('username', 'unique', '用户名已存在！', 1, 'unique', 3),
        array('cpassword', 'password', '两次输入密码不一致！', 1, 'confirm', 3),
        array('checkcode','require','验证码不能为空！',1),
        array('checkcode','check_verify','验证码输入错误！',1,'callback'),
    );
    //登陆表单验证规则【模型里定义必须是public】
    public $_login_validate = array(
        array('username','require','用户名不能为空！',1),
        array('password','require','密码不能为空！',1),
        array('checkcode','require','验证码不能为空！',1),
        array('checkcode','check_verify','验证码输入错误！',1,'callback'),
    );
    /*********检测输入的验证码是否正确，$code为用户输入的验证码字符串*********/
    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        $verify->reset = false;
        return $verify->check($code, $id);
    }
    //登陆验证账号密码
    public function login(){
        //dump($this);die;
        //获取模型中的数据
        $username = $this->username;
        $password = $this->password;

        //查询用户是否存在
        $user = $this->field('id,username,password,jifen')
            ->where(array(
            'username' => array('eq',$username)
        ))->find();
        if ($user){
            if ($user['password'] == md5($password)){
                //判断是否记住账号
                if (isset($_POST['checked'])){
                    setcookie('s_username',$user['username'],time()+7*24*3600,'/');
                    setcookie('s_password',$password,time()+7*24*3600,'/');
                }
                //登陆成功存session
                session('m_id',$user['id']);
                session('m_username',$user['username']);

                // 计算当前会员级别ID并存session
                $memberLevelModel = D('member_level');
                $level_id = $memberLevelModel->field('id')
                    ->where(array(
                    'jifen_bottom' => array('elt',$user['jifen']),
                    'jifen_top' => array('egt',$user['jifen']),
                ))->find();
                session('level_id',$level_id['id']);

                //将当前cookie中的购物车信息添加到数据库
                $cartModel = D('cart');
                $cartModel->moveCart();
                return true;
            }else{
                $this->error = '密码输入不正确！';
                return false;
            }
        }else{
            $this->error = '用户名不存在！';
            return false;
        }
    }
    //注册之前进行密码加密
    protected function _before_insert(&$data,$option){
        $data['password'] = md5($data['password']);
    }
    //退出
    public function logout(){
        session(null);
    }
}
?>