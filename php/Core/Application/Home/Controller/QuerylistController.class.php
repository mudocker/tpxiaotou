<?php
namespace Home\Controller;
use OT\DataDictionary;
use QL\QueryList;
use \Curl\Curl;
use \Curl\MultiCurl;
/**
 * 获取远程页面内容
 * 主要获取首页聚合数据
 */
class QuerylistController extends HomeController {

    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.170 Safari/537.36';

    public function proxyip(){
        $conf = R('Loadconfig/webconfig');
        if($conf['PROXY_ON'] == 0){
            return false;
        }else{
            if($conf['PROXY_TYPE'] == 'api'){
                $api = $conf['PROXY_API'];
                $ip = file_get_contents($api);
            }elseif($conf['PROXY_TYPE'] == 'ip'){
                $ip = $conf['PROXY_IP'];
            }
            return $ip;
        }
    }


    public function getWebHtml($url){
        /*
         * 获取目标站编码
         */
        $cjconf = R('Loadconfig/config');
        $charset_id =$cjconf['webcharset'];
        $codesarr = C('WEBCHARSETS');
        $webcharset = $codesarr[$charset_id];
        $rules = [];
        $data = QueryList::Query($url,$rules,'',$webcharset,$webcharset)->getHtml($rel = false);
        return($data);
    }
    //测试
    public function demo($url){
        //获取网页代码
        $curl = new Curl();
        //$curl->setOpt(CURLOPT_HEADER, true); //头部响应
        //代理
        $proxyip = $this->proxyip();
        if($proxyip) {
            $proxy = explode('@',$proxyip);
            $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, 1);
            $curl->setOpt(CURLOPT_PROXY, $proxy[0]);
            $curl->setOpt(CURLOPT_PROXYUSERPWD, $proxy[1]);
        }
        $curl->setTimeout(20);
        $curl->setUserAgent($this->userAgent);
        $curl->setReferrer('https:/www.baidu.com/');
        $curl->get($url);
        if ($curl->error) {
            echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            return false;
        } else $html = $curl->response;

        $response =$curl->responseHeaders;
        $content_type = $response['content-type'];
        header('Content-Type: '.$content_type);
        return $html;
    }
    //下载css文件
    public function getFileCss($html){
        $config = R('Loadconfig/config');
        $target_url = $config['weburl'];
        $this->getAllLink($data,$html);
        $multi_curl = new MultiCurl();
        $multi_curl->setUserAgent($this->userAgent);
        $this->loopDown($data,$target_url,$multi_curl);
        $multi_curl->start();
        return;
    }
    public function getAllLink(&$result, $html){
        $config = R('Loadconfig/config');
        $target_url = $config['weburl'];	//目标站网址
        $webconfig = R('Loadconfig/webconfig');
        $cur_url = $webconfig['WEB_URL'];//当前网址
        $result = QueryList::Query($html,array(
            'css' => array('link','href')
        ))->getData(function($item) use($target_url,$cur_url){
            $extension = get_extension($item['css']);
            if($extension != 'css') return false;
            $res = str_replace($cur_url,'',$target_url.$item['css']);
            return $res;
        });
        $result = array_unique(array_filter($result));
    }

    function loopDown($data,$target_url,&$multi_curl,$filepath='./Runtime/Html/css/'){
        foreach($data as $vo){
            $filename = str_replace(array($target_url,'../','..\\'),'',$vo);
            $randname = get_randname($filename,'css');//随机文件名
            !is_dir($filepath) and  mkdir($filepath,0755,true);
            $filename = $filepath . '/' . $randname;
            !file_exists($filename) and  $multi_curl->addDownload($vo, $filename);
        }
    }





    public function getFileImg($html){
        function getAllImage(&$data,$html,$target_url){
            $webconfig = R('Loadconfig/webconfig');
            $cur_url = $webconfig['WEB_URL'];//当前网址

            $data = QueryList::Query($html,array(
                'img' => array('img','src')
            ))->getData(function($item) use($target_url,$cur_url){
                $extension = get_extension($item['img']);
                if(!in_array($extension,array('jpg','jpeg','gif','png'))) return false;
                $res = str_replace($cur_url,'',$target_url.$item['img']);
                return $res;
            });
            $data = array_unique(array_filter($data));
        }
        function loopDownPic($data,$target_url,&$multi_curl){
            foreach($data as $vo){
                $filename = str_replace($target_url,'',$vo);
                $filepath = './Runtime/Html/img/';
                $dir = pathinfo($filename,PATHINFO_DIRNAME);
                $dir = $filepath.$dir;
                !is_dir($dir) and  mkdir($dir,0755,true);

                !file_exists($filepath .$filename) and  $multi_curl->addDownload($vo, $filepath . $filename);
            }
        }
        $config = R('Loadconfig/config');
        $target_url = $config['weburl'];	//目标站网址
        $data=[];
        getAllImage($data,$html,$target_url);
        $multi_curl = new MultiCurl();
        $multi_curl->setUserAgent($this->userAgent);
        loopDownPic($data,$target_url,$multi_curl);
        $multi_curl->start();
        return;
    }

    //下载css文件
    public function getFileJs($html){
        function getAllJs(&$data,$html,$target_url){
            $webconfig = R('Loadconfig/webconfig');
            $cur_url = $webconfig['WEB_URL'];//当前网址
            $data = QueryList::Query($html,['js' => array('script','src')])->getData(function($item) use($target_url,$cur_url){
                $extension = get_extension($item['js']);
                if($extension != 'js') return false;
                $res = str_replace($cur_url,'',$target_url.$item['js']);
                return $res;
            });
            $data = array_unique(array_filter($data));
        }
        function loopDownJs($data,$target_url,&$multi_curl){
            foreach($data as $vo){
                $filename = str_replace(array($target_url,'../','..\\'),'',$vo);
                $filepath = './Runtime/Html/js/';
                $randname = get_randname($filename,'js');
                !is_dir($filepath) and  mkdir($filepath,0755,true);
                $filename = $filepath . '/' . $randname;
                !file_exists($filename) and  $multi_curl->addDownload($vo,$filename );
            }
        }
        $config = R('Loadconfig/config');
        $target_url = $config['weburl'];	//目标站网址
        $data=[];
        getAllJs($data,$html,$target_url);
        $multi_curl = new MultiCurl();
        $multi_curl->setUserAgent($this->userAgent);
        loopDownJs($data,$target_url,$multi_curl);
        $multi_curl->start();
        return;
    }





}