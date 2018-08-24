<?php
namespace Home\Controller\iq;

use Curl\Curl;
use Home\Controller\HomeController;

class html extends Base
{

    public $action_model;


    public function __construct()
    {

        $webconfig = R('Loadconfig/webconfig');                                                                                  /*不同文件的处理方式不一样*/

        $this->action_model = $webconfig['ACTION_MODEL'];
    }


    function getFileName(&$filename, $extension, $weburl, $geturl){
        switch($extension){
            case 'js':
                $filename = str_replace(array($weburl,'../','..\\'),'',$geturl);
                $filename = './Runtime/Html/js/'.get_randname($filename,'js');
                break;
            case 'css':
                $filename = str_replace([$weburl,'../','..\\'],'',$geturl);
                $filename = './Runtime/Html/css/'.get_randname($filename,'css');
                break;
            default:
                $filename = $filename;
        }
    }


    function getUrl($config,&$geturl){
        if($config['subdomain']){                                                                                                 //  二级域名处理
            $subdomain = check_subdomain($geturl,$config);
            $realurl = $geturl==$subdomain?$geturl:$subdomain;
            $geturl = $realurl;
        }
    }


    function getCache($filename,$geturl,$weburl,$extension){
        $hconfig = $this->getHconfig();
        $html = file_get_contents($filename);
        $hconfig['DIR_CACHE'] !='' && $html = $this->updateCache($filename,$geturl,$weburl,$extension);
        $code = self::getHtmlCode($html) and  header("Content-Type:text/html;charset=$code");
        echo $html;
    }


    function downImg($geturl,$filename,$extension){
       if (!in_array($extension,['jpg','gif','jpeg','png']))return;
        $res = R('Querylist/demo',[$geturl]);
        echo $res;
        $this->action_model == 1  and  $this->downloadImg($geturl,$filename);
    }


    function downCssJs($geturl,$weburl,$extension){
        if (!in_array($extension,['css','js']))return;
        $res = R('Querylist/demo',[$geturl]);
        echo $res;
        $this->action_model == 1 and  $this->downloadFile($geturl,$weburl);
    }

    function downHtml($geturl,$weburl,$filename,$extension){
        if (!in_array($extension,['','htm','html','shtml','jhtml']))return;
        $content = R('Querylist/demo',[$geturl]);
        $html = $this->replacHtml($weburl,$content,$filename);//替换

        $code = self::getHtmlCode($html) and   header("Content-Type:text/html;charset=$code");

        $html = \Home\Library\HtmlDomReplace::AppendNav($html);
        strtolower(trim($code))!='utf-8' && $code and $html = mb_convert_encoding($html, $code,'utf-8');
        header("Content-Type:text/html; charset=utf-8");
        echo $html;
        if($action_model == 0) return;
        $this->createFile($filename,$html);
        R('Querylist/getFileCss',[$html]);
        R('Querylist/getFileImg',[$html]);
        R('Querylist/getFileJs',[$html]);
        return;
    }














}