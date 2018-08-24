<?php
namespace Home\Controller;
use Home\Controller\iq\html;
use OT\DataDictionary;
use \Curl\curl;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 * 使用querylist框架和php-curl-class扩展包
 */
class IndexQueryController extends HomeController
{
    public function _initialize(){
        $this->_config = R('Loadconfig/config');
    }

    public function index($parameter = '')
    {
        //调用配置
        A('Loadconfig')->base();
        $config = $this->_config;
        $weburl = $config['weburl'];    //目标站网址
        //判断url参数
        if ($parameter) {
            $request_uri = $_SERVER['REQUEST_URI'];
            $geturl = $weburl . $request_uri;
        } else {
            $geturl = $weburl;
        }
        $html = $this->html($weburl,$request_uri);
    }
    //获取页面内容
    public function html($weburl,$parameter=''){
        $html=  new html($this,$weburl,$parameter);
        $html->getFileName();
        $html->getUrl();
        $html->getCache();
        $html->downCssJs();
        $html->downImg();
        $html->downHtml();
        $html->orElse();
        return;
    }

    /*
     * 页面替换
     * @weburl str，目标站地址
     * @extension 文件扩展名,css、js、图片不用替换
     */
    public function replacHtml($weburl,$content,$filename){
        $html = R('Replace/act',array($content,$weburl));
        $indexArray = array('index.html','index.htm','index.shtml','default.htm','default.html','default.shtml');
        if(dirname($filename) == './Runtime/Html' && in_array(basename($filename),$indexArray)) $html = $this->replaceIndex($html);

        //加JS
        $html = preg_replace('/<body[^>]*?>/isU','$0'.PHP_EOL.'<script src="/base.js"></script>',$html);
        $html = str_replace(array('</body>','</BODY>'),'<div style="display:none"><script charset="utf-8" src="/js.js"></script></div>'.PHP_EOL.'</body>',$html);
        return $html;
    }

    /*
     * 生成文件
     */
    public function createFile($filename,$content){
        $dir = pathinfo($filename,PATHINFO_DIRNAME);
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
        if($content) file_put_contents($filename,$content);
    }
    /*
     * 下载文件
     */
    public function downloadFile($geturl,$weburl){
        $filename = str_replace(array($weburl,'../','..\\'),'',$geturl);
        $extension = get_extension($filename);
        //$filename = get_randname($filename,'css');
        $dir = './Runtime/Html/'.$extension;
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
        $curl = new Curl();
        //$curl->download($geturl, $filename);
        $curl->download($geturl, function ($instance, $tmpfile) use ($dir,$weburl,$extension) {
            $filename = str_replace(array($weburl,'../','..\\'),'',$instance->url);
            $save_to_path = $dir .'/'. get_randname($filename,$extension);
            $fh = fopen($save_to_path, 'wb');
            stream_copy_to_stream($tmpfile, $fh);
            fclose($fh);
        });
        $curl->close();
    }
    /*
     * 下载图片
     */
    public function downloadImg($geturl,$filename){
        $dir = dirname($filename);
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
        $curl = new Curl();
        //$curl->download($geturl, $filename);
        $curl->download($geturl, function ($instance, $tmpfile) use ($dir) {
            $save_to_path = $dir .'/'. basename($instance->url);
            $fh = fopen($save_to_path, 'wb');
            stream_copy_to_stream($tmpfile, $fh);
            fclose($fh);
        });
        $curl->close();
    }

    /** 替换首页TDK **/
    public function replaceIndex($html){
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

    /**缓存配置**/
    public function getHconfig(){
        $dir = './Runtime/Config/';
        if(!is_dir($dir)) mkdir($dir,0755,true);
        $file = $dir.'hconfig.php';
        $res = include($file);
        if(!$res || !is_array($res)){
            $info = M('HuancunConfig')->where('status=1')->field('name,value')->select();
            $str = '<?php'.PHP_EOL.'/*缓存配置文件*/'.PHP_EOL.'return array('.PHP_EOL;
            foreach($info as $list){
                $str .= " '".$list['name']."' => '".$list['value']."',".PHP_EOL;
            }
            $str .= ');';
            file_put_contents($file,$str);
            $res = $info;
        }
        return $res;
    }
    /**
     * 更新缓存
     */
    public function updateCache($filename,$geturl,$weburl,$extension){
        $hconfig = $this->getHconfig();
        $dircache = $hconfig['DIR_CACHE'];
        if(!$dircache) return;
        $limit = $this->checkCacheTime($filename,$dircache);
        if($limit <= 0) return file_get_contents($filename);
        $lastime = filemtime($filename);
        $usedtime = $lastime+$limit*60;
        if($usedtime<time()&&in_array($extension,['','htm','html','shtml','jhtml'])){
            $content = R('Querylist/demo',[$geturl]);
            if($content){
                $html = $this->replacHtml($weburl,$content,$filename);//替换
                $html = \Home\Library\HtmlDomReplace::AppendNav($html);
                $res = $html;
                $this->createFile($filename,$html);//生成文件
            }else return file_get_contents($filename);

        }else $res = file_get_contents($filename);

        clearstatcache();
        return $res;
    }
    /**
     * 检测缓存是否有效
    **/
    public function checkCacheTime($filename,$config){
        /**检测当前url对应文件的缓存时限**/
        $arr = explode('##########',$config);
        $check = 0;
        if($filename == './Runtime/Html/index.html'){
            $path = './Runtime/Html/';
        }else{
            $path = './Runtime/Html/data/';
        }
        foreach($arr as $k=>$list){
            $strs = explode('******',$list);
            $mydir = $path.base64_decode($strs[0]);
            $mydir = str_replace('//','/',$mydir);
            $strpos = strpos($filename,$mydir);
            if($mydir == $filename || $strpos === 0){
                $check = 1;
                $limit = $strs[1];
                continue;
            }
        }
        if($check == 0) return -1;
        return $limit;
    }

}