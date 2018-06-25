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


<div class="tab-div">
    <div id="tabbar-div">
        <p>
            <span class="tab-front">基本信息</span>
            <span class="tab-back">商品描述</span>
            <span class="tab-back">会员价格</span>
            <span class="tab-back">商品属性</span>
            <span class="tab-back">商品相册</span>
        </p>
    </div>
    <div id="tabbody-div">
        <form enctype="multipart/form-data" action="/index.php/Admin/Goods/add.html" method="post">
            <!-- 基本信息 -->
            <table width="90%" class="btn-table" align="center">
                <tr>
                    <td class="label">主分类：</td>
                    <td>
                        <select name="cate_id" id="">
                            <option value="">请选择主分类</option>
                            <?php foreach($cateDate as $val):?>
                                <option value="<?php echo ($val['id']); ?>"><?php echo str_repeat('-',3*$val['level']).$val['cate_name'];?></option>
                            <?php endforeach;?>
                        </select>
                        <span class="require-field">*</span>
                    </td>
                </tr>
                <tr>
                    <td class="label">扩展分类：<input type="button" onclick="$('#cate_list').append($('#cate_list').find('select').eq(0).clone())" value="添加一个"></input></td>
                    <td id="cate_list">
                        <select name="ext_cate_id[]" id="">
                            <option value="">请选择</option>
                            <?php foreach($cateDate as $val):?>
                            <option value="<?php echo ($val['id']); ?>"><?php echo str_repeat('-',3*$val['level']).$val['cate_name'];?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="label">所有品牌：</td>
                    <td>
                        <?php buildSelect('brand','brand_id','id','brand_name');?>
                        <!--<select name="brand_id" id="">-->
                            <!--<option value="">请选择</option>-->
                            <!--<?php if(is_array($brandDate)): foreach($brandDate as $key=>$val): ?>-->
                                <!--<option value="<?php echo ($val['id']); ?>"><?php echo ($val['brand_name']); ?></option>-->
                            <!--<?php endforeach; endif; ?>-->
                        <!--</select>-->
                    </td>
                </tr>
                <tr>
                    <td class="label">商品名称：</td>
                    <td><input type="text" name="goods_name" value=""size="30" />
                    <span class="require-field">*</span></td>
                </tr>
                <tr>
                    <td class="label">LOGO：</td>
                    <td><input type="file" name="logo"/></td>
                </tr>
                <tr>
                    <td class="label">本店售价：</td>
                    <td>
                        <input type="text" name="shop_price" value="0" size="20"/>
                        <span class="require-field">*</span>
                    </td>
                </tr>
                <tr>
                    <td class="label">是否上架：</td>
                    <td>
                        <input type="radio" name="is_on_sale" value="1"/> 是
                        <input type="radio" name="is_on_sale" value="0"/> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">市场售价：</td>
                    <td>
                        <input type="text" name="mark_price" value="0" size="20" />
                    </td>
                </tr>
                <tr>
                    <td class="label">促销价格：</td>
                    <td>
                        价格：￥<input type="text" name="promote_price"/>元
                        开始时间：<input type="text" id="promote_start_date" name="promote_start_date"/>
                        结束时间：<input type="text" id="promote_end_date" name="promote_end_date"/>
                    </td>
                </tr>
                <tr>
                    <td class="label">是否新品：</td>
                    <td>
                        <input type="radio" name="is_new" value="是"/> 是
                        <input type="radio" name="is_new" value="否" checked/> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">是否热卖：</td>
                    <td>
                        <input type="radio" name="is_hot" value="是"/> 是
                        <input type="radio" name="is_hot" value="否" checked/> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">是否精品：</td>
                    <td>
                        <input type="radio" name="is_best" value="是"/> 是
                        <input type="radio" name="is_best" value="否" checked/> 否
                    </td>
                </tr>
                <tr>
                    <td class="label">排序：</td>
                    <td>
                        <input type="text" name="sort_number" value="100"/>
                    </td>
                </tr>
                <tr>
                    <td class="label">是否推荐到楼层：</td>
                    <td>
                        <input type="radio" name="is_floor" value="是"/> 是
                        <input type="radio" name="is_floor" value="否" checked/> 否
                    </td>
                </tr>
            </table>
            <!-- 商品描述 -->
            <table style="display: none" width="100%" class="btn-table" align="center">
                <tr>
                    <td>
                        <textarea id="goods_desc" name="goods_desc"></textarea>
                    </td>
                </tr>
            </table>
            <!-- 会员价格 -->
            <table style="display: none" width="90%" class="btn-table" align="center">
                <tr>
                    <td>
                        <?php if(is_array($memberLevelDate)): foreach($memberLevelDate as $k=>$val): ?><strong><?php echo ($val['level_name']); ?>：</strong><input type="text" name="member_price[<?php echo ($val['id']); ?>]" value="" size="20" />元<br><?php endforeach; endif; ?>
                    </td>
                </tr>
            </table>
            <!-- 商品属性 -->
            <table style="display: none" width="90%" class="btn-table" align="center">
                <tr>
                    <td>
                        <strong>商品类型：</strong><?php buildSelect('type','type_id','id','type_name') ;?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <ul id="attr_list"></ul>
                    </td>
                </tr>
            </table>
            <!-- 商品相册 -->
            <table style="display: none" width="90%" class="btn-table" align="center">
                <tr align="center">
                    <td>
                        <strong>选择图片：</strong>
                        <input type="file" name="goods_pic[]" multiple="multiple"/>
                    </td>
                </tr>
            </table>
            <div class="button-div">
                <input type="submit" value=" 确定 " class="button"/>
                <input type="reset" value=" 重置 " class="button" />
            </div>
        </form>
    </div>
