<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               => 'localhost', // 服务器地址
    'DB_NAME'               => 'php', // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'chichengyu',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'php_',    // 数据库表前缀


    'DEFAULT_FILTER'        =>  'trim,htmlspecialchars', // 默认参数过滤方法 用于I函数...

    //模板配置
    'TMPL_L_DELIM'          =>  '<{',            // 模板引擎普通标签开始标记
    'TMPL_R_DELIM'          =>  '}>',            // 模板引擎普通标签结束标记


    'SHOW_PAGE_TRACE'  =>true,

    //设置图片的路径
    'IMAGE_CONFIG'     => array(
        'maxsize'  => 1024*1024,// 设置附件上传大小
        'exts'     => array('jpg', 'gif', 'png', 'jpeg'),// 设置附件上传类型
        'rootPath' => './Public/Uploads/', // 设置附件上传根目录(相对路径),上传相对于电脑硬盘,所以是相对路径
        'viewPath' => '/Public/Uploads/',//图片显示时的src路径(绝对路径),相对你于网站根目录
    )
);