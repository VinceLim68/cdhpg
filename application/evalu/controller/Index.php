<?php

namespace app\evalu\controller;

use think\Controller;
use app\evalu\model\Comm;
use think\Loader;
use app\evalu\logic\CommL;
use app\evalu\model\SalesModel;

class Index extends Controller {
	protected $db;
	protected function _initialize() {
		parent::_initialize ();
		$this->db = new SalesModel();
	}
	public function index() {
		if (request ()->isPost ()) {
			$result = $this->validate ( input ( 'post.' ), [ 
					'searchfor' => 'require|max:25|min:2' 
			] );
			
			if (true !== $result) {
				// 验证失败 输出错误信息
				$this->error ( $result );
				exit ();
			} else {
				/* 查询数据并输出 */
				$fields = CommL::macthSearch ( input ( 'searchfor' ) );
				$this->assign ( 'fields', $fields );
				// $user = Comm::get(1);
				// halt($user->pri_level) ;
			}
		} else {
			$this->assign ( 'fields', array () );
		}
		
		return $this->fetch ();
	}
	public function _index_back() {
		/* 这是原来的index方法，先备份在这里，主要学习验证器的应用 */
		if (request ()->isPost ()) {
			
			/* 加载验证器来验证 数据 */
			$validate = Loader::validate ( 'Sales' );
			
			if (! $validate->check ( input ( 'post.' ) )) {
				// 验证失败 输出错误信息
				$this->error ( $validate->getError () );
				exit ();
			} else {
				/* 查询数据并输出 */
				$list = $this->db->where ( 'community_name', input ( 'searchfor' ) )->limit ( 10 )->select ();
				foreach ( $list as $key => $user ) {
					echo $user . '<br/>';
				}
			}
		}
		
		return $this->fetch ();
	}
	public function getUrl() {
		
		/**
		 * PHP获取路径或目录实现
		 */
		echo '魔术变量，获取当前文件的绝对路径<br/>';
		echo "__FILE__: ========> " . __FILE__;
		echo '<br/><br/>';
		
		echo '魔术变量，获取当前脚本的目录<br/>';
		echo "__DIR__: ========> " . __DIR__;
		echo '<br/><br/>';
		
		echo 'dirname返回路径的目录部分,dirname(__FILE__)相当于__DIR__<br/>';
		echo "dirname(__FILE__): ========> " . dirname ( __FILE__ );
		echo '<br/><br/>';
		
		echo '$_SERVER["PHP_SELF"] ';
		echo '和$_SERVER["SCRIPT_NAME"]的结果一般相同，他们都是获取当前脚本的文件名<br/>';
		echo '只有当php以cgi方式运行时有区别，但是现在几乎找不到以cgi方式运行php了<br/>';
		echo '$_SERVER["PHP_SELF"]: ========> ' . $_SERVER ['PHP_SELF'];
		echo '<br/>';
		echo '$_SERVER["SCRIPT_NAME"]: ========> ' . $_SERVER ['SCRIPT_NAME'];
		echo '<br/><br/>';
		
		echo '当前执行脚本的绝对路径。记住，在CLI方式运行php是获取不到的<br/>';
		echo '$_SERVER["SCRIPT_FILENAME"]: ========> ' . $_SERVER ['SCRIPT_FILENAME'];
		echo '<br/><br/>';
		
		echo '当前运行脚本所在的文档根目录。在服务器配置文件中定义。<br/>';
		echo '$_SERVER["DOCUMENT_ROOT"]: ========> ' . $_SERVER ['DOCUMENT_ROOT'];
		echo '<br><br/>';
		
		echo 'getcwd()返回当前工作目录<br/>';
		echo "getcwd(): ========> " . getcwd ();
		echo '<br><br/>';
	}
	public function main() {
		return $this->fetch ();
	}
}

