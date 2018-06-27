<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class CaijiController extends AdminController {
 
	private $model_id        =   4; //采集模型id
	/**
     * 批量保存配置
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function save($config){
        if($config && is_array($config)){
            $Config = M('CaijiConfig');
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $Config->where($map)->setField(array('value'=>$value,'update_time'=>time()));
            }
        }
		$this->createWebconfig();
        $this->success('保存成功！');
    }
	
	/* 网站基本配置 */
	public function index(){
        $list   =   M("CaijiConfig")->where(array('status'=>1,'group'=>1))->field('id,name,title,value,remark,type,extra')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
        $this->assign('id',$id);
        $this->meta_title = '采集设置';
        $this->display();
	}
	/* 采集节点 */
	public function jiedian(){

		$model = M('Model')->getByName('caiji_rule');
		$model_id   =   null;
		$cate_id    =   0;
		$this->assign('model', null);
        //解析列表规则
        $fields =	array();
        $grids  =	preg_split('/[;\r\n]+/s', trim($model['list_grid']));
        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val      = explode(':', $value);
            // 支持多个字段显示
            $field   = explode(',', $val[0]);
            $value    = array('field' => $field, 'title' => $val[1]);
            if(isset($val[2])){
                // 链接信息
                $value['href']  =   $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use(&$fields){$fields[]=$match[1];}, $value['href']);
            }
            if(strpos($val[1],'|')){
                // 显示格式定义
                list($value['title'],$value['format'])    =   explode('|',$val[1]);
            }
            foreach($field as $val){
                $array  =   explode('|',$val);
                $fields[] = $array[0];
            }
        }
		$list = M('CaijiRule')->where('status>=0')->select();
        $list = $this->parseDocumentList($list,4);

        $this->assign('list',   $list);
		$this->assign('list_grids', $grids);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
	}
	/* 新增节点 */
	public function addJiedian(){
        // 获取当前的模型信息
        $model    =   M('Model')->getByName('caiji_rule');
        //获取表单字段排序
        $fields = get_model_attribute($this->model_id);
        $this->assign('fields',     $fields);
        $this->assign('model',      $model);
        $this->meta_title = '新增采集节点';
        $this->display('addjiedian');
	}

    /**
     * 节点编辑
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
        $id     =   I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }

        // 获取详细数据 
        $Document = D('CaijiRule');
        $data = $Document->where("id=$id")->find();
        if(!$data){
            $this->error($Document->getError());
        }

		$model_id = $this->model_id;
        // 获取当前的模型信息
        $model    =   M('Model')->getByName('caiji_rule');
        $this->assign('data', $data);
        $this->assign('model_id', $model_id);
        $this->assign('model',      $model);

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields',     $fields);

        $this->meta_title   =   '编辑节点';
        $this->display();
    }

    /**
     * 更新节点
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
        $document   =   D('CaijiRule');
        $res = $document->update();
        if(!$res){
            $this->error($document->getError());
        }else{
			$this->createCjconfig();//生成配置文件
            $this->success('保存成功！');
            //$this->success($res['id']?'更新成功':'新增成功', Cookie('__forward__'));
        }
    }

    /**
     * 设置一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function setStatus($model='CaijiRule'){
        return parent::setStatus('CaijiRule');
    }

	/* 关键词内链 */
	public function linkwords(){
        $list   =   M("CaijiConfig")->where(array('status'=>1,'group'=>2))->field('id,name,title,value,remark,type,extra')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
        $this->assign('id',$id);
        $this->meta_title = '内链关键词设置';
        $this->display('index');
	}
	/* 同义词替换 */
	public function replace(){
        $list   =   M("CaijiConfig")->where(array('status'=>1,'group'=>3))->field('id,name,title,value,remark,type,extra')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
        $this->assign('id',$id);
        $this->meta_title = '同义词替换设置';
        $this->display('index');	
	}
    /*
     * 代理设置
     */
    public function proxy(){
        $list   =   M("CaijiConfig")->where(array('status'=>1,'group'=>6))->field('id,name,title,value,remark,type,extra')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
        //$this->assign('id',$id);
        $this->meta_title = '代理设置';
        $this->assign('act','caiji/proxy');
        $this->display('index');
    }
	/** 过滤屏蔽 **/
	public function filter(){
		$this->display('index');
	}
	/** 设置默认节点 
	 * @id int 节点ID
	**/
	public function useRule($id){
		$act = M('CaijiConfig')->where(array('name'=>'DEFAULT_RULE'))->setField(array('value'=>$id,'update_time'=>time()));
		if($act){
			$this->createWebconfig();
			$this->createCjconfig();//生成配置文件
			$this->success('操作成功');				
		}else{
			$this->error('操作失败');		
		}
	}

	/**生成网站配置文件**/
	public function createWebconfig(){
        $dir = './Runtime/Config/';
        if(!is_dir($dir)) mkdir($dir,0755,true);        
        $file = $dir.'webconfig.php';
		$info = M('CaijiConfig')->where('status=1')->field('name,value')->select();
		$res = '<?php'.PHP_EOL.'/*网站配置文件*/'.PHP_EOL.'return array('.PHP_EOL;
		foreach($info as $list){
			if($list['name'] == 'REPLACE_FILE'){
				$v = str_replace(',',"','",$list['value']);
				$res .= " 'REPLACE_FILE' => array('".$v."'),".PHP_EOL;
			}else{
				$v = $list['value'];
				$v = addcslashes($v,"\\");//转义特殊符号，避免冲突
				$v = addcslashes($v,"'");//转义特殊符号，避免冲突
				$res .= " '".$list['name']."' => '".$v."',".PHP_EOL;
			}
		}
		$res .= ');';
		file_put_contents($file,$res);	
	}

	/**生成采集规则配置文件**/
	public function createCjconfig(){
        $dir = './Runtime/Config/';
        if(!is_dir($dir)) mkdir($dir,0755,true);        
        $file = $dir.'cjconfig.php';
		$rule_id = M('CaijiConfig')->where('name="DEFAULT_RULE" and status=1')->getField('value');
		$info = M('CaijiRule')->where("id=$rule_id")->find();
		$res = '<?php'.PHP_EOL.'/*采集规则配置文件*/'.PHP_EOL.'return array('.PHP_EOL;
		foreach($info as $k=>$v){
			$v = addcslashes($v,"\\");//转义特殊符号，避免冲突
			$v = addcslashes($v,"'");//转义特殊符号，避免冲突
			$res .= " '".$k."' => '".trim($v)."',".PHP_EOL;
		}
		$res .= ');';
		file_put_contents($file,$res);	
	}
    /*
     * 字符串替换
     */
    public function replaceFormat($id=''){
        if($id){
            // 获取详细数据
            $Document = M('CaijiRule');
            $conf = $Document->where("id=$id")->find();
            if(!$conf){
                $this->error($Document->getError());
            }
            $str_rules = $conf['str_rules'];
            $arr = explode('##########',$str_rules);
            $arr = array_filter($arr);
            $data = array();
            foreach($arr as $list){
                $strs = explode('******',$list);
                //$find[] = trim($strs[0]);
                //$replace[] = trim($strs[1]);
                $data[] = array('find'=>trim($strs[0]),'replace'=>trim($strs[1]));
            }
            $num = count($data);
            $num = $num==0?1:$num;
            $size = (intval ($num/10)+1)*10;
        }else{
            $data = array();
            $size = 10;
        }
        $data = array_pad($data,$size,'');
        $this->assign('data',$data);
        $this->display('replaceformat');
    }

}