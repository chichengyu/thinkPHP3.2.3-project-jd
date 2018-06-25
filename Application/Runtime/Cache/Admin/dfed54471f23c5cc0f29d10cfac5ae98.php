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


<div class="main-div">
    <form name="main_form" method="POST" action="/index.php/Admin/Category/edit/id/21.html">
        <input type="hidden" name="id" value="<?php echo ($action_cate['id']); ?>">
        <table cellspacing="1" cellpadding="3" width="100%">
            <tr>
                <td class="label">上级分类：</td>
                <td>
                    <select name="parent_id" id="">
                        <option value="0">顶级分类</option>
                        <?php foreach($data as $k=>$val): if($val['id'] == $action_cate['id'] || in_array($val['id'],$children_cate)) continue; ?>
                            <option <?php if($action_cate['parent_id'] == $val['id']) echo 'selected';?> value="<?php echo ($val['id']); ?>"><?php echo str_repeat('-',6*$val['level']).$val['cate_name'];?></option>
                        <?php endforeach;?>
                        <!--<?php if(is_array($data)): foreach($data as $key=>$val): ?>-->
                            <!--<?php if($val['id'] == $action_cate['id'] && in_array($action_cate,$children_cate)):?>-->
                                <!--<?php continue;?>-->
                            <!--<?php endif;?>-->
                            <!--<option <?php if($action_cate['parent_id'] == $val['id']) echo 'selected';?> value="<?php echo ($val['id']); ?>"><?php echo str_repeat('-',6*$val['level']).$val['cate_name'];?></option>-->
                        <!--<?php endforeach; endif; ?>-->
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label">分类名称：</td>
                <td>
                    <input type="text" name="cate_name" value="<?php echo ($action_cate['cate_name']); ?>" size="60"/>
                </td>
            </tr>
            <tr>
                <td class="label">是否推荐到楼层：</td>
                <td>
                    <input type="radio" name="is_floor" value="是" <?php if($action_cate['is_floor'] == '是') echo 'checked';?>/> 是
                    <input type="radio" name="is_floor" value="否" <?php if($action_cate['is_floor'] == '否') echo 'checked';?>/> 否
                </td>
            </tr>
            <tr>
                <td colspan="99" align="center">
                    <input type="submit" class="button" value=" 确定 " />
                    <input type="reset" class="button" value=" 重置 " />
                </td>
            </tr>
        </table>
    </form>
</div>


<script>
</script>

<!-- 底部 -->
<div id="footer">
    共执行 7 个查询，用时 0.028849 秒，Gzip 已禁用，内存占用 3.219 MB<br />
    版权所有 &copy; 2005-2012 上海商派网络科技有限公司，并保留所有权利。</div>

</body>
</html>