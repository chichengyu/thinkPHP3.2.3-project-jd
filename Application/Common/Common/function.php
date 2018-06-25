<?php
/**
 * Created by PhpStorm.
 * User: 小池
 * Date: 2018/5/1
 * Time: 18:51
 */


/****【正则删除】 从当前url中去掉某个参数后的url地址 ****/
/* @param  $param 要删除参数名
 * */
function filterUrl($param){
    //获取当前url地址
    $url = $_SERVER['PHP_SELF'];
    //正则
    $reg = "/\/$param\/[^\/]+/";
    return preg_replace($reg,'',$url);
}


/**** 为订单神生成一个支付宝支付按钮 ****/
/*
 * @param $orderId 订单id
 * @param $btnName 按钮名称
 * */
function makeAlipayBtn($orderId,$btnName = '去支付宝支付'){
    return require('./alipay/alipayapi.php');
}



/**** 删除图片 ****/
function deleteImage($imagePath = array()){
    $image = C('IMAGE_CONFIG');
    if ($imagePath){
        foreach ($imagePath as $v){
            unlink($image['rootPath'].$v);
        }
    }
}
/**** uploadOne => 图片上传函数 ****/
/*
 * @param   $imageName        => 表单提交的file文件的name属性
 * @dirName $dirName          => 上传（子）目录
 * @param   $thumb            => 是否生成缩略图
 * */
function uploadOne($imageName,$dirName,$thumb=array()){
    /**
     * 上传图片并生成缩略图
     * 用法：
     * $ret = uploadOne('logo', 'Goods', array(
            array(600, 600),
            array(300, 300),
            array(100, 100),
        ));
        返回值：
        if($ret['ok'] == 1){
            $ret['images'][0];   // 原图地址
            $ret['images'][1];   // 第一个缩略图地址
            $ret['images'][2];   // 第二个缩略图地址
            $ret['images'][3];   // 第三个缩略图地址
        }else{
            $this->error = $ret['error'];
            return FALSE;
        }
         *
    */
    if (isset($_FILES[$imageName]) && $_FILES[$imageName]['error'] == 0){
        $image_config = C('IMAGE_CONFIG');
        $upload = new \Think\Upload(array(
            'maxSize' => $image_config['maxsize'],// 设置附件上传大小
            'exts'    => $image_config['exts'],// 设置附件上传类型
            'rootPath' => $image_config['rootPath'] // 设置附件上传根目录(相对路径),上传相对于电脑硬盘,所以是相对路径
        ));// 实例化上传类
        $upload->savePath = $dirName.'/'; // 设置附件上传（子）目录
        // 上传文件
        // 上传时指定一个要上传的图片的名称，否则【多文件上传时】会把表单中所有的图片都同时处理，循环后面的图片再想上传时却再找不到图片了
        $info = $upload->upload(array($imageName => $_FILES[$imageName]));
        if(!$info) {
            // 吧上传错误提示错误信息给model模型中的error属性
            return array(
                'ok'   =>0,
                'error'=>$upload->getError()
            );
        }else {
            // 上传成功
            $res['ok'] = 1;//状态,用于模型中判断是否上传成功
            /** 原图路径 **/
            $res['images'][0] = $logo = $info[$imageName]['savepath'].$info[$imageName]['savename'];

            /******* 生成缩略图 ******/
            $image = new \Think\Image();
            //判断是否生成缩略图
            if ($thumb){
                //循环生成缩略图
                foreach ($thumb as $k => $v){
                    $image->open($image_config['rootPath'].$logo);
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                    $res['images'][$k+1] = $info[$imageName]['savepath'].'thumb_'.$k.'_'.$info[$imageName]['savename'];
                    $image->thumb($v[0],$v[1])->save($image_config['rootPath'].$res['images'][$k+1]);
                }
            }
            return $res;
        }
    }
}


//有选择性的过滤 => 说明：行呢个非常低,尽量少用  商品描述过滤
function removeXSS($data){
    require_once './Public/htmlpurifier/HTMLPurifier.auto.php';

    $_clean_xss_config = HTMLPurifier_Config::createDefault();
    $_clean_xss_config->set('Core.Encoding', 'UTF-8');
    //设置保留的标签
    $_clean_xss_config->set('HTML.Allowed','div,b,p,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
    $_clean_xss_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
    $_clean_xss_config->set('HTML.TargetBlank', TRUE);
    $_clean_xss_obj = new HTMLPurifier($_clean_xss_config);
    //执行过滤
   return $_clean_xss_obj->purify($data);
}

//生成图片
function showImage($url,$width='',$height=''){
    $image = C('IMAGE_CONFIG');
    echo "<img src='{$image['viewPath']}$url' width='$width' height='$height'/>";
}


//生成下拉选框select
/*
 * 使用一张表的数据生成下拉选框
 * @param $tableName        表名
 * @param $selectName       select下拉选框的name名
 * @param $valueFieldName   查询那个字段的值作为option选项的vlaue值
 * @param $textFieldName    查询那个字段的值作为option选项显示的内容
 * @param $selected         是否选中
 * */
function buildSelect($tableName,$selectName,$valueFieldName,$textFieldName,$selected=''){
    $model = D("$tableName");
    $data = $model->field("$valueFieldName,$textFieldName")->select();
    $select = "<select name='$selectName'><option value=''>请选择</option>";

    foreach ($data as $k=>$v){
        if ($selected && $selected == $v[$valueFieldName]){
            $default_selected = 'selected';
        }else{
            $default_selected = '';
        }
        $select .= "<option $default_selected value='$v[$valueFieldName]'>$v[$textFieldName]</option>";

    }
    $select .= "</select>";

    echo $select;
}
/*********检测输入的验证码是否正确，$code为用户输入的验证码字符串*********/
function check($code, $id = ''){
    $verify = new \Think\Verify();
    $verify->reset = false;
    return $verify->check($code, $id);
}


?>