</div>
<h1></h1>


<!--导入在线编辑器-->
<link href="/Public/utf8php/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="/Public/utf8php/third-party/jquery.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/utf8php/umeditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/utf8php/umeditor.min.js"></script>
<script type="text/javascript" src="/Public/utf8php/lang/zh-cn/zh-cn.js"></script>
<script>
    UM.getEditor('goods_desc',{
        initialFrameWidth:'100%',
        initialFrameHeight:350
    });
</script>


<!-- 输入框日期时间处理 -->
<link href="/Public/datetimepicker/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" charset="utf-8" src="/Public/datetimepicker/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/Public/datetimepicker/datepicker-zh_cn.js"></script>
<link rel="stylesheet" media="all" type="text/css" href="/Public/datetimepicker/time/jquery-ui-timepicker-addon.min.css" />
<script type="text/javascript" src="/Public/datetimepicker/time/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="/Public/datetimepicker/time/i18n/jquery-ui-timepicker-addon-i18n.min.js"></script>
<script>
    $.timepicker.setDefaults($.timepicker.regional['zh-CN']);//设置中文
    $("#promote_start_date").datetimepicker();
    $("#promote_end_date").datetimepicker();
</script>


<!-- 点击切换 -->
<script>
    $(function(){
        $('#tabbar-div p span').click(function () {
            $('.tab-front').removeClass('tab-front').addClass('tab-back');
            $(this).addClass('tab-front').removeClass('tab-back');

            var index = $(this).index();
            $('.btn-table').eq(index).show().siblings('.btn-table').hide();
        });
    })


    //ajax实时请求商品属性
    $(function () {
        $('select[name=type_id]').change(function () {
            var type_id = $(this).val();
            //选择一个类型就执行ajax
            if(type_id){
                $.ajax({
                    type:'GET',
                    url:"<?php echo U('ajaxGetAttr','',false);?>/type_id/"+type_id,
                    dataType:'json',
                    success:function (data) {
                        console.log(data);
                        var li = '';
                        $(data).each(function (key,val) {
                            li += '<li>';
                            //拼接+号
                            if (val['attr_type'] == '可选'){
                                li += '<a onclick="addNewAttr(this);" href="#">[+]</a>';
                            }
                            //属性名称
                            li += val['attr_name'] + '：';
                            //如果属性有可选值就做下拉选框,否则做文本框
                            if(val['attr_option_values']){
                                /****** 生成下拉选框 *****/
                                var _attrArr = val['attr_option_values'].split(',');
                                li += '<select name="attr_value['+val['id']+'][]"><option value="">请选择</option>';

                                for (var i = 0; i < _attrArr.length; i++){
                                    li += '<option  value="'+_attrArr[i]+'">'+_attrArr[i]+'</option>';
                                }
                                li += '</select>';
                            }else{
                                /****** 生成文本框 *****/
                                li += '<input type="text" name="attr_value['+val["id"]+'][]" value=""/>';
                            }
                            li += '</li>';
                        });
                        $('#attr_list').html(li);
                    }
                });
            }
        });
    })
    // 点击时下拉选框的添加与删除
    function addNewAttr(dom) {
        var li = $(dom).parent();
        if(li.find('a').text() == '[+]'){
            var newLi = li.clone();
            newLi.find('a').text('[-]');
            li.after(newLi);
        }else{
            li.remove();
        }

    }

</script>

<!-- 底部 -->
<div id="footer">
    共执行 7 个查询，用时 0.028849 秒，Gzip 已禁用，内存占用 3.219 MB<br />
    版权所有 &copy; 2005-2012 上海商派网络科技有限公司，并保留所有权利。</div>

</body>
</html>