<?php
/*
 * 功能：会员首页
 * Author:资料空白
 * Date:20180604
 */
class PayController extends PcBasicController
{
	private $m_order;
	private $m_payment;
	public function init()
	{
        parent::init();
		$this->m_order = $this->load('order');
		$this->m_payment = $this->load('payment');
	}

	public function indexAction()
	{
		\Yaf\Dispatcher::getInstance()->disableView();
		$token = $this->get('token');//
		$type = $this->get('type');//

		if($token AND $type){
			//从缓存中读取详细信息
			$session_params = \Yaf\Session::getInstance()->__get($token);
			if(is_array($session_params) AND !empty($session_params)){
				$ip = getClientIP();
				//从缓存中读取商品信息
				$product = $this->_getProduct($session_params['hash_id']);
				if($product['requireinput']>0){
					$contact = $this->get('contact');//
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
					$contact = $ip.'@ip.net'; 
				}
				
				//生成orderid
				$orderid = 'zlkb' . date('Y') . date('m') . date('d') . date('H') . date('i') . date('s') . mt_rand(10000, 99999);
				//开始下单，入库
				$order = array(
					'orderid'=>$orderid,
					'userid'=>0,
					'email'=>$contact,
					'pid'=>$product['id'],
					'description'=>"付费商品-".$product['name'],
					'money'=>$product['price'],
					'ip'=>$ip,
					'status'=>0,
					'addtime'=>time(),
				);
				$oid = $this->m_order->Insert($order);
				if($oid>0){
					if($type == 'alipay'){
						$paymethod = 'zfbf2f';
					}elseif($type == 'weixin'){
						$paymethod = 'yzpay';
					}else{
						$result = array('ret'=>1,'msg'=>'暂不支持此支付方式');
						Helper::response($result);
					}
					
					$payments = $this->m_payment->getConfig();
					if(isset($payments[$paymethod]) AND !empty($payments[$paymethod])){
						$payconfig = $payments[$paymethod];
						if($payconfig['active']>0){
							$payclass = "\\Pay\\".$paymethod."\\".$paymethod;
							$PAY = new $payclass();
							$pay_params =array('orderid'=>$orderid,'money'=>$order['money'],'productname'=>$order['description'],'web_url'=>$this->config['web_url']);
							$pay_data = $PAY->pay($payconfig,$pay_params);
							if($pay_data['code']>1){
								$result = array('ret'=>1,'msg'=>$pay_data['msg']);
							}else{
								$session_params['qrcode'] = $pay_data['data']['qr'];
								$session_params['oid'] = $oid;
								$session_params['contact'] = $contact;
								\Yaf\Session::getInstance()->__set($token, $session_params);
								$data = array(
									"qrcode"=>urlencode($pay_data['data']['qr']),
									"payurl"=>$this->config['web_url']."/api/pay/alipay/?token=".$token,
								);
								$result = array('ret'=>0,'data'=>$data);
							}
						}else{
							$result = array('ret'=>1,'msg'=>'异常');
						}
					}else{
						$result = array('ret'=>1,'msg'=>'异常');	
					}
				}else{
					$result = array('ret'=>1,'msg'=>'异常');	
				}
			}else{
				$result = array('ret'=>1,'msg'=>'异常');
			}
		}else{
			$result = array('ret'=>1,'msg'=>'丢失参数');
		}
		Helper::response($result);
	}
	
	//从这里可以直接跳转
	public function alipayAction()
	{
		$token = $this->get('token');
		$session_params = \Yaf\Session::getInstance()->__get($token);
		if(is_array($session_params) AND !empty($session_params)){
			$url = $session_params['qrcode'];
			$this->redirect($url);
		}else{
			$url = "https://mobile.alipay.com/index.htm?cid=wap_dc";
			$this->redirect($url);
		}
		exit();
	}
	
	//检查是否支付完成
	public function checkAction()
	{
		$token = $this->get('token');
		if($token){
			$session_params = \Yaf\Session::getInstance()->__get($token);
			if(is_array($session_params) AND !empty($session_params)){
				$oid = $session_params['oid'];
				$order = $this->m_order->Where(array('id'=>$oid,'isdelete'=>0))->SelectOne();
				if(empty($order)){
					$data=array('ret'=>1,'msg'=>'没有订单');
				}else{
					if($order['status']<1){
						$data = array('ret' => 0, 'msg' => '未支付','data'=>array('ispaid'=>false));
					}else{
						$l_encryption = new Encryption();
						$oid = $l_encryption->encrypt($order['id']); 
						$cookie = "zlkb_token_{$session_params['hash_id']}|{$oid}";
						$data = array('ret' => 0, 'msg' => 'success','data'=>array('ispaid'=>true,'cookie'=>$cookie));
					}
				}
			}else{
				$data = array('ret' => 1, 'msg' => '异常');
			}
		}else{
			$data = array('ret' => 1, 'msg' => '丢失参数');
		}
		Helper::response($data);
	}
	
}