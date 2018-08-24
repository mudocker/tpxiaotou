<?php
/**
 * Created by PhpStorm.
 * User: ACER-VERITON
 * Date: 2018/8/24
 * Time: 10:45
 */

namespace Home\Controller\iq;


use Home\Controller\HomeController;

class Base extends HomeController
{
     function createFile($filename,$content){
        $dir = pathinfo($filename,PATHINFO_DIRNAME);
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
        if($content) file_put_contents($filename,$content);
    }
     function replacHtml($weburl,$content,$filename){
        $html = R('Replace/act',array($content,$weburl));
        $indexArray =['index.html','index.htm','index.shtml','default.htm','default.html','default.shtml'];
        dirname($filename) == './Runtime/Html' && in_array(basename($filename),$indexArray) && $html = $this->replaceIndex($html);

        //加JS
        $html = preg_replace('/<body[^>]*?>/isU','$0'.PHP_EOL.'<script src="/base.js"></script>',$html);
        $html = str_replace(array('</body>','</BODY>'),'<div style="display:none"><script charset="utf-8" src="/js.js"></script></div>'.PHP_EOL.'</body>',$html);
        return $html;
    }


     function downloadImg($geturl,$filename){
        $dir = dirname($filename);
        !is_dir($dir) &&  mkdir($dir,0755,true);
        $curl = new Curl();
        $curl->download($geturl, function ($instance, $tmpfile) use ($dir) {
            $save_to_path = $dir .'/'. basename($instance->url);
            $fh = fopen($save_to_path, 'wb');
            stream_copy_to_stream($tmpfile, $fh);
            fclose($fh);
        });
        $curl->close();
    }

     function downloadFile($geturl,$weburl){
        $filename = str_replace([$weburl,'../','..\\'],'',$geturl);
        $extension = get_extension($filename);
        $dir = './Runtime/Html/'.$extension;
        !is_dir($dir) && mkdir($dir,0755,true);
        $curl = new Curl();

        $curl->download($geturl, function ($instance, $tmpfile) use ($dir,$weburl,$extension) {
            $filename = str_replace([$weburl,'../','..\\'],'',$instance->url);
            $save_to_path = $dir .'/'. get_randname($filename,$extension);
            $fh = fopen($save_to_path, 'wb');
            stream_copy_to_stream($tmpfile, $fh);
            fclose($fh);
        });
        $curl->close();
    }

    /** 替换首页TDK **/
     function replaceIndex($html){
        $pattern = array(
            '/<TITLE>([\w\W]*?)<\/TITLE>/is',
            '/<META\s+name=["|\']keywords["|\']\s+content=["|\']([\w\W]*?)["|\'][^>]*\/?>/is',
            '/<meta\s+content="[^"]+"\s+name="keywords"\s*?\/?>/is',
            "/<meta\s+content='[^']+'\s+name='keywords'\s*?\/?>/is",
            '/<meta http-equiv=["|\']keywords["|\'] content=["|\']([\w\W]*?)["|\']\/?>/is',
            '/<meta\s+name=[\'|"]description[\'|"]\s+content=[\'|"]([\w\W]*?)[\'|"][^>+]?\/?>/is',
            '/<meta\s+content="[^"]+"\s+name="description"\s*?\/?>/is',
            "/<meta\s+content='[^']+'\s+name='description'\s*?\/?>/is",
        );
        $conf = R('Loadconfig/webconfig');
        /**判断目标站编码，非utf-8则需要转换**/
        $cjconf = R('Loadconfig/config');
        $charset_id =$cjconf['webcharset'];
        $codesarr = C('WEBCHARSETS');
        $webcharset = $codesarr[$charset_id];
        /*preg_match('/<meta[^>]+charset="?([^>]*?)["|\']([^>]*)?\/?>/is',$html,$match);//判断meta标签里的编码*/
        preg_match( '/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/is',$html,$match);//判断meta标签里的编码

        if($charset_id >0){
            $index_title = iconv("utf-8",$webcharset."//TRANSLIT",$conf['WEB_INDEX_TITLE']);
            $index_keywords = iconv("utf-8",$webcharset."//TRANSLIT",$conf['INDEX_KEYWORDS']);
            $index_description = iconv("utf-8",$webcharset."//TRANSLIT",$conf['INDEX_DESCRIPTION']);
        }else{
            /**处理meta标签编码与实际编码不符的情况**/
            if(strtolower($match[1]) != 'utf-8'){
                /*$html = preg_replace('/<meta[^>]+charset="?([^>]*?)["|\']([^>]*)?\/?>/i','<meta charset="utf-8">',$html);*/
                $html = preg_replace( '/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/i','<meta charset="utf-8">',$html);
            }
            $index_title = $conf['WEB_INDEX_TITLE'];
            $index_keywords = $conf['INDEX_KEYWORDS'];
            $index_description = $conf['INDEX_DESCRIPTION'];
        }

        $replace = array(
            '<title>'.$index_title.'</title>',
            '<meta name="keywords" content="'.$index_keywords.'" />',
            '<meta name="keywords" content="'.$index_keywords.'" />',
            '<meta name="keywords" content="'.$index_keywords.'" />',
            '<meta name="keywords" content="'.$index_keywords.'" />',
            '<meta name="description" content="'.$index_description.'" />',
            '<meta name="description" content="'.$index_description.'" />',
            '<meta name="description" content="'.$index_description.'" />',
        );
        $html = preg_replace($pattern,$replace,$html);
        return $html;
    }
}