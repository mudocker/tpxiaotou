<?php


$str = <<< EOF

<HEAD>
<title>马海祥博客-专注于分享seo思维和sem网络营销的医疗SEO博客</title>
<META content="text/html; charset=gb2312 " http-equiv=Content-Type>
<meta name="description" content="SEO思维博客就是要帮助初学seo的创业者,如何在网站制作的同时把seo技术、网页优化、网站管理、团队合作以及多方面的因素融合到sem网络营销中,把周围接触到的新鲜事物敏锐的跟seo联系起来,全方位展示给访客最新的、最有价值的信息,进而从一个全新的层次上提升seo优化的水平,达到网络信息最佳化的展示效果." />
<meta name="keywords" content="马海祥博客,seo思维,seo博客,新型seo,sem营销,网络营销,医疗seo,医院seo" />
<meta charset="UTF-8">
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">

<LINK rel=stylesheet type=text/css href="http://www.mahaixiang.cn/templets/MA/style/mhx.css">

<SCRIPT type=text/javascript src="http://www.mahaixiang.cn/templets/MA/style/jquery.min.js"></SCRIPT>
<SCRIPT type=text/javascript src="http://www.mahaixiang.cn/templets/MA/style/comm.js"></SCRIPT>

<SCRIPT type=text/javascript src="http://www.mahaixiang.cn/templets/MA/style/jquery.lazyload.js"></SCRIPT>

EOF;

header("Content-Type:text/html;charset=gb2312");

if (preg_match('/<meta.*charset.?=[ |"]?([^ ]*).?".*>/i', $str,$matches)) {
    print_r($matches);
}