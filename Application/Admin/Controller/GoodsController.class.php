<?php
    namespace Admin\Controller;

    class GoodsController extends BaseController{

        //库存量
        public function goodsNumber(){
            //商品id
            $goods_id = I('get.id');
            $goodsNumberModel = D('goods_number');
            //处理表单
            if (IS_POST){
                //添加的时候先删除原来的库存
                $goodsNumberModel->where(array(
                    'goods_id' => array('eq',$goods_id)
                ))->delete();
                /*
                 * 注意：库存量是两个属性id对应一个库存量
                 * 如：白色(颜色属性id) - 16g(内存属性id) = 当前的库存量
                 * */
                //提交的数据
                $goodsAttrIdList = I('post.goods_attr_id_list');
                $goodsNumber = I('post.goods_number');

                //两个属性对应一个库存量
                $goods_attr_id_Counm = count($goodsAttrIdList);//计算提交的属性数量
                $goods_number_Counm = count($goodsNumber);//计算提交的商品库存数量
                //计算每次提交时,取几个属性id拼成字符串与库存量一起存
                $rate = $goods_attr_id_Counm/$goods_number_Counm;
                $_i = 0;
                foreach ($goodsNumber as $k=>$v){
                    $goodsAttrId = array();
                    //计算每次提交时,从提交的$goodsAttrIdList属性id中取几个属性id拼成字符串与库存量一起存
                    for ($i = 0; $i < $rate; $i++){
                        $goodsAttrId[] = $goodsAttrIdList[$_i];
                        $_i++;
                    }
                    //避免前后台乱序,前后台存时都以升序的方法存进表去
                    sort($goodsAttrId,SORT_NUMERIC);//以数字算法来排序
                    $goodsAttrId = (string)implode(',',$goodsAttrId);
                    //goods_id商品id => goods_number库存量 => 白色属性id3,内存属性id4
                    //格式如：15(goods_id) -> 1000(goods_number) -> 3,4(goods_attr_id_list,白色属性id3,内存属性id4)
                    $goodsNumberModel->add(array(
                        'goods_id' => $goods_id,
                        'goods_number' => $v,
                        'goods_attr_id_list' =>$goodsAttrId
                    ));
                }
                $this->success('修改成功！',U('goodsNumber?id='.I('get.id')));
                exit;
            }
            $goodsAttrModel = D('goods_attr');
            $goodsAttrDate = $goodsAttrModel->field('a.*,b.attr_name,b.attr_type')
                ->alias('a')
                ->join('__ATTRIBUTE__ b ON a.attr_id=b.id')
                ->where(array(
                'a.goods_id' => array('eq',$goods_id),
                'b.attr_type' => array('eq','可选')
            ))->select();
            //处理查询处理的数组数据
            $NewGoodsAttrDate = array();
            foreach ($goodsAttrDate as $val) {
                //将商品属性(红色手机、粉色手机)属于颜色的一些放在一个颜色的数组,商品属性(16g、32g)属于内存的放在一个内存的数组中
                $NewGoodsAttrDate[$val['attr_name']][] = $val;
            }

            /**** 查询已有的库存量 ****/
            $goodsNumberDate = $goodsNumberModel->where(array(
                'goods_id' => array('eq',$goods_id)
            ))->select();
            //dump($goodsNumberDate);die;

            //设置页面信息
            $this->assign(array(
                'goodsNumberDate' => $goodsNumberDate,
                'NewGoodsAttrDate' => $NewGoodsAttrDate,
                '_page_title' => '修改库存',
                '_page_btn_name' => '返回列表',
                '_page_btn_link' => U('lst')
            ));

            //显示表单
            $this->display();
        }

        // ajax删除相册图片
        public function getDelPic(){
            $goods_pic_id = I('get.pic_id');
            $goodsPicModel = D('goods_pic');
            $goodsPicDate = $goodsPicModel->field('pic,sm_pic,mid_pic,big_pic')
                ->where(array(
                'id' => array('eq',$goods_pic_id)
            ))->find();
            //删除图片
            deleteImage($goodsPicDate);
            //删除数据
            $goodsPicModel->delete($goods_pic_id);
        }

        // ajax删除商品属性
        public function ajaxDelAttr(){
            //商品id
            $goods_id = addslashes(I('get.goods_id'));//addslashes防止sql注入,转移一下
            $goodsAttrId = addslashes(I('get.goods_attr_id'));
            $goodsAttrModel = D('goods_attr');
            $goodsAttrModel->delete($goodsAttrId);
            //删除商品属性的时候,跟该条属性相关的库存也删除
            $goodsNumberModel = D('goods_number');
            $goodsNumberModel->where(array(
                'goods_id' => array('EXP',"=$goods_id AND find_in_set($goodsAttrId,`goods_attr_id_list)")
            ))->delete();
        }
        // ajax请求返回的数据
        public function ajaxGetAttr(){
            $attrModel = D('attribute');
            $attrDate = $attrModel->where(array(
                'type_id' => array('eq',I('get.type_id'))
            ))->select();
            //返回数据时,一定要echo输出,不能是return,否则前端接收不到什么
            echo json_encode($attrDate);
        }

        //添加商品
        public function add(){
            //判断是否提交
            if(IS_POST){
                $model = D('goods');
            /*
             * I函数  进行变量获取和过滤,防止xss攻击
             * I('POST.')获取整个$_POST 数组
             * create方法的第二个参数可以指定创建数据的操作状态，默认情况下是自动判断是写入还是更新操作。
             * 系统内置的数据操作包括Model::MODEL_INSERT（或者1）和Model::MODEL_UPDATE（或者2），
             * create方法返回的是 创建完成的数据对象数组
             * */
                if ($model->create(I('POST.'),1)){
                    //插入到数据库
                    if ($model->add()){
                        //U('list') // 生成当前访问模块的list方法操作地址
                        $this->success('操作成功',U('lst'));
                        exit;
                    }
                }
                //从model模型中获取失败原因
                $error = $model->getError();
                //显示错误信息,默认返回上一页
                $this->error($error);
            }
        /*   //修改之前查询所有的品牌
            $brandModel = D('brand');
            $brandDate = $brandModel->select();

            此处注释,是使用了自己封装的函数buildSelect,然后生成下拉选框
        */

            //查询会员级别
            $memberLevel = D('MemberLevel');
            $memberLevelDate = $memberLevel->select();

            //查询category表中的所有分类
            $cateModel = D('category');
            $cateDate = $cateModel->getTree();

            //设置页面信息
            $this->assign(array(
                'memberLevelDate'=>$memberLevelDate,
                'cateDate' =>$cateDate,
                '_page_title' => '添加商品',
                '_page_btn_name' => '商品列表',
                '_page_btn_link' => U('lst')
            ));

            //显示表单
            $this->display();
        }
        //商品列表
        public function lst(){
            $model = D('goods');

            //返回数据和分页
            $data = $model->search();
            $this->assign($data);

            /***** 查询所有分类 *****/
            $cateModel = D('category');
            $cateDate = $cateModel->getTree();

            //设置页面信息
            $this->assign(array(
                'cateDate' => $cateDate,
                '_page_title' => '商品列表',
                '_page_btn_name' => '添加新商品',
                '_page_btn_link' => U('add')
            ));
            $this->display();
        }
        //修改商品
        public function edit(){
            $model = D('goods');
            $id = I('get.id');
            //判断是否提交
            if (IS_POST){
                //注意：save修改更新数据的时候,create成功返回的是影响行数,失败返回的是false
                //但如果修改的数据跟以前是一样的,那么返回的影响行数是0
                if ($model->create(I('post.'),2)){
                    if (false !== $model->save()){
                        $this->success('修改成功',U('lst'));
                        exit;
                    }
                }
                //从模型中获取失败信息
                $error = $model->getError();
                $this->error($error);
            }
            //读取当前修改的这条数据
            $data = $model->find($id);
            $this->assign('data',$data);

         /*   //修改之前查询所有的品牌
            $brandModel = D('brand');
            $brandDate = $brandModel->select();

            此处注释,是使用了自己封装的函数buildSelect,然后生成下拉选框
        */

            /***** 查询所有会员 *****/
            $memberLevelModel =  D('member_level');
            $memberLevelDate = $memberLevelModel->select();
            /***** 查询所有会员价格 *****/
            $memberPiceModel = D('member_price');
            $memberPiceDate = $memberPiceModel->where(array(
                'goods_id' => array('eq',$id)
            ))->select();

            //dump($memberPiceDate);die;

            /***** 查询当前商品的所有属性 *****/
            /*sql语句
                select a.goods_name,a.type_id,b.attr_name,b.id,c.* from php_goods a inner join php_attribute b ON a.type_id=b.type_id inner join php_goods_attr c ON b.id=c.attr_id;
             * */
            $goodsAttrModel = D('attribute');
            $goodsAttrDate = $goodsAttrModel->field('a.*,b.attr_values,b.id goods_attr_id,b.attr_id')
                ->alias('a')
                ->join("LEFT JOIN __GOODS_ATTR__ b ON (a.id=b.attr_id AND b.goods_id=$id)")
                ->where(array(
                'a.type_id' => array('eq',$data['type_id'])
            ))->select();

            /****查询category表中的所有分类****/
            $cateModel = D('category');
            $cateDate = $cateModel->getTree();

            /**** 查询扩展分类 ****/
            $extCateModel = D('goods_cate');
            $extCateDate = $extCateModel->where(array(
                'goods_id' => $id
            ))->select();

            /**** 查询商品相册 ****/
            $goodsPicModel = D('goods_pic');
            $goodsPicDate = $goodsPicModel->field('id,sm_pic')->where(array(
                'goods_id' => array('eq',$id)
            ))->select();

            //设置页面信息
            $this->assign(array(
                'cateDate' => $cateDate,
                '_page_title' => '修改商品',
                '_page_btn_name' => '商品列表',
                '_page_btn_link' => U('lst'),
                'extCateDate' => $extCateDate,
                'memberLevelDate' => $memberLevelDate,
                'memberPiceDate' => $memberPiceDate,
                'goodsAttrDate' => $goodsAttrDate,
                'goodsPicDate' => $goodsPicDate
            ));

            $this->display();
        }
        //删除商品
        public function delete(){
            $model = D('goods');
            $id = I('get.id');
            if (false !== $model->delete($id)){
                $this->success('删除成功！',U('lst'));
            }else{
                $this->error('删除失败！失败原因：'.$model->getError());
            }
        }
    }

?>