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



<!-- 分类列表 -->
<form method="post" action="" name="listForm" onsubmit="">
    <div class="list-div" id="listDiv">
        <table cellpadding="3" cellspacing="1">
            <tr>
               <th>分类名称</th>
                <th>操作</th>
            </tr>
            <?php if(is_array($data)): foreach($data as $key=>$val): ?><tr class="tron">
                <td><?php echo str_repeat('-',8*$val['level']); echo ($val["cate_name"]); ?></td>
                <td align="center">
                    <a href="<?php echo U('edit?id='.$val['id'])?>">修改</a>
                    <a onclick="return confirm('你确定要删除吗？')" href="<?php echo U('delete?id='.$val['id']);?>">删除</a>
                </td>
            </tr><?php endforeach; endif; ?>
        </table>
    </div>
</form>



<script type="text/javascript" src="/Public/datetimepicker/jquery-1.7.2.min.js"></script>
<!-- 引入自定义代码高亮tron.js -->
<script type="text/javascript" src="/Public/Admin/Js/tron.js"></script>

<!-- 底部 -->
<div id="footer">
    共执行 7 个查询，用时 0.028849 秒，Gzip 已禁用，内存占用 3.219 MB<br />
    版权所有 &copy; 2005-2012 上海商派网络科技有限公司，并保留所有权利。</div>

</body>
</html>