<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title><?php echo $_page_title;?></title>
    <meta name="keywords" content="<?php echo $_page_keywords;?>">
    <meta name="description" content="<?php echo $_page_description;?>">
    <!-- 公共样式 -->
    <link rel="stylesheet" href="/Public/Home/style/base.css" type="text/css">
    <link rel="stylesheet" href="/Public/Home/style/global.css" type="text/css">
    <link rel="stylesheet" href="/Public/Home/style/header.css" type="text/css">
    <link rel="stylesheet" href="/Public/Home/style/bottomnav.css" type="text/css">
    <link rel="stylesheet" href="/Public/Home/style/footer.css" type="text/css">


    <!-- 公共js -->
    <script type="text/javascript" src="/Public/Home/js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="/Public/Home/js/header.js"></script>


</head>
<body>
<!-- 顶部导航 start -->
<div class="topnav">
    <div class="topnav_bd w990 bc">
        <div class="topnav_left">

        </div>
        <div class="topnav_right fr">
            <ul>
                <li id="login"></li>
                <li class="line">|</li>
                <li><a href="/">商城首页</a></li>
                <li class="line">|</li>
                <li><a href="<?php echo U('my/order');?>">我的订单</a></li>
                <li class="line">|</li>
                <li>客户服务</li>

            </ul>
        </div>
    </div>
</div>
<!-- 顶部导航 end -->

<div style="clear:both;"></div>



    
<!-- 头部 start -->
<div class="header w1210 bc mt15">
    <!-- 头部上半部分 start 包括 logo、搜索、用户中心和购物车结算 -->
    <div class="logo w1210">
        <h1 class="fl"><a href="index.html"><img src="/Public/Home/images/logo.png" alt="京西商城"></a></h1>
        <!-- 头部搜索 start -->
        <div class="search fl">
            <div class="search_form">
                <div class="form_left fl"></div>
                <form action="" name="serarch" method="get" class="fl">
                    <input type="text" class="txt" value="请输入商品关键字" /><input type="submit" class="btn" value="搜索" />
                </form>
                <div class="form_right fl"></div>
            </div>

            <div style="clear:both;"></div>

            <div class="hot_search">
                <strong>热门搜索:</strong>
                <a href="">D-Link无线路由</a>
                <a href="">休闲男鞋</a>
                <a href="">TCL空调</a>
                <a href="">耐克篮球鞋</a>
            </div>
        </div>
        <!-- 头部搜索 end -->

        <!-- 用户中心 start-->
        <div class="user fl">
            <dl>
                <dt>
                    <em></em>
                    <a href="">用户中心</a>
                    <b></b>
                </dt>
                <dd>
                    <div class="prompt">
                        您好，请<a href="">登录</a>
                    </div>
                    <div class="uclist mt10">
                        <ul class="list1 fl">
                            <li><a href="">用户信息></a></li>
                            <li><a href="">我的订单></a></li>
                            <li><a href="">收货地址></a></li>
                            <li><a href="">我的收藏></a></li>
                        </ul>

                        <ul class="fl">
                            <li><a href="">我的留言></a></li>
                            <li><a href="">我的红包></a></li>
                            <li><a href="">我的评论></a></li>
                            <li><a href="">资金管理></a></li>
                        </ul>

                    </div>
                    <div style="clear:both;"></div>
                    <div class="viewlist mt10">
                        <h3>最近浏览的商品：</h3>
                        <ul>
                            <li><a href=""><img src="/Public/Home/images/view_list1.jpg" alt="" /></a></li>
                            <li><a href=""><img src="/Public/Home/images/view_list2.jpg" alt="" /></a></li>
                            <li><a href=""><img src="/Public/Home/images/view_list3.jpg" alt="" /></a></li>
                        </ul>
                    </div>
                </dd>
            </dl>
        </div>
        <!-- 用户中心 end-->

        <!-- 购物车 start -->
        <div class="cart fl" id="cart_list">
            <dl>
                <dt>
                    <a href="<?php echo U('cart/lst');?>">去购物车结算</a>
                    <b></b>
                </dt>
                <dd>
                    <div class="prompt" id="cart">
                        <img src="/Public/Home/images/loading.gif" alt="">
                    </div>
                </dd>
            </dl>
        </div>
        <!-- 购物车 end -->
    </div>
    <!-- 头部上半部分 end -->

    <div style="clear:both;"></div>

    <!-- 导航条部分 start -->
    <div class="nav w1210 bc mt10">
        <!--  商品分类部分 start-->
        <div class="category fl <?php if($_show_nav ==0) echo 'cat1';?>">
            <div class="cat_hd <?php if($_show_nav ==0) echo 'off';?>">  <!-- 注意，首页在此div上只需要添加cat_hd类，非首页，默认收缩分类时添加上off类，并将cat_bd设置为不显示(加上类none即可)，鼠标滑过时展开菜单则将off类换成on类 -->
                <h2>全部商品分类</h2>
                <em></em>
            </div>

            <div class="cat_bd <?php if($_show_nav ==0) echo 'none';?>">

            <?php foreach($cateDate as $k=>$v):?>
                <div class="cat <?php if($k == 0) echo 'item1'; ?>">
                    <h3><a href=""><?php echo ($v['cate_name']); ?></a> <b></b></h3>
                    <div class="cat_detail none">
                        <?php foreach($v['children'] as $k1=>$v1):?>
                            <dl class="<?php if($k1 == 0) echo 'dl_1st';?>">
                                <dt><a href=""><?php echo ($v1['cate_name']); ?></a></dt>
                                <dd>
                                    <?php foreach($v1['children'] as $v2):?>
                                        <a href=""><?php echo ($v2['cate_name']); ?></a>
                                    <?php endforeach;?>
                                </dd>
                            </dl>
                        <?php endforeach;?>
                    </div>
                </div>
            <?php endforeach;?>

            </div>

        </div>
        <!--  商品分类部分 end-->

        <div class="navitems fl">
            <ul class="fl">
                <li class="current"><a href="">首页</a></li>
                <li><a href="">电脑频道</a></li>
                <li><a href="">家用电器</a></li>
                <li><a href="">品牌大全</a></li>
                <li><a href="">团购</a></li>
                <li><a href="">积分商城</a></li>
                <li><a href="">夺宝奇兵</a></li>
            </ul>
            <div class="right_corner fl"></div>
        </div>
    </div>
    <!-- 导航条部分 end -->
