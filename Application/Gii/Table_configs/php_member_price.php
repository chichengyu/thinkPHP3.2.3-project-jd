<?php
return array(
	'tableName' => 'php_member_price',    // 表名
	'tableCnName' => '',  // 表的中文名
	'moduleName' => 'Admin',  // 代码生成到的模块
	'withPrivilege' => FALSE,  // 是否生成相应权限的数据
	'topPriName' => '',        // 顶级权限的名称
	'digui' => 0,             // 是否无限级（递归）
	'diguiName' => '',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	// 添加时允许接收的表单中的字段
	'insertFields' => "array('price','level_id','goods_id')",
	// 修改时允许接收的表单中的字段
	'updateFields' => "array('id','price','level_id','goods_id')",
	'validate' => "
		array('price', 'require', '会员价格不能为空！', 1, 'regex', 3),
		array('price', 'currency', '会员价格必须是货币格式！', 1, 'regex', 3),
		array('level_id', 'require', '级别ID不能为空！', 1, 'regex', 3),
		array('level_id', 'number', '级别ID必须是一个整数！', 1, 'regex', 3),
		array('goods_id', 'require', '商品ID不能为空！', 1, 'regex', 3),
		array('goods_id', 'number', '商品ID必须是一个整数！', 1, 'regex', 3),
	",
	/********************** 表中每个字段信息的配置 ****************************/
	'fields' => array(
		'price' => array(
			'text' => '会员价格',
			'type' => 'text',
			'default' => '',
		),
		'level_id' => array(
			'text' => '级别ID',
			'type' => 'text',
			'default' => '',
		),
		'goods_id' => array(
			'text' => '商品ID',
			'type' => 'text',
			'default' => '',
		),
	),
	/**************** 搜索字段的配置 **********************/
	'search' => array(
		array('price', 'between', 'pricefrom,priceto', '', '会员价格'),
		array('level_id', 'normal', '', 'eq', '级别ID'),
		array('goods_id', 'normal', '', 'eq', '商品ID'),
	),
);