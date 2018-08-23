<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'MODULE_DENY_LIST'   => array('Common','User','Install'),
    'MODULE_ALLOW_LIST'  => array('Home','Admin'),
    'DEFAULT_MODULE'     => 'Home',
    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'rFi)qRE+1nz&JxGXtcQ<Olu{:|Wj=wp"}o_-MA[K', //默认数据加密KEY

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true,
    'URL_MODEL'            => 3,
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数


    'DB_TYPE'   => 'mysql',
    'DB_HOST'   => '127.0.0.1',
    'DB_NAME'   => str_replace(['.','-'],['_',''],$_SERVER['HTTP_HOST']),
    'DB_USER'   => 'root',
    'DB_PWD'    => 'root',
    'DB_PORT'   => '3306',
    'DB_PREFIX' => 'ot_',


    'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),
	'WEBCHARSETS' => array('utf-8','gb2312','gbk','big5'),
	///'HOME_TEMPLATE'=> '../../../../static/template/home/classic/',//前台模板路径
	'TAG_REMARK' => '公用：
TDK_title {$title}

列表页：
面包屑：{$breadcrumbs}
文章列表：
<volist name="list" id="data">
	<a href="/article/detail/{$data[\'id\']}.html  ">{$data.title}</a>
	<p class="lead">{$data.description}</p>
	<span>发表于 {$data.create_time|date="Y-m-d",###}</span>
</volist>
分页：{$page}

文章页:
TDK_description {$description}
面包屑：{$breadcrumbs}
文章标题 {$info.title}
文章内容 {$info.content}
',
);