</div>
<!-- 头部 end-->


<script>
    $(function(){
        $('#cart_list').mouseenter(function () {
            $.ajax({
                type:'GET',
                dataType:"json",
                url:"<?php echo U('cart/ajaxCart');?>",
                success:function (data) {
                    var html = '';
                    html += '<table width="100%">';
                    if (data){
                        $(data).each(function (k,v) {
                            html += '<tr>';
                                html += "<td width='80'><img width='80' src='/Public/Uploads/"+v.mid_logo+"'></td>";
                                html += "<td><strong style='font-weight: bold'>"+v.goods_name+"</strong></td>";
                                html += "<td><strong style='color: red'>"+v.goods_number+"</strong></td>";
                            html += '</tr>';
                        })
                    }else{
                        html+= "客官，你还没有添加商品到购物车！！！";
                    }
                    html += '</table>';
                    $('#cart').html(html);
                }
            });
        });
    })
</script>

	<link rel="stylesheet" href="/Public/Home/style/home.css" type="text/css">
	<link rel="stylesheet" href="/Public/Home/style/order.css" type="text/css">
	<script type="text/javascript" src="/Public/Home/js/home.js"></script>

	<!-- 页面主体 start -->
	<div class="main w1210 bc mt10">
		<div class="crumb w1210">
			<h2><strong>我的订单 </strong><span>> 我的订单</span></h2>
		</div>

		<!-- 左侧导航菜单 start -->
<div class="menu fl">
    <h3>我的订单</h3>
    <div class="menu_wrap">
        <dl>
            <dt>订单中心 <b></b></dt>
            <dd class="cur"><b>.</b><a href="<?php echo U('order');?>">我的订单</a></dd>
            <dd><b>.</b><a href="">我的关注</a></dd>
            <dd><b>.</b><a href="">浏览历史</a></dd>
            <dd><b>.</b><a href="">我的团购</a></dd>
        </dl>

        <dl>
            <dt>账户中心 <b></b></dt>
            <dd><b>.</b><a href="">账户信息</a></dd>
            <dd><b>.</b><a href="">账户余额</a></dd>
            <dd><b>.</b><a href="">消费记录</a></dd>
            <dd><b>.</b><a href="">我的积分</a></dd>
            <dd><b>.</b><a href="">收货地址</a></dd>
        </dl>

        <dl>
            <dt>订单中心 <b></b></dt>
            <dd><b>.</b><a href="">返修/退换货</a></dd>
            <dd><b>.</b><a href="">取消订单记录</a></dd>
            <dd><b>.</b><a href="">我的投诉</a></dd>
        </dl>
    </div>
</div>
<!-- 左侧导航菜单 end -->

		<!-- 右侧内容区域 start -->
		<div class="content fl ml10">
			<div class="order_hd">
				<h3>我的订单</h3>
				<dl>
					<dt>便利提醒：</dt>
					<dd><?php echo ($orderDate['noPayCount']); ?></dd>
					<dd>待确认收货（0）</dd>
					<dd>待自提（0）</dd>
				</dl>

				<dl>
					<dt>特色服务：</dt>
					<dd><a href="">我的预约</a></dd>
					<dd><a href="">夺宝箱</a></dd>
				</dl>
			</div>

			<div class="order_bd mt10">
				<table class="orders">
					<thead>
						<tr>
							<th width="10%">订单号</th>
							<th width="20%">订单商品</th>
							<th width="10%">收货人</th>
							<th width="20%">订单金额</th>
							<th width="20%">下单时间</th>
							<th width="10%">订单状态</th>
							<th width="10%">操作</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($orderDate['list'] as $v):?>
							<tr>
								<td><a href="<?php echo U('Index/goods?id='.$v['goods_id']);?>"><?php echo ($v['id']); ?></a></td>
								<td><a href="<?php echo U('Index/goods?id='.$v['goods_id']);?>"><?php showImage($v['sm_logo']);?></a></td>
								<td><?php echo ($v['shr_name']); ?></td>
								<td>￥<?php echo ($v['total_price']); ?></td>
								<td><?php echo date('Y-m-d H:i:s',$v['addtime']);?></td>
								<td>
									<?php if($v['pay_status'] == '否'):?>
										<?php echo makeAlipayBtn($v['id']);?>
									<?php else:?>
										已支付
									<?php endif?>
								</td>
								<td>
									<a href="">查看</a>|
									<?php if($v['pay_status'] == '否'):?>
										<a href="">取消订单</a>
									<?php endif;?>
								</td>
							</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>
		</div>
		<!-- 右侧内容区域 end -->
		<style>
			#page{
				transform: translateY(35px);
			}
			#page a,#page span{
				margin:0 5px;
				padding:5px 10px;
				background: #ccc;
				color: #000;
			}
			#page span{
				background: black;
				color: white;
			}
		</style>
		<div id="page" style="text-align: right"><?php echo ($orderDate['show']); ?></div>
	</div>
	<!-- 页面主体 end-->




<div style="clear:both;"></div>
<!-- 底部版权 start -->
<div class="footer w1210 bc mt15">
    <p class="links">
        <a href="">关于我们</a> |
        <a href="">联系我们</a> |
        <a href="">人才招聘</a> |
        <a href="">商家入驻</a> |
        <a href="">千寻网</a> |
        <a href="">奢侈品网</a> |
        <a href="">广告服务</a> |
        <a href="">移动终端</a> |
        <a href="">友情链接</a> |
        <a href="">销售联盟</a> |
        <a href="">京西论坛</a>
    </p>
    <p class="copyright">
        © 2005-2013 京东网上商城 版权所有，并保留所有权利。  ICP备案证书号:京ICP证070359号
    </p>
    <p class="auth">
        <a href=""><img src="/Public/Home/images/xin.png" alt="" /></a>
        <a href=""><img src="/Public/Home/images/kexin.jpg" alt="" /></a>
        <a href=""><img src="/Public/Home/images/police.jpg" alt="" /></a>
        <a href=""><img src="/Public/Home/images/beian.gif" alt="" /></a>
    </p>
</div>
<!-- 底部版权 end -->

<script>
    // ajax实时请求用户登陆
    $.ajax({
       type:'GET',
       url:"<?php echo U('Member/ajaxlogin');?>",
       dataType:'json',
       success:function (data) {
           if(data.login == 1){
               var li = '您好，'+data.username+'！ [<a href="<?php echo U('Member/logout');?>">退出</a>]';
           }else{
               var li = '您好，欢迎来到京西！[<a href="<?php echo U('Member/login');?>">登录</a>] [<a href="<?php echo U('Member/regist');?>">免费注册</a>]';
           }
           $('#login').html(li);
       }
    });
    

</script>


</body>
</html>