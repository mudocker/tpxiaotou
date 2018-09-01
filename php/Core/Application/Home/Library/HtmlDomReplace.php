<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Library;

use FluentDOM\DOM\Element;

/**
 * Xpath 替换 Html内容  实现 镜像模板动态内容替换
 *
 * @author Administrator
 */
class HtmlDomReplace {
    

    public static function AppendNav($html){
        $_config = R('Loadconfig/config');
        if($_config['xpath_enable'] != 1 || $_config['xpath_nav_article_category']=='') return $html;
        $options=  [
            \FluentDOM\Loader\Options::ENCODING => 'gb2312',
            \FluentDOM\Loader\Options::FORCE_ENCODING => 'gb2312'
        ];
        $document = \FluentDOM::load( $html, 'text/html' , $options );
        return !self::_documentAppendNav($document,$_config)? $html: $document->saveHTML()?:$html;
    }
    
    public static function _documentAppendNav(&$document,$_config){
        $navs = $document($_config['xpath_nav_block']);
        if(!$navs->length)  return false;
        $nav = $navs[0];
        switch($_config['xpath_nav_template_mode']){
            case 1:
                $nav_elements = self::createHtmlNavElements($nav,$_config['xpath_nav_type_template'],$_config['xpath_nav_article_category']);
                break;
            case 2:
                $nav_elements = self::createXapthNavElements($nav,$_config['xpath_nav_pos_template'],$_config['xpath_nav_clean_template'],$_config['xpath_nav_article_category']);
                break;
            default:
                return false;
        }
        if($_config['xpath_nav_mode'] == 1) $nav->append($nav_elements);
        elseif($_config['xpath_nav_mode'] == 2){
            $replace_index = explode(',', $_config['xpath_nav_replace']);
            $lis = $nav('./*');
            foreach($replace_index as $index){
                $lis[$index-1]->before(array_pop($nav_elements));
                $lis[$index-1]->remove();
            }
        }
        return true;
    }

    /**
     * 根据输入的html模板生成导航节点
     * @param Element $nav 导航块节点
     * @param string $html 导航html模板
     * @return array[Element]
     */
    public static function createHtmlNavElements(&$nav,$html,$category_id){
        $result = [];
        $categorys = D('Category')->field(['name','id','title'])->where(['id in ('.$category_id.')'])->select();
        foreach($categorys as $category){
            $fragment = $nav->ownerDocument->createDocumentFragment();
            $nav_row = str_replace(['<{url}>','<{name}>'],[U('Article/index',array('category'=>$category['id'])),$category['title']], $html);
            $fragment->appendXml($nav_row);
            $result[] = $fragment;
        }
        return $result;
    }
    
    /**
     * 根据xpath定位 生成导航节点
     * @param type $nav
     * @param type $temp_xpath
     * @param type $clean_xpath
     * @param type $category_id
     */
    public static function createXapthNavElements(&$nav,$temp_xpath,$clean_xpath,$category_id){
        $result = [];
        $categorys = D('Category')->field(['name','id','title'])->where(['id in ('.$category_id.')'])->select();
        $nav_rows = $nav($temp_xpath);
        if(!$nav_rows->length) return [];
        $nav_row = $nav_rows[$nav_rows->length-1];

        if($clean_xpath){
            $clean_nodes = $nav_row($clean_xpath);
            foreach($clean_nodes as $clean_node) $clean_node->remove();

        }
        foreach($categorys as $category){
            $newNav = $nav_row->cloneNode(true);
            foreach($newNav('.//a') as $newNav_a){
                $newNav_a->setAttribute('href',U('Article/index',array('category'=>$category['id'])));
                $newNav_a->nodeValue = $category['title'];
            }
            $result[]=$newNav;
        }
        return $result;
    }
    
    /**
     * 获取缓存的用于xpath替换的页面
     * @param string $page_type 页面类型  目前只有2种  list / detail
     * @param integer $web_id  采集节点ID
     * @param type $url
     */
    public static function getXpathHtmlCache($page_type,$web_id,$url){
        $file = RUNTIME_PATH."Xpath/$web_id/$page_type.html";
        if(is_file($file)) return $file;
        else{
            $path = dirname($file);
           !is_dir($path) and  mkdir($path,0777,true);
            $html = file_get_contents($url);
            return  $html && file_put_contents($file, $html)?  $file: false;
        }
    }
    

    public static function XpathArticleList($category,$p){
        $_config = R('Loadconfig/config');
        if($_config['xpath_enable'] != 1 || $_config['xpath_nav_article_category']=='') return '';

        $file =  self::getXpathHtmlCache('list',$_config['id'],$_config['xpath_list_template']); 
        $document =  \FluentDOM::load( $file,  'text/html',[\FluentDOM\Loader\Options::ALLOW_FILE => TRUE]);
        if(!self::_documentAppendNav($document,$_config)) return false;

        $listDoms = $document($_config['xpath_list_block']);
        if(!$listDoms->length) return false;
        $listDom = $listDoms[0];
        foreach($listDom('./*') as $li) $li->remove();

        $DocumentModel = D('Document');
        $rows = $DocumentModel->page($p, $category['list_row'])->lists($category['id']);
        foreach($rows as $row){
            $html =  str_replace([
                '<{url}>',
                '<{img}>',
                '<{title}>',
                '<{datetime}>',
                '<{desc}>',
            ], [
                U('Article/detail',array('id'=>$row['id'])),
                '',
                $row['title'],
                date('Y-m-d H:i:s',$row['update_time']),
                $row['description'],
            ], $_config['xpath_list_row_type']);
            $listDom->appendXml($html);
        }
        if($_config['xpath_list_pages']){
            $total =   $DocumentModel->where(['category_id'=>$category['id']])->count();
            $pageDoms = $document($_config['xpath_list_pages']);
            if($pageDoms->length){

                $page = new \Think\Page($total, $category['list_row'], $_REQUEST);
                if($total>$listRows) $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');

                $p =$page->show();
                $pageDom = $pageDoms[0];
                $pageDom->nodeValue = '';
                $pageDom->append(\FluentDOM::load( $p,  'text/html') );
            }
        }
        return $document->saveHTML();
    }
    
    /**
     * 根据XPath生成文章详情页
     * @param type $info
     * @param type $next
     */
    public static function XpathArticleDetail($info){
//        print_r($next);exit;
        $_config = R('Loadconfig/config');
        $file =  self::getXpathHtmlCache('detail',$_config['id'],$_config['xpath_article_template']); 
        $document =  \FluentDOM::load( $file,  'text/html',[\FluentDOM\Loader\Options::ALLOW_FILE => TRUE]);
        $fields =  explode("\n",$_config['xpath_article_fields']);
        foreach($fields as $field){
            $pos = strpos($field,':' );
            if( $pos === false ||  $pos===0) continue;

            $tag = trim(mb_substr($field, 0,$pos));
            $xpath = mb_substr($field, $pos+1);
            switch ($tag){
                case 'datetime':
                    $doms = $document($xpath);
                    if($doms->length){
                        $dom = $doms[0];
                        $dom->nodeValue = date('Y-m-d H:i:s',$info['update_time']);
                    }
                    break;
                case 'title':
                case 'description':
                    $doms = $document($xpath);
                    if($doms->length){
                        $dom = $doms[0];
                        $dom->nodeValue = $info[$tag];
                    }
                    break;
                case 'content':
                    $doms = $document($xpath);
                    if($doms->length){
                        $dom = $doms[0];
                        $contentDom = \FluentDOM::load(  '<div>'.$info[$tag].'</div>',  'text/html');
                        $dom->nodeValue = '';
                        $dom->append($contentDom);
                    }
                    break;
                case 'next':
                    $doms = $document($xpath);
                    if($doms->length){
                        $dom = $doms[0];
                        $next = M('Document')->field('id,title')->where('id>'.$info['id'].' and type='.$info['type'])->order('id desc')->limit('1')->select();
                        if($next){
                            $dom->nodeValue = $next['title'];
                            $dom->setAttribute('href',U('Article/detail',array('id'=>$next['id'])) );
                        }
                    }
                    break;
            }
        }
        return $document->saveHTML();
    }
    
    
       
}
