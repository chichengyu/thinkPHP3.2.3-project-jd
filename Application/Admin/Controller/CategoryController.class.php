<?php
/**
 * Created by PhpStorm.
 * User: 小池
 * Date: 2018/5/5
 * Time: 11:49
 */

namespace Admin\Controller;

class CategoryController extends BaseController{

    //添加分类
    public function add(){
        $model = D('category');
        if (IS_POST){
            if ($model->create(I('post.'),1)){
                if ($model->add()){
                    $this->success('添加成功！',U('lst'));
                }
            }
            $this->error('添加失败！'.$model->getError());
        }

        //先查询所有分类
        $cateTata = $model->getTree();

        //设置页面信息
        $this->assign(array(
            'data' => $cateTata,
            '_page_title' => '添加分类',
            '_page_btn_name' => '分类列表',
            '_page_btn_link' => U('lst')
        ));

        $this->display();
    }
    //分类列表
    public function lst(){
        $model = D('category');
        $data = $model->getTree();

        //设置页面信息
        $this->assign(array(
            'data' => $data,
            '_page_title' => '分类列表',
            '_page_btn_name' => '添加分类',
            '_page_btn_link' => U('add')
        ));

        $this->display();
    }
    //修改分类
    public function edit(){
        $id = I('get.id');
        $model = D('Category');
        if (IS_POST){
            //$model->where("id=$id")->save(I('POST.'));这样直接修改也可以
            if (false !== $model->create(I('post.'),2)){
                if ($model->save()){
                    $this->success('修改成功',U('lst'));
                    exit;
                }
            }

            $this->error('修改失败！'.$model->getError());
        }
        //查询当前这条分类信息
        $action_cate = $model->find($id);
        $this->assign('action_cate',$action_cate);

        //查找当前分类的子类
        $children_cate = $model->getChild($id);
        $this->assign('children_cate',$children_cate);

        //查询所有分类
        $cateDate = $model->getTree();

        //设置页面信息
        $this->assign(array(
            'data' => $cateDate,
            '_page_title' => '修改分类',
            '_page_btn_name' => '分类列表',
            '_page_btn_link' => U('lst'),
        ));

        $this->display();
    }
    //删除分类
    public function delete(){
        $model = D('category');
        if (false !== $model->delete(I('get.id'))){
            $this->success('删除成功！',U('lst'));
        }else{
            $this->error('删除失败！'.$model->getError());
        }
    }
}

?>