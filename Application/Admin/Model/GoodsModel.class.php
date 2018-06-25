<?php
    namespace Admin\Model;
    use Think\Model;

    class GoodsModel extends Model{
        //add添加时允许接收的字段表单数据
        protected $insertFields = array('goods_name','mark_price','shop_price','goods_desc','is_on_sale','is_delete','brand_id','cate_id','type_id','promote_price','promote_start_date','promote_end_date','is_new','is_hot','is_best','sort_number','is_floor');
        //update修改时允许接收的字段表单数据
        protected $updateFields = array('id','goods_name','mark_price','shop_price','goods_desc','is_on_sale','is_delete','brand_id','cate_id','type_id','promote_price','promote_start_date','promote_end_date','is_new','is_hot','is_best','sort_number','is_floor');
        //定义验证规则
        protected $_validate = array(
            array('goods_name','require','商品名称不能为空！',1),
            array('mark_price','currency','市场价格必须是货币类型！',1),
            array('shop_price','currency','本店价格必须是货币类型！',1),
            array('cate_id','require','商品名称不能为空！',1),
        );

        //这个方法在添加之前会被自动调用 ->钩子方法
        protected function _before_insert(&$data,$option){//此处&$data必须是引用传递
            /******************* logo处理 *******************/
            //判断有没有选中图片
            /*if ($_FILES['logo']['error'] == 0){
                ########上传logo图片 ########
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 1024*1024 ;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath = './Public/Uploads/'; // 设置附件上传根目录
                $upload->savePath = 'Goods/'; // 设置附件上传（子）目录
                // 上传文件
                $info = $upload->upload();
                if(!$info) {
                    // 吧上传错误提示错误信息给model模型中的error属性
                    $this->error = $upload->getError();
                    return false;
                }else{
                    // 上传成功
                    #### logo图像处理 => 生成缩略图 ####
                    //拼接上传的原图的路径
                    $logo = $info['logo']['savepath'].$info['logo']['savename'];
                    //拼接出缩略图的路径
                    $mbiglogo = $info['logo']['savepath'].'mbig_'.$info['logo']['savename'];
                    $biglogo = $info['logo']['savepath'].'big_'.$info['logo']['savename'];
                    $midlogo = $info['logo']['savepath'].'mid_'.$info['logo']['savename'];
                    $smlogo = $info['logo']['savepath'].'sm_'.$info['logo']['savename'];

                    $image = new \Think\Image();
                    $image->open('./Public/Uploads/'.$logo);
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                    $image->thumb(700,700)->save('./Public/Uploads/'.$mbiglogo);
                    $image->thumb(350,350)->save('./Public/Uploads/'.$biglogo);
                    $image->thumb(130,130)->save('./Public/Uploads/'.$midlogo);
                    $image->thumb(50,50)->save('./Public/Uploads/'.$smlogo);
                    #### 把缩略图路径存到数据库 ###
                    $data['logo'] = $logo;
                    $data['mbig_logo'] = $mbiglogo;
                    $data['big_logo'] = $biglogo;
                    $data['mid_logo'] = $midlogo;
                    $data['sm_logo'] = $smlogo;
                }
            }*/

            //注意：uploadOne是我们自己封装好的函数
            //(Application\Common\Common\function.php)
            if ($_FILES['logo']['error'] == 0){
                $res = uploadOne('logo','Goods',array(
                    array(700,700),
                    array(350,350),
                    array(150,150),
                    array(50,50)
                ));
                if ($res['ok'] == 1){
                    #### 把缩略图路径存到数据库 ###
                    $data['logo'] = $res['images'][0];
                    $data['mbig_logo'] = $res['images'][1];
                    $data['big_logo'] = $res['images'][2];
                    $data['mid_logo'] = $res['images'][3];
                    $data['sm_logo'] = $res['images'][4];
                }else{
                    $this->error = $res['error'];
                    return false;
                }
            }

            //获取当前时间并添加到表单中
            $data['addtime'] = date('Y-m-d H:i:s',time());
            $data['goods_desc'] = removeXSS($_POST['goods_desc']);
        }
        //添加之后的钩子函数
        protected function _after_insert($data,$option){
            /****** 处理商品相册 ******/
            $goods_id = $data['id'];
            //处理接收的多个图片
            if (isset($_FILES)){
                //接收上传的图片
                $picsArr = array();
                $pic = $_FILES['goods_pic'];
                foreach ($pic['name'] as $k=>$v) {
                    $picsArr[] = array(
                       'name' => $v,
                       'type' => $pic['type'][$k],
                       'tmp_name' => $pic['tmp_name'][$k],
                       'error' => $pic['error'][$k],
                       'size' => $pic['size'][$k],
                    );
                }
                // 把处理好的数组赋给$_FILES，因为uploadOne函数上传图片是到$_FILES中找图片
                $_FILES = $picsArr;
                $goodsPicModel = D('goods_pic');
                foreach ($picsArr as $k=>$v) {
                    if ($v['error'] == 0){
                        //调用封装的上传图片函数,循环上传
                        $res = uploadOne($k,'Goods',$thumb=array(
                            array(700,700),
                            array(350,350),
                            array(150,150)
                        ));
                        if ($res['ok'] == 1){
                            $goodsPicModel->add(array(
                                'pic' => $res['images'][0], // 原图地址
                                'sm_pic' => $res['images'][3],        // 第二个缩略图地址
                                'mid_pic' => $res['images'][2],      // 第三个缩略图地址
                                'big_pic' => $res['images'][1],     // 第四个缩略图地址
                                'goods_id' => $goods_id
                            ));
                        }
                    }
                }
            }

            /****** 处理商品属性 ******/
            $attrValue = I('post.attr_value');
            $goodsAttrModel = D('goods_attr');
            foreach ($attrValue as $k=>$v){
                foreach ($v as $val){
                    $goodsAttrModel->add(array(
                        'attr_values' => $val,
                        'attr_id' => $k,
                        'goods_id' => $data['id']
                    ));
                }
            }
            /****** 处理扩展分类 ******/
            $ext_cate_id = I('post.ext_cate_id');
            if ($ext_cate_id){
                $gcModel = D('goods_cate');
                foreach ($ext_cate_id as $k=>$v){
                    if (empty($v)){
                        continue;
                    }
                    $gcModel->add(array(
                        'cate_id' => $v,
                        'goods_id' => $data['id']
                    ));
                }
            }
            /****** 处理会员价格 ******/
            $memberPrice = I('post.member_price');
            $memberPriceModel = D('MemberPrice');
            foreach ($memberPrice as $k=>$v){
                $v = (float)$v;
                if ($v > 0){
                    $memberPriceModel->add(array(
                        'price' => $v,
                        'level_id' => $k,
                        'goods_id' => $data['id']
                    ));
                }
            }
        }

        //修改商品(更新)之前的钩子函数
        protected function _before_update(&$data,$option){
            //当前修改的商品id
            $goods_id = $option['where']['id'];

            /****** 修改商品属性 *****/
            $goodsAttrId = I('post.goods_attr_id');
            $attrValues = I('post.attr_value');
            $goodsAttrModel = D('goods_attr');
            $_i = 0;
            foreach ($attrValues as $k=>$v){
                foreach ($v as $v1){
                    //判断是新添加的还是修改原来的,根据商品属性id判断
                    //为空表示新加的,不为空表示商品属性表有这个id表示修改
                    if ($goodsAttrId[$_i] == '' && $v1){
                        $goodsAttrModel->add(array(
                            'attr_values' => $v1,
                            'attr_id' => $k,
                            'goods_id' => $goods_id
                        ));
                    }else{
                        $goodsAttrModel->where(array(
                            'id' => array('eq',$goodsAttrId[$_i])
                        ))->setField('attr_values',$v1);
                    }
                    $_i++;
                }
            }

            /**** 修改扩展分类id ****/
            $ext_cate_id = I('post.ext_cate_id');
            if ($ext_cate_id){
                /**** 先删除原来的扩展分类,在添加新的 ****/
                $extGoodsCateModel = D('goods_cate');
                $extGoodsCateModel->where(array(
                    'goods_id' => $goods_id
                ))->delete();
                foreach ($ext_cate_id as $v){
                    if (empty($v)){
                        continue;
                    }
                    $extGoodsCateModel->add(array(
                        'cate_id' => $v,
                        'goods_id' => $goods_id
                    ));
                }
            }

            /********* 修改商品会员 ***********/
            $goodsMemberPriceArr = I('post.member_price');
            $goodsMenberPriceModel = D('member_price');
            //dump($goodsMemberPriceArr);die;
            //先删除
            $goodsMenberPriceModel->where(array(
                'goods_id' => array('eq',$goods_id)
            ))->delete();
            //先查询该商品是否有会员价格
            foreach ($goodsMemberPriceArr as $k=>$v) {
                $v = (float)$v;
                if ($v > 0){
                    //再添加
                    $goodsMenberPriceModel->add(array(
                        'price' => $v,
                        'level_id' => $k,
                        'goods_id' => $goods_id
                    ));
                }
            }
            //dump(I('post.member_price'));die;


            /********* logo处理 ***********/
            //判断有没有选中图片
            /*  if ($_FILES['logo']['error'] == 0){
                ########## 上传logo图片 ###########
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 1024*1024 ;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath = './Public/Uploads/'; // 设置附件上传根目录
                $upload->savePath = 'Goods/'; // 设置附件上传目录

                // 上传文件
                $info = $upload->upload();
                if(!$info) {
                    // 上传错误提示错误信息
                    $this->error = $upload->getError();
                    return false;
                }else{
                    // 上传成功
                    ########## 图像处理 => 生产缩略图 ##########
                    //拼接上传的图片(原图)路径
                    $logo = $info['logo']['savepath'].$info['logo']['savename'];
                    //拼接出缩略图的路径
                    $mbiglogo = $info['logo']['savepath'].'mbig_'.$info['logo']['savename'];
                    $biglogo = $info['logo']['savepath'].'big_'.$info['logo']['savename'];
                    $midlogo = $info['logo']['savepath'].'mid_'.$info['logo']['savename'];
                    $smlogo = $info['logo']['savepath'].'sm_'.$info['logo']['savename'];

                    $image = new \Think\Image();
                    $image->open('./Public/Uploads/'.$logo);
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                    $image->thumb(700,700)->save('./Public/Uploads/'.$mbiglogo);
                    $image->thumb(350,350)->save('./Public/Uploads/'.$biglogo);
                    $image->thumb(130,130)->save('./Public/Uploads/'.$midlogo);
                    $image->thumb(50,50)->save('./Public/Uploads/'.$smlogo);
                    ########## 把缩略图路径保存到数据库 ##########
                    $data['logo'] = $logo;
                    $data['mbig_logo'] = $mbiglogo;
                    $data['big_logo'] = $biglogo;
                    $data['mid_logo'] = $midlogo;
                    $data['sm_logo'] = $smlogo;
                    ########## 删除原来的图片 ##########
                    //获取隐藏域传递的id
                    //这时其实第二个参数$option中有id,出于优化考虑,直接使用参数快于I函数(获取、过滤要花费时间)
                    //$id = I('post.id');
                    $id = $option['where']['id'];
                    $oldLogo = $this->field('logo,mbig_logo,big_logo,mid_logo,sm_logo')->find($id);
                    unlink('./Public/Uploads/'.$oldLogo['logo']);
                    unlink('./Public/Uploads/'.$oldLogo['sm_logo']);
                    unlink('./Public/Uploads/'.$oldLogo['mid_logo']);
                    unlink('./Public/Uploads/'.$oldLogo['big_logo']);
                    unlink('./Public/Uploads/'.$oldLogo['mbig_logo']);
                }
            }*/
            if ($_FILES['logo']['error'] == 0){
                //注意：uploadOne是我们自己封装好的函数
                //(Application\Common\Common\function.php)
                $res = uploadOne('logo','Goods',array(
                    array(700,700),
                    array(350,350),
                    array(150,150),
                    array(50,50)
                ));
                if ($res['ok'] == 1){
                    ########## 把缩略图路径保存到数据库 ##########
                    $data['logo'] = $res['images'][0];
                    $data['mbig_logo'] = $res['images'][1];
                    $data['big_logo'] = $res['images'][2];
                    $data['mid_logo'] = $res['images'][3];
                    $data['sm_logo'] = $res['images'][4];
                    ########## 删除原来的图片(使用自己封装的方法) ##########
                    //获取隐藏域传递的id
                    //这时其实第二个参数$option中有id,出于优化考虑,直接使用参数快于I函数(获取、过滤要花费时间)
                    //$id = I('post.id');
                    $id = $option['where']['id'];
                    $oldLogo = $this->field('logo,mbig_logo,big_logo,mid_logo,sm_logo')->find($id);
                    /**自定义的删除原图函数**/
                    deleteImage($oldLogo);
                }else{
                    $this->error = $res['error'];
                    return false;
                }
            }

            /****** 修改商品相册 *****/
            if (isset( $_FILES['goods_pic'])){
                $pic = $_FILES['goods_pic'];
                $goodsPicArr = array();
                foreach ($pic['name'] as $k=>$v) {
                    $goodsPicArr[] = array(
                        'name' => $v,
                        'type' => $pic['type'][$k],
                        'tmp_name' => $pic['tmp_name'][$k],
                        'error'=>$pic['error'][$k],
                        'size' =>$pic['size'][$k]
                    );
                }
                // 把处理好的数组赋给$_FILES，因为uploadOne函数上传图片是到$_FILES中找图片
                $_FILES = $goodsPicArr;
                $goodsPicModel = D('goods_pic');
                foreach ($goodsPicArr as $k=>$v) {
                    //判断是否上传了新的相册图片
                    if ($v['error'] == 0){
                        //然后在循环添加新的相册图片
                        $res = uploadOne($k,'Goods',array(
                            array(700,700),
                            array(350,350),
                            array(150,150)
                        ));
                        if ($res['ok'] == 1){
                            $goodsPicModel->add(array(
                                'pic' => $res['images'][0],        // 原图地址
                                'sm_pic' => $res['images'][3],     // 第二个缩略图地址
                                'mid_pic' => $res['images'][2],    // 第三个缩略图地址
                                'big_pic' => $res['images'][1],    // 第四个缩略图地址
                                'goods_id' => $goods_id
                            ));
                        }
                    }
                }
            }

            $data['addtime'] = date('Y-m-d H:i:s',time());
            $data['goods_desc'] = removeXSS($_POST['goods_desc']);
        }
        //删除商品
        protected function _before_delete($option){
            $id = $option['where']['id'];

            /**** 删除商品相册图片 ****/
            $goodsPicModel = D('goods_pic');
            //先删除图片
            $goodsPic = $goodsPicModel->field('pic,sm_pic,mid_pic,big_pic')
                ->where(array(
                'goods_id' => array('eq',$id)
            ))->select();
            foreach ($goodsPic as $v) {
                deleteImage($v);
            }
            //在删除数据
            $goodsPicModel->where(array(
                'goods_id' => array('eq',$id)
            ))->delete();

            /**** 删除商品库存量 ****/
            $goodsNumberModel = D('goods_number');
            $goodsNumberModel->where(array(
                'goods_id' =>array('eq',$id)
            ))->delete();

            /**** 删除商品属性 ****/
            $goodsAttrModel = D('goods_attr');
            $goodsAttrModel->where(array(
                'goods_id' => $id
            ))->delete();

            /****** 删除数据之前先触动钩子函数删除原来的图片 ******/
            $oldLogo = $this->field('logo,sm_logo,mid_logo,big_logo,mbig_logo')->find($id);
            deleteImage($oldLogo);

            /**** 删除扩展分类 ****/
            $extCateModel = D('goods_cate');
            $extCateModel->where(array(
                'goods_id' => array('eq',$id)
            ))->delete();
        }

        //查询商品列表
        public function search($perpage=5){
            /************ 搜索 ************/
            $where = array();
            $gn = I('get.gn');
            if ($gn)
                $where['goods_name'] = array('like',"%$gn%");

            $fp = I('get.fp');
            $tp = I('get.tp');
            if ($fp && $tp)
                $where['shop_price'] = array('between',array($fp,$tp));
            elseif ($fp)
                $where['shop_price'] = array('egt',$fp);
            elseif ($tp)
                $where['shop_price'] = array('elt',$tp);

            $ios = I('get.ios');
            if ($ios)
                $where['is_on_sale'] = array('eq',$ios);

            $fa = I('get.fa');
            $ta = I('get.ta');
            if ($fa && $ta)
                $where['addtime'] = array('between',array($fa,$ta));
            elseif ($fa)
                $where['addtime'] = array('egt',$fa);
            elseif ($ta)
                $where['addtime'] = array('elt',$ta);
            //品牌搜索
            $brandId = I('get.brand_id');
            if ($brandId){
                $where['brand_id'] = array('eq',$brandId);
            }
            // 分类搜索
            $cateId = I('get.cate_id');
            if ($cateId){
                //取出所有子分类的id
                //$cateModel = D('category');
                //$cateChild_id = $cateModel->getChild($cateId);
                //把当前分类id和子分类id放在一个数组里
                //$cateChild_id[] = $cateId;
                //按照这个分类id的数组进行搜索商品
                //$where['cate_id'] = array('in',$cateChild_id);
                ###使用封装的方法进行搜索
                $cateChild_id = $this->getGoodsIdByCateId($cateId);
                if ($cateChild_id){
                    $where['a.id'] = array('in',$cateChild_id);
                }
            }

            /************* 分页 *************/
            // 查询满足要求的总记录数
            $count = $this->alias('a')->where($where)->count();

            // 实例化分页类 传入总记录数和每页显示的记录数(25)
            $Page = new \Think\Page($count,$perpage);
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $show = $Page->show();// 分页显示输出

            /*********** 排序 ************/
            $orderby = 'a.id';
            $orderway = 'desc';
            $odby = I('get.odby');
            if ($odby){
                if ($odby == 'id_asc')
                    $orderway = 'asc';
                elseif ($odby == 'price_desc')
                    $orderby = 'shop_price';
                elseif ($odby == 'price_asc'){
                    $orderby = 'shop_price';
                    $orderway = 'asc';
                }
            }
            /************* 联表查询去数据 *************/
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = $this->field('a.*,b.brand_name,c.cate_name,group_concat(e.cate_name separator "、") ext_cate_name')      //显示那些字段
                ->order("$orderby $orderway")                   //排序
                ->where($where)                                 //搜索
                ->alias('a')                                                 //别名
                ->join('LEFT JOIN __BRAND__ b ON a.brand_id=b.id
                        LEFT JOIN __CATEGORY__ c ON a.cate_id=c.id
                        LEFT JOIN __GOODS_CATE__ d ON a.id=d.goods_id
                        LEFT JOIN __CATEGORY__ e ON d.cate_id=e.id')        //联表查询
                ->group('a.id')                                 //分组
                ->limit($Page->firstRow.','.$Page->listRows)    //分页
                ->select();


            //$list = $this->query($sql);
            //$this->field('a.*,b.brand_name,c.cate_name,group_concat(e.cate_name separator "、") ext_cate_name')
            /*
             *  LEFT JOIN __BRAND__ b ON a.brand_id=b.id    取品牌名称
             *  LEFT JOIN __CATEGORY__ c ON a.cate_id=c.id  取主分类名称
             *  LEFT JOIN __GOODS_CATE__ d ON a.id=d.goods_id
                LEFT JOIN __CATEGORY__ e ON d.cate_id=e.id  取商品扩展分类名称
             *
             * */

            return array(
                'list' => $list,
                'page' => $show
            );
        }
        /***** 取出一个分类下所有商品的id，即考虑主分类也考虑扩展分类 *****/
        public function getGoodsIdByCateId($cateId){
            /****** 查询主分类 ******/
            //取出当前主分类下所有子分类的id
            $cateModel = D('Admin/Category');
            $cateChild_id = $cateModel->getChild($cateId);
            //把当前分类id和子分类id放在一个数组里
            $cateChild_id[] = $cateId;
            //按照这个分类id的数组进行搜索商品id
            $goods_id = $this->alias('a')->field('a.id')->where(array(
                'cate_id' =>array('IN',$cateChild_id)
            ))->select();

            /***** 取出扩展分类的id *****/
            //就是查找扩展分类中的cate_id有没有与当前的主分类及其下子分类的id相等的商品
            $extCateModel = D('goods_cate');
            //查找扩展分类id有没有在主类及其下的子分类id的数组中,在 就表示这个是主分类的同时也是扩展分类,取出这个商品goods_id
            $extCateGoodsId = $extCateModel->field('distinct goods_id id')->where(array(
                'cate_id' => array('IN',$cateChild_id)
            ))->select();

            if ($goods_id && $extCateGoodsId){
                //然后把扩展分类得到的商品id与查询主分类及其子分类数组得到所有商品id进行合并,并且会有重复的,需要去重(因为主分类也可能是扩展分类中的一个,所以肯定会重复)
                $goods_id = array_merge($goods_id,$extCateGoodsId);
            }elseif ($extCateGoodsId){
                $goods_id = $extCateGoodsId;
            }

            //把二维数组转一维数组
            $id = array();
            foreach ($goods_id as $k=>$v){
                //进行数组去重
                if (!in_array($v['id'],$id)){
                    $id[] = $v['id'];
                }
            }
            return $id;
        }


    }

?>