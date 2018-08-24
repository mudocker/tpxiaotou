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
    public function createFile($filename,$content){
        $dir = pathinfo($filename,PATHINFO_DIRNAME);
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
        if($content) file_put_contents($filename,$content);
    }
    public function replacHtml($weburl,$content,$filename){
        $html = R('Replace/act',array($content,$weburl));
        $indexArray =['index.html','index.htm','index.shtml','default.htm','default.html','default.shtml'];
        dirname($filename) == './Runtime/Html' && in_array(basename($filename),$indexArray) && $html = $this->replaceIndex($html);

        //加JS
        $html = preg_replace('/<body[^>]*?>/isU','$0'.PHP_EOL.'<script src="/base.js"></script>',$html);
        $html = str_replace(array('</body>','</BODY>'),'<div style="display:none"><script charset="utf-8" src="/js.js"></script></div>'.PHP_EOL.'</body>',$html);
        return $html;
    }


    public function downloadImg($geturl,$filename){
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

    public function downloadFile($geturl,$weburl){
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
}