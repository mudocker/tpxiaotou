<?php
namespace Home\Controller\iq;


class html extends Base
{

    public $action_model;
    public $geturl;
    public $weburl;
    public $extension;
    public $filename;
    public $config;
    public $exists=false;

    public function __construct($self,$weburl,$parameter)
    {
        $this->config=$config = $self->_config;

        $config['url_rewrite_on'] == 1 && $parameter = get_url_before_rewrite($parameter,$config['url_rewrite_rules']);
        $config['forcedurl'] == 1 and  $parameter = url_convert($parameter,1);
        $geturl = $weburl.$parameter;
        $this->geturl = preg_replace('/([^:])\/\//is','${1}/',$geturl);//处理url中的"//"
        $this->extension = get_extension($geturl);
        $this->filename = get_filepath($geturl);
        $this->exists=file_exists($this->filename);
        $content_type = get_ContentType($this->extension);                                                                               /* 设置head信息 */
        $content_type = $content_type?$content_type:'text/html';
        header('Content-Type: '.$content_type);
        $this->geturl = $geturl;
        $this->weburl = $weburl;
        $this->action_model = R('Loadconfig/webconfig')['ACTION_MODEL'];
    }




    function getFileName(){
      if (true){
          $geturl=&$this->geturl;
          $filename=&$this->filename;
          $weburl=&$this->weburl;
      }
        switch($this->extension){
            case 'js':
                $filename = str_replace([$weburl,'../','..\\'],'',$geturl);
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


    function getUrl(){
        if (true){
            $geturl=&$this->geturl;
            if (!$this->config['subdomain'])return;
        }
        $subdomain = check_subdomain($geturl,$this->config);
        $realurl = $geturl==$subdomain?$geturl:$subdomain;
        $geturl = $realurl;
    }


    function getCache(){
         if (!$this->exists) return;
        $hconfig = $this->getHconfig();
        $html = file_get_contents($this->filename);
        $hconfig['DIR_CACHE'] !='' && $html = $this->updateCache($this->filename,$this->geturl,$this->weburl,$this->extension);
        $code = self::getHtmlCode($html) and  header("Content-Type:text/html;charset=$code");
        echo $html;
        exit();
    }


    function downImg(){
        if ($this->isRet(['jpg','gif','jpeg','png']))return;
        $res = R('Querylist/demo',[$this->geturl]);
        echo $res;
        $this->action_model == 1  and  $this->downloadImg($this->geturl,$this->filename);
        exit();
    }


    function downCssJs(){
        if ($this->isRet(['css','js']))return;
        $res = R('Querylist/demo',[$this->geturl]);
        echo $res;
        $this->action_model == 1 and  $this->downloadFile($this->geturl,$this->weburl);
        exit();
    }

    function downHtml(){
      if ($this->isRet(['','htm','html','shtml','jhtml']))return;
        $content = R('Querylist/demo',[$this->geturl]);
        $html = $this->replacHtml($this->weburl,$content,$this->filename);//替换
        $code = self::getHtmlCode($html) and   header("Content-Type:text/html;charset=$code");
        $html = \Home\Library\HtmlDomReplace::AppendNav($html);
        strtolower(trim($code))!='utf-8' && $code and $html = mb_convert_encoding($html, $code,'utf-8');
        header("Content-Type:text/html; charset=utf-8");
        echo $html;
        if($this->action_model == 0) return;
        $this->createFile($this->filename,$html);
        R('Querylist/getFileCss',[$html]);
        R('Querylist/getFileImg',[$html]);
        R('Querylist/getFileJs',[$html]);
        exit();
    }

    function isRet($params){
          return !in_array($this->extension,$params)||$this->exists;
    }
    function orElse(){
        $content = R('Querylist/getWebHtml',[$this->geturl]);
        $html = $this->replacHtml($this->weburl,$content,$this->filename);
        $code = self::getHtmlCode($html) and  header("Content-Type:text/html;charset=$code");
        $html = \Home\Library\HtmlDomReplace::AppendNav($html);
        (strtolower(trim($code))!='utf-8' && $code) and $html = mb_convert_encoding($html, $code,'utf-8');
        echo $html;
    }













}