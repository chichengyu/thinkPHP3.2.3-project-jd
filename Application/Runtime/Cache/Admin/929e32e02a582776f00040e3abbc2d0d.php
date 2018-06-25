<?php if (!defined('THINK_PATH')) exit();?><!-- 头部 -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>ECSHOP 管理中心 - 商品列表 </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="/Public/Admin/Styles/general.css" rel="stylesheet" type="text/css" />
    <link href="/Public/Admin/Styles/main.css" rel="stylesheet" type="text/css" />
</head>
<script src="/Public/Admin/Js/jquery.min.js"></script>
<body>
<h1>
    <?php if($_page_btn_name):?>
    <span class="action-span"><a href="<?php echo ($_page_btn_link); ?>"><?php echo ($_page_btn_name); ?></a></span>
    <?php endif;?>
    <span class="action-span1"><a href="/index.php/Admin/index/index">ECSHOP 管理中心</a></span>
    <span id="search_id" class="action-span1"> - <?php echo ($_page_title); ?> </span>
    <div style="clear:both"></div>
</h1>



<form method="post" action="/index.php/Admin/Goods/goodsNumber/id/29.html" name="listForm" onsubmit="">
    <div class="list-div" id="listDiv">
        <table cellpadding="3" cellspacing="1">
            <tr>
                <?php foreach($NewGoodsAttrDate as $k=>$v):?>
                    <th><?php echo ($k); ?></th>
                <?php endforeach;?>
                <th width="150">库存量</th>
                <th width="150">操作</th>
            </tr>
            <?php if($goodsNumberDate):?>
                <?php foreach($goodsNumberDate as $key=>$val): $attr_id_list = explode(',',$val['goods_attr_id_list']); ?>
                    <tr class="tron">
                        <?php
 $gaCount = count($NewGoodsAttrDate); foreach($NewGoodsAttrDate as $k=>$v):?>
                            <td align="center">
                                <select name="goods_attr_id_list[]" id="">
                                    <option value="">请选择</option>
                                    <?php foreach($v as $v1): if(in_array($v1['id'],$attr_id_list)){ $select = 'selected'; }else{ $select = ''; } ?>
                                        <option <?php echo $select;?> value="<?php echo ($v1['id']); ?>"><?php echo ($v1['attr_values']); ?></option>
                                    <?php endforeach;?>
                                </select>
                            </td>
                        <?php endforeach;?>
                        <td align="center">
                            <input type="text" name="goods_number[]" value="<?php echo ($val['goods_number']); ?>">
                        </td>
                        <td align="center">
                            <input onclick="addNewTr(this)" type="button" value="<?php echo $key==0?'+':'-' ;?>">
                        </td>
                    </tr>
                <?php endforeach;?>
            <?php else:?>
                <tr>
                    <?php
 $gaCount = count($NewGoodsAttrDate); foreach($NewGoodsAttrDate as $k=>$v):?>
                    <td align="center">
                        <select name="goods_attr_id_list[]" id="">
                            <option value="">请选择</option>
                            <?php foreach($v as $v1):?>
                            <option value="<?php echo ($v1['id']); ?>"><?php echo ($v1['attr_values']); ?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                    <?php endforeach;?>
                    <td align="center">
                        <input type="text" name="goods_number[]" value="">
                    </td>
                    <td align="center">
                        <input onclick="addNewTr(this)" type="button" value="+">
                    </td>
                </tr>
            <?php endif;?>
            <tr id="btn">
                <td align="center" colspan="<?php echo $gaCount+2;?>">
                    <input type="submit" value="提交" >
                </td>
            </tr>
        </table>
    </div>
</form>


<!-- 引入自定义代码高亮tron.js -->
<script type="text/javascript" src="/Public/Admin/Js/tron.js"></script>
<script>
    function addNewTr(Dom){
        var tr = $(Dom).parent().parent();
        if ($(Dom).val() == '+'){
            var newTr = tr.clone();
            newTr.find('input[type="button"]').val('-');
            $('#btn').before(newTr);
        }else{
            tr.remove();
        }
    }
</script>

<!-- 底部 -->
<div id="footer">
    共执行 7 个查询，用时 0.028849 秒，Gzip 已禁用，内存占用 3.219 MB<br />
    版权所有 &copy; 2005-2012 上海商派网络科技有限公司，并保留所有权利。</div>

</body>
</html>