<?php
/**
 * File: notify.php
 * Functionality: 支付返回处理
 * Author: 资料空白
 * Date: 2018-6-8
 */
namespace Pay;

class notify
{
	//处理返回
	public function run(array $params)
	{
		//支付渠道
		$paymethod = $params['paymethod'];
		//订单号
		$tradeid = $params['tradeid'];
		//支付金额
		$paymoney = $params['paymoney'];
		//本站订单号
		$orderid = $params['orderid'];
		
		$m_order =  \Helper::load('order');
		$m_products_card = \Helper::load('products_card');
		$m_email_queue = \Helper::load('email_queue');
		$m_products = \Helper::load('products');
		$m_config = \Helper::load('config');
		$web_config = $m_config->getConfig();
		
		try{
				//1.先更新支付总金额
				$update = array('status'=>1,'paytime'=>time(),'tradeid'=>$tradeid,'paymethod'=>$paymethod,'paymoney'=>$paymoney);
				$u = $m_order->Where(array('orderid'=>$orderid,'status'=>0))->Update($update);
				if(!$u){
					$data =array('code'=>1004,'msg'=>'更新失败');
				}else{
					//2.开始进行订单处理
					//通过orderid,查询order订单,与商品信息
					$order = $m_order->Where(array('orderid'=>$orderid))->SelectOne();
					$product = $m_products->SelectByID('name,stockcontrol,qty,contact',$order['pid']);
					
					if(!empty($order) AND !empty($product)){
	
							//3.自动处理
							//查询通过订单中记录的pid，根据购买数量查询卡密
							$cards = $m_products_card->Where(array('pid'=>$order['pid'],'active'=>0,'isdelete'=>0))->SelectOne();
							if(is_array($cards) AND !empty($cards)){
								//3.1 库存充足,获取对应的卡id,卡密
								
								//3.1.2 进行卡密处理,如果进行了库存控制，就开始处理
								if($product['stockcontrol']>0){
									//3.1.2.1 直接进行卡密与订单的关联
									$m_products_card->Where(array('id'=>$cards['id']))->Where(array('active'=>0))->Update(array('active'=>1));
									//3.1.2.2 然后进行库存清减
									$qty_m = array('qty' => 'qty-1','sellnum'=>'sellnum+1');
									$m_products->Where(array('id'=>$order['pid'],'stockcontrol'=>1))->Update($qty_m,TRUE);
									$kucunNotic=";当前商品库存剩余:".($product['qty']-1);
								}else{
									//3.1.2.3不进行库存控制时,自动发货商品是不需要减库存，也不需要取消卡密；因为这种情况下的卡密是通用的；
									$qty_m = array('sellnum'=>'sellnum+1');
									$m_products->Where(array('id'=>$order['pid']))->Update($qty_m,TRUE);
									$kucunNotic="";
								}
								//3.1.3 更新订单状态,同时把卡密写到订单中
								$m_order->Where(array('orderid'=>$orderid,'status'=>1))->Update(array('status'=>2,'kami'=>$cards['card']));
								//3.1.4 把邮件通知写到消息队列中，然后用定时任务去执行即可
								$m = array();
								//3.1.4.1通知用户,定时任务去执行
								if(isEmail($product['contact'])){
									$content = '用户:' . $order['email'] . ',购买的商品['.$product['name'].'],卡密发送成功'.$kucunNotic;
									$m[]=array('email'=>$product['contact'],'subject'=>'用户购买商品','content'=>$content,'addtime'=>time(),'status'=>0);
								}
								//3.1.4.2通知管理员,定时任务去执行
								if(isEmail($web_config['admin_email'])){
									$content = '用户:' . $order['email'] . ',购买的商品['.$product['name'].'],卡密发送成功'.$kucunNotic;
									$m[]=array('email'=>$web_config['admin_email'],'subject'=>'用户购买商品','content'=>$content,'addtime'=>time(),'status'=>0);
								}
								
								if(!empty($m)){
									$m_email_queue->MultiInsert($m);
								}
								
								//这里刷新一下缓存
								$product = $m_products->SelectByID('',$order['pid']);
								$this->_setCache($product['hashid'],$product);
								
								$data =array('code'=>1,'msg'=>'自动发卡');
							}else{
								//3.2 这里说明库存不足了，干脆就什么都不处理，直接记录异常，同时更新订单状态
								$m_order->Where(array('orderid'=>$orderid,'status'=>1))->Update(array('status'=>3));
								file_put_contents(YEWU_FILE, CUR_DATETIME.'-'.'库存不足，无法处理'.PHP_EOL, FILE_APPEND);
								//3.2.3邮件通知写到消息队列中，然后用定时任务去执行即可
								$m = array();
								if(isEmail($web_config['admin_email'])){
									$content = '用户:' . $order['email'] . ',购买的商品['.$product['name'].'],由于库存不足暂时无法处理,请尽快处理!';
									$m[] = array('email'=>$web_config['admin_email'],'subject'=>'用户购买商品','content'=>$content,'addtime'=>time(),'status'=>0);
								}
								//3.2.3.2通知管理员,定时任务去执行
								if(isEmail($product['contact'])){
									$content = '用户:' . $order['email'] . ',购买的商品['.$product['name'].'],由于库存不足暂时无法处理,请尽快处理!';
									$m[] = array('email'=>$product['contact'],'subject'=>'用户购买商品','content'=>$content,'addtime'=>time(),'status'=>0);
								}
								
								if(!empty($m)){
									$m_email_queue->MultiInsert($m);
								}
								$data =array('code'=>1,'msg'=>'库存不足,无法处理');
							}

					}else{
						//这里有异常，到时统一记录处理
						$data =array('code'=>1003,'msg'=>'订单/商品不存在');
					}
				}	
		} catch(\Exception $e) {
			file_put_contents(YEWU_FILE, CUR_DATETIME.'-'.$e->getMessage().PHP_EOL, FILE_APPEND);
			$data =array('code'=>1001,'msg'=>$e->getMessage());
		}
		//file_put_contents(YEWU_FILE, CUR_DATETIME.'-'.'异步处理结果:'.json_encode($data).PHP_EOL, FILE_APPEND);
		return $data;
	}
	
	//刷新缓存
	public function _setCache($hash_id,$content)
	{
		try {
			$redis = new Phpredis();
		} catch (\Exception $e) {
			$redis = false;
		}
		if($redis===false){
			\Yaf\Session::getInstance()->__set($hash_id,$content);
		}else{	
			$redis->set($hash_id,json_encode($content));
		}
		return true;
	}
	
}