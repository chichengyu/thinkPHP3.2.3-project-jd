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


<div class="form-div">
    <form action="/index.php/Admin/Goods/lst" name="searchForm">
        <p>
            主分类：
            <select name="cate_id" id="">
                <option value="">请选择</option>
                <?php if(is_array($cateDate)): foreach($cateDate as $key=>$val): ?><option <?php if(I('get.cate_id')==$val['id']) echo 'selected';?> value="<?php echo ($val['id']); ?>"><?php echo str_repeat('-',3*$val['level']).$val['cate_name'];?></option><?php endforeach; endif; ?>
            </select>
        </p>
        <p>
            品  牌：<?php buildSelect('brand','brand_id','id','brand_name',I('get.brand_id'));?>
        </p>
        <p>
            商品价格：<input type="text" value="<?php echo I('get.gn');?>" name="gn" size="50">
        </p>
        <p>
            价  格：
            从 <input type="text" value="<?php echo I('get.fp');?>" name="fp">
            到 <input type="text" value="<?php echo I('get.tp');?>" name="tp">
        </p>
        <p>
            是否上架：
            <?php $ios = I('get.ios');?>
            <input type="radio" name="ios" value="" <?php if($ios=='') echo 'checked';?> >全部
            <input type="radio" name="ios" value="是" <?php if($ios=='是') echo 'checked';?>>上架
            <input type="radio" name="ios" value="否" <?php if($ios=='否') echo 'checked';?>>下架
        </p>
        <p>
            添加时间：
            从 <input id="fa" type="text" value="<?php echo I('get.fa');?>" name="fa">
            到 <input id="ta" type="text" value="<?php echo I('get.ta');?>" name="ta">
        </p>
        <p>
            排序方式：
            <?php $odby=I('get.odby','id_desc');?>
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="odby" value="id_desc" <?php if($odby=='id_desc') echo checked;?>>以添加时间降序
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="odby" value="id_asc" <?php if($odby=='id_asc') echo checked;?>>以添加时间升序
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="odby" value="price_desc" <?php if($odby=='price_desc') echo checked;?>>以价格降序
            <input onclick="this.parentNode.parentNode.submit();" type="radio" name="odby" value="price_asc" <?php if($odby=='price_asc') echo checked;?>>以价格升序
        </p>
        <input type="submit" value=" 搜索 " class="button" />
    </form>
</div>

<!-- 商品列表 -->
<form method="post" action="" name="listForm" onsubmit="">
    <div class="list-div" id="listDiv">
        <table cellpadding="3" cellspacing="1">
            <tr>
                <th>编号</th>
                <th>主分类</th>
                <th>扩展分类</th>
                <th>品牌</th>
                <th>商品名称</th>
                <th>logo</th>
                <th>市场价格</th>
                <th>本店价格</th>
                <th>是否上架</th>
                <th>添加时间</th>
                <th>操作</th>
            </tr>
            <?php if(is_array($list)): foreach($list as $key=>$val): ?><tr class="tron">
                <td align="center"><?php echo ($val["id"]); ?></td>
                <td align="center"><?php echo ($val["cate_name"]); ?></td>
                <td align="center"><?php echo ($val["ext_cate_name"]); ?></td>
                <td align="center"><?php echo ($val["brand_name"]); ?></td>
                <td align="center" class="first-cell"><span><?php echo ($val["goods_name"]); ?></span></td>
                <td align="center"><?php showImage($val['sm_logo']);?></td>
                <td align="center"><span onclick=""><?php echo ($val["mark_price"]); ?></span></td>
                <td align="center"><span><?php echo ($val["shop_price"]); ?></span></td>
                <td align="center"><span><?php echo ($val["is_on_sale"]); ?></span></td>
                <td align="center"><span><?php echo ($val["addtime"]); ?></span></td>
                <td align="center">
                    <a href="<?php echo U('goodsNumber?id='.$val['id'])?>">库存量</a>
                    <a href="<?php echo U('edit?id='.$val['id'])?>">修改</a>
                    <a onclick="return confirm('你确定要删除吗？')" href="<?php echo U('delete?id='.$val['id']);?>">删除</a>
                </td>
            </tr><?php endforeach; endif; ?>
        </table>


    <!-- 分页开始 -->
        <table id="page-table" cellspacing="0">
            <tr>
                <td width="80%">&nbsp;</td>
                <td align="center" nowrap="true">
                    <?php echo ($page); ?>
                </td>
            </tr>
        </table>
    <!-- 分页结束 -->
    </div>
</form>



<!-- 输入框日期时间处理 -->
<link href="/Public/datetimepicker/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/Public/datetimepicker/jquery-1.7.2.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/datetimepicker/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/datetimepicker/datepicker-zh_cn.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="/Public/datetimepicker/time/jquery-ui-timepicker-addon.min.css" />
<script type="text/javascript" src="/Public/datetimepicker/time/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="/Public/datetimepicker/time/i18n/jquery-ui-timepicker-addon-i18n.min.js"></script>
<script>
    $.timepicker.setDefaults($.timepicker.regional['zh-CN']);//设置中文
    $("#fa").datetimepicker();
    $("#ta").datetimepicker();
</script>

<!-- 引入自定义代码高亮tron.js -->
<script type="text/javascript" src="/Public/Admin/Js/tron.js"></script>

<!-- 底部 -->
<div id="footer">
    共执行 7 个查询，用时 0.028849 秒，Gzip 已禁用，内存占用 3.219 MB<br />
    版权所有 &copy; 2005-2012 上海商派网络科技有限公司，并保留所有权利。</div>

</body>
</html>