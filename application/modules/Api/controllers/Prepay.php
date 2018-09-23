<?php
/*
 * 功能：会员首页
 * Author:资料空白
 * Date:20180604
 */
class PrepayController extends PcBasicController
{
	public function init()
	{
        parent::init();
	}

	public function indexAction()
	{
		Yaf\Dispatcher::getInstance()->disableView();
		$url = $this->get('url');//请求地址
		$enter_type = $this->get('enter_type');//类型
		$hash_id = $this->get('hash_id');//付费资源hashid
		$callback = $this->get('callback');

		if($enter_type AND $url AND $hash_id AND $callback){
			try{
				$product = $this->_getProduct($hash_id);
				if(is_array($product) AND !empty($product)){
					//1.1查询url是否经过授权
						$url_arr = parse_url($url);
						if(is_array($url_arr) AND isset($url_arr['host'])){
							if(strstr($this->config['web_url'],$url_arr['host']) OR (strlen($product['url'])>0 AND strstr($product['url'],$url_arr['host']))){
								//1.3显示商品
								if($product['active']>0){
									if($product['stockcontrol']>0 AND $product['qty']<1){
										$data = array("status"=>2,"hashid"=>$hash_id);
									}else{
										$uuid = md5(session_id());
										$token = md5($uuid.$hash_id);	
										$session_params = array('hash_id'=> $hash_id,'pid'=>$product['id'],'url'=> $url);
										\Yaf\Session::getInstance()->__set($token,$session_params);
										$data = array("token"=>$token,"uuid"=>$uuid);
									}
								}else{
									$data = array("status"=>4,"hashid"=>$hash_id);
								}
							}else{
								$data = array("status"=>5,"hashid"=>$hash_id);
							}
						}else{
							$data = array("status"=>5,"hashid"=>$hash_id);
						}
				}else{
					$data = array("status"=>-1,"hashid"=>$hash_id);
				}
				$result = array('ret'=>0,'data'=>$data);
			} catch (\Exception $e) {
				$result = array('ret'=>1,'data'=>array(),'msg'=>$e->getMessage());
			}
		}else{
			$data = array("status"=>0,"hashid"=>$hash_id);
			$result = array('ret'=>1,'data'=>$data,'msg'=>'丢失参数');
		}
		die($callback . '(' . json_encode($result) . ')');
	}

}