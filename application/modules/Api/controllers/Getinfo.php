<?php
/*
 * 功能：会员首页
 * Author:资料空白
 * Date:20180604
 */
class GetinfoController extends PcBasicController
{
	private $m_products;
	private $m_order;
	public function init()
	{
        parent::init();
		$this->m_products = $this->load('products');
		$this->m_order = $this->load('order');
	}

	public function indexAction()
	{
		Yaf\Dispatcher::getInstance()->disableView();
		$hash_id = $this->get('hash_id');//信息编码
		$url = $this->get('url');//请求页面地址,用于校验页面是否授权
		$auth = $this->get('auth');//当前商品授权auth
		$callback = $this->get('callback');
		
		if($hash_id AND $url AND $callback){
			try{
				$product = $this->_getProduct($hash_id);
				if(is_array($product) AND !empty($product)){
					//1.1查询url是否经过授权
						$url_arr = parse_url($url);
						if(is_array($url_arr) AND isset($url_arr['host'])){
							if(strstr($this->config['web_url'],$url_arr['host']) OR (strlen($product['url'])>0 AND strstr($product['url'],$url_arr['host']))){
								if($auth){
									//1.2显示付费信息
									$l_encryption = new Encryption();
									$order_id_str = $l_encryption->decrypt($auth);
									if($order_id_str){
										$order = $this->m_order->Where("status>0 AND id in ({$order_id_str})")->Where(array('pid'=>$product['id'],'isdelete'=>0))->Order(array('id'=>'DESC'))->Select();
										if(is_array($order) AND !empty($order)){
											if(count($order)>1){
												$archives = array();
												if($product['stockcontrol']>0){
													
													foreach($order AS $k=>$o){
														if($k>0){
															$archives[] = array('date'=>date('Y-m-d',$o['paytime']),'content'=>$o['kami']);
														}
													}
												}
												
												$data = array("status"=>1,"hashid"=>$hash_id,"price"=>$product['price'],"soldcount"=>$product['sellnum'],"content"=>$order[0]['kami'],'archives'=>$archives);
												$result = array('ret'=>0,'data'=>$data);
												die($callback . '(' . json_encode($result) . ')');
											}else{
												$data = array("status"=>1,"hashid"=>$hash_id,"price"=>$product['price'],"soldcount"=>$product['sellnum'],"content"=>$order[0]['kami']);
												$result = array('ret'=>0,'data'=>$data);
												die($callback . '(' . json_encode($result) . ')');
											}
										}
									}
								}	
								//1.3显示商品
								if($product['active']>0){
									if($product['stockcontrol']>0 AND $product['qty']<1){
										$data = array("status"=>2,"hashid"=>$hash_id);
									}else{
										$data = array("status"=>0,"hashid"=>$hash_id,"price"=>$product['price'],"soldcount"=>$product['sellnum'],'requireinput'=>$product['requireinput']);
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
			$result = array('ret'=>1,'data'=>array(),'msg'=>'丢失参数');
		}
		die($callback . '(' . json_encode($result) . ')');
	}

}