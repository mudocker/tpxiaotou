
/otxghtgl2018.php
administrator
pax2017htgl

#  编码正则匹配错误导致html修改异常

Core\Application\Home\Controller

IndexQueryController.class.php  约224行 235行

IndexCurlController.class.php  约250行 290行

    两处替换
    /*preg_match('/<meta[^>]+charset="?([^>]*?)["|\']([^>]*)?\/?>/is',$html,$match);//判断meta标签里的编码*/
    preg_match( '/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/is',$html,$match);//判断meta标签里的编码
    
    /*$html = preg_replace('/<meta[^>]+charset="?([^>]*?)["|\']([^>]*)?\/?>/i','<meta charset="utf-8">',$html);*/
    $html = preg_replace( '/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/i','<meta charset="utf-8">',$html);


# 乱码

IndexQueryController 约103行 

增加 header("Content-Type:text/html;charset=[code]");

增加方法 getHtmlCode($html);

http://l.cn/index.php?s=/IndexQuery/html/weburl=http://l.cn/1.html