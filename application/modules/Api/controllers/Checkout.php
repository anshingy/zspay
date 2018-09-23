<?php
/*
 * 功能：会员首页
 * Author:资料空白
 * Date:20180604
 */
class CheckoutController extends PcBasicController
{
	public function init()
	{
        parent::init();
	}

	
	public function indexAction()
	{
		$token = $this->get('token');
		$view = $this->get('view');
		$iframe = $this->get('iframe');
		
		if($token AND $view){
			//从缓存中读取详细信息
			$session_params = \Yaf\Session::getInstance()->__get($token);
			if(is_array($session_params) AND !empty($session_params)){
				$product = $this->_getProduct($session_params['hash_id']);
				$data['token'] = $token;
				$data['session_params'] = $session_params;
				$data['product'] = $product;
				$this->getView()->assign($data);
			}else{
				header('HTTP/1.1 403 Forbidden');
				exit();
			}
		}else{
			header('HTTP/1.1 403 Forbidden');
			exit();
		}
	}
	
}