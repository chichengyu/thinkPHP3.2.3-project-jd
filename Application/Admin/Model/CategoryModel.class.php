<?php
/**
 * Created by PhpStorm.
 * User: 小池
 * Date: 2018/5/5
 * Time: 10:46
 */

namespace Admin\Model;
use Think\Model;

class CategoryModel extends Model{

    protected $insertFields = array('cate_name','parent_id','is_floor');
    protected $updateFields = array('id','cate_name','parent_id','is_floor');
    protected $_validate = array(
        array('cate_name','require','分类名称不能为空',1)
    );

    //递归函数：1   找一个分类下的所有子类的id
    public function getChild($catId){
        //取出所有分类
        $data = $this->select();
        //true每次调用的时候清空数组中上次查询的子类结果
        return $this->_getChild($data,$catId,true);
    }
    /* ##### 递归函数：1 ######
     * 递归从数据中找子分类
     * @param $data     所有分类
     * @param $catId    查询$catId(谁)的子类
     * @param $isClear  是否清空(每次调用的时候必须清空上次数组中存放的子类)，默认不清空
     * */
    private function _getChild($data,$catId,$isClear=false){
        static $_res = array();//用来保存找到的子类的ID
        if($isClear){
            $_res = array();
        }
        foreach ($data as $k=>$v){
            if ($v['parent_id'] == $catId){
                $_res[] = $v['id'];//找到的子类ID存进数组
                //再找这个$v的子类
                $this->_getChild($data,$v['id']);//一级一级的找下去,此时不清空
            }
        }
        return $_res;
    }

    //递归函数：2   显示所有分类层次结构在分类列表页面
    public function getTree(){
        $data = $this->select();
        return $this->_getTree($data);
    }
    /* ##### 递归函数：2 ######
     * 递归从数据中找子分类,进行排序 生成结构层次图
     * @param $data         所有分类
     * @param $parent_id    默认查询所有顶级分类,包括每个顶级分类下的子类
     * @param $level        用来标记是第几级,让分类层级在页面更加明显
     * */
    private function _getTree($data,$parent_id=0,$level=0){
        static $_res = array();
        foreach ($data as $k=>$v) {
            if ($v['parent_id'] == $parent_id){
                $v['level'] = $level;//用来标记是第几级
                $_res[] = $v;
                //找子类
                //注意：每一次的遍历$level始终为0,如果写成++level就改变了每次遍历的初始值0,这样每遍历一次level就累加一次,显然错了
                $this->_getTree($data,$v['id'],$level+1);
            }
        }
        return $_res;
    }


    //删除之前的钩子函数
    protected function _before_delete(&$option){
        //先去查找当前这个分类id下的的所有子分类
        //利用上面封装的查找getChild递归查询到这个分类下的所有子分类的ID
        $child_id = $this->getChild($option['where']['id']);
     /*   ####方法一####
      *    if ($res){
            $child_id = implode(',',$child_id);
            //用查询到的子分类ID的数组进行删除
            #注意：这里必须是生成父类模型Model的delete进行删除,因为如果使用$this调用delete()进行删除,那么会在delete之前又会调用$this->_before_delete,这样就形成死循环了,而用来父类Model的delete就会在delete之前调用父类的_before_delete和这个$this的_before_delete没关系,就不会死循环了

            $model = new \Think\Model;
            //调用父类Model的方法时,不知道是那张表,所以需要指定表
            $model->table('__CATEGORY__')->delete($child_id);
        }
     */
        ####方法二(推荐)#### 注意：&$option必须引用传递
        if ($child_id){
            //把$option['where']['id']顶级分类ID加到$child_id子分类数组里面
            $child_id[] = $option['where']['id'];
            //把数组每项都拼接起来
            $child_id_str = implode(',',$child_id);
            //在赋值给$option
            $option['where']['id'] = array(
                0 => 'IN',
                1 => $child_id_str
            );
        }
    }
}
?>