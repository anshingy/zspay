<?php
/*
 * 功能：会员首页
 * Author:资料空白
 * Date:20180604
 */
class ValidateController extends PcBasicController
{
	private $m_order;
	public function init()
	{
        parent::init();
		$this->m_order = $this->load('order');
	}

	public function indexAction()
	{
		\Yaf\Dispatcher::getInstance()->disableView();
		$token = $this->get('token');//当前token
		$hash_id = $this->get('hash_id');//商品id
		
		if($token AND $hash_id){
			$product = $this->_getProduct($hash_id);
			if(is_array($product) AND !empty($product)){
				$session_params = \Yaf\Session::getInstance()->__get($token);
				if(is_array($session_params) AND !empty($session_params)){
					if($product['requireinput']>0){
						$contact = $this->get('contact');//邮箱
						if($contact){
							if(!isEmail($contact)){
								$result = array('ret'=>1,'msg'=>'请输入正确的邮箱');
								Helper::response($result);
							}
						}else{
							$result = array('ret'=>1,'msg'=>'请输入邮箱');
							Helper::response($result);	
						}
					}else{
						$contact = $session_params['contact'];//邮箱
						if(!$contact){
							$result = array('ret'=>1,'msg'=>'未找到对应的支付记录');
							Helper::response($result);	
						}
					}

								
					if($product['requireinput']>0){
						$where = array('email'=>$contact,'pid'=>$session_params['pid'],'isdelete'=>0);
						$order = $this->m_order->Where("status>0")->Where($where)->Select();
						if(is_array($order) AND !empty($order)){
							$order_id_array = array_column($order, 'id');
							$order_id_str = implode(',',$order_id_array);
							$l_encryption = new Encryption();
							$cookie = $l_encryption->encrypt($order_id_str);
							$data = array('cookie'=>"zlkb_token_{$hash_id}|{$cookie}");
							$result = array('ret'=>0,'data'=>$data);
						}else{
							$result = array('ret'=>1,'msg'=>"未找到对应的支付记录");
						}
					}else{
						$where = array('id'=>$session_params['oid'],'email'=>$contact,'pid'=>$session_params['pid'],'isdelete'=>0);
						$order = $this->m_order->Where("status>0")->Where($where)->SelectOne();
						if(is_array($order) AND !empty($order)){
							$l_encryption = new Encryption();
							$cookie = $l_encryption->encrypt($session_params['oid']);
							$data = array('cookie'=>"zlkb_token_{$hash_id}|{$cookie}");
							$result = array('ret'=>0,'data'=>$data);
						}else{
							$result = array('ret'=>1,'msg'=>"未找到对应的支付记录");
						}	
					}
				}else{
					$result = array('ret'=>1,'msg'=>"异常!");
				}	
			}else{
				$result = array('ret'=>1,'msg'=>"信息不存在!");
			}
		}else{
			$result = array('ret'=>1,'msg'=>"丢失参数!");
		}
		Helper::response($result);
	}	
}