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
        !is_dir($dir) and  mkdir($dir,0755,true);
        $content && file_put_contents($filename,$content);
    }
     function replacHtml($weburl,$content,$filename){
        $html = R('Replace/act',[$content,$weburl]);
        $indexArray =['index.html','index.htm','index.shtml','default.htm','default.html','default.shtml'];
        dirname($filename) == './Runtime/Html' && in_array(basename($filename),$indexArray) && $html = $this->replaceIndex($html);

        //加JS
        $html = preg_replace('/<body[^>]*?>/isU','$0'.PHP_EOL.'<script src="/base.js"></script>',$html);
    //    $html = str_replace(array('</body>','</BODY>'),'<div style="display:none"><script charset="utf-8" src="/js.js"></script></div>'.PHP_EOL.'</body>',$html);
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
         $this->getPattern($pattern);

        $cjconf = R('Loadconfig/config');
        $charset_id =$cjconf['webcharset'];
        $codesarr = C('WEBCHARSETS');
        $webcharset = $codesarr[$charset_id];

        $this->getReplace($replace,$charset_id,$webcharset);
        $html = preg_replace($pattern,$replace,$html);
        return $html;
    }

    private function getReplace(&$replace,$charset_id,$webcharset){


        $conf = R('Loadconfig/webconfig');
        if($charset_id >0){
            $index_title            = iconv("utf-8",$webcharset."//TRANSLIT",$conf['WEB_INDEX_TITLE']);
            $index_keywords         = iconv("utf-8",$webcharset."//TRANSLIT",$conf['INDEX_KEYWORDS']);
            $index_description      = iconv("utf-8",$webcharset."//TRANSLIT",$conf['INDEX_DESCRIPTION']);
        }else{

          /*  preg_match( '/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/is',$html,$match);*/
            /*       strtolower($match[1]) != 'utf-8' and $html = preg_replace( '/<meta[^>]+charset=["|\']?([^"^\'^>]*)["|\']?([^>]+)?\/?>/i','<meta charset="utf-8">',$html);*/
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

    }

    private function  getPattern(&$pattern){
        $pattern =[
            '/<TITLE>([\w\W]*?)<\/TITLE>/is',
            '/<META\s+name=["|\']keywords["|\']\s+content=["|\']([\w\W]*?)["|\'][^>]*\/?>/is',
            '/<meta\s+content="[^"]+"\s+name="keywords"\s*?\/?>/is',
            "/<meta\s+content='[^']+'\s+name='keywords'\s*?\/?>/is",
            '/<meta http-equiv=["|\']keywords["|\'] content=["|\']([\w\W]*?)["|\']\/?>/is',
            '/<meta\s+name=[\'|"]description[\'|"]\s+content=[\'|"]([\w\W]*?)[\'|"][^>+]?\/?>/is',
            '/<meta\s+content="[^"]+"\s+name="description"\s*?\/?>/is',
            "/<meta\s+content='[^']+'\s+name='description'\s*?\/?>/is",
        ];
        return $pattern;
    }
}