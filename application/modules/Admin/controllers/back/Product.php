<?php

/*
 * 功能：会员中心－个人中心
 * Author:资料空白
 * Date:20180509
 */

class ProductController extends PcBasicController
{
	private $m_products;
	private $m_products_card;
	
    public function init()
    {
        parent::init();
		$this->m_products = $this->load('products');
		$this->m_products_card = $this->load('products_card');
    }

    public function indexAction()
    {
        if ($this->login==FALSE AND !$this->userid) {
            $this->redirect("/member/login");
            return FALSE;
        }
		$data = array();
		$data['title'] = "出售资源";
        $this->getView()->assign($data);
    }
	
	//我的产品ajax
	public function ajaxAction()
	{
        if ($this->login==FALSE AND !$this->userid) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }	

		//2.再开始进行数据处理
		$where = array('userid'=>$this->userid,'isdelete'=>0);
		$page = $this->get('page');
		$page = is_numeric($page) ? $page : 1;
		
		$limit = $this->get('limit');
		$limit = is_numeric($limit) ? $limit : 10;
		
		$total=$this->m_products->Where($where)->Total();
		
        if ($total > 0) {
            if ($page > 0 && $page < (ceil($total / $limit) + 1)) {
                $pagenum = ($page - 1) * $limit;
            } else {
                $pagenum = 0;
            }
			
            $limits = "{$pagenum},{$limit}";
			$items=$this->m_products->Where($where)->Limit($limits)->Order(array('id'=>'DESC'))->Select();
			
            if (empty($items)) {
                $data = array('code'=>0,'count'=>0,'data'=>array(),'msg'=>'无数据');
            } else {
                $data = array('code'=>0,'count'=>$total,'data'=>$items,'msg'=>'有数据');
            }
        } else {
            $data = array('code'=>0,'count'=>0,'data'=>array(),'msg'=>'无数据');
        }
		Helper::response($data);
	}
	
    public function addAction()
    {
        if ($this->login==FALSE AND !$this->userid) {
            $this->redirect("/member/login");
            return FALSE;
        }
		$data = array();
		$data['title'] = "添加资源";
		$this->getView()->assign($data);
    }
	
    public function editAction()
    {
        if ($this->login==FALSE AND !$this->userid) {
            $this->redirect("/member/login");
            return FALSE;
        }
		$hashid = $this->get('hashid');
		if($hashid AND strlen($hashid)>0){
			$data = array();
			$product = $this->m_products->Where(array('hashid'=>$hashid,'isdelete'=>0))->SelectOne();
			if(is_array($product) AND !empty($product)){
				$data['product'] = $product;
				
				$urls = str_replace(";","\r\n",$product['url']); 
				$data['urls'] = $urls;

				//获取卡密
				$content = '';
				if($product['isfaka']>0){
					$cards = $this->m_products_card->Where(array('isdelete'=>0,'pid'=>$product['id']))->Select();
					foreach($cards AS $card){
						$content .= $card['card']."\r\n";
					}
				}else{
					$cards = $this->m_products_card->Where(array('isdelete'=>0,'pid'=>$product['id']))->SelectOne();
					$content .= $cards['card'];
				}

				$data['content'] = $content;
				$data['title'] = "编辑资源";
				$this->getView()->assign($data);
			}else{
				$this->redirect("/member/product");
				return FALSE;
			}
		}else{
            $this->redirect("/member/product");
            return FALSE;
		}
    }
	
	public function editajaxAction()
	{
		$method = $this->getPost('method',false);
		$id = $this->getPost('id',false);
		$csrf_token = $this->getPost('csrf_token', false);
		
		$data = array();
		
        if ($this->login==FALSE AND !$this->userid) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }	
		
		if($method AND $csrf_token){
			if ($this->VerifyCsrfToken($csrf_token)) {
				if($method == 'edit' AND is_numeric($id) AND $id>0){
					$name = $this->getPost('name',false);
					$price = $this->getPost('price',false);
					$active = $this->getPost('active',false);
					if($name AND is_numeric($price) AND is_numeric($active) AND $csrf_token){
						if($price<1){
							$data = array('code' => 1000, 'msg' => '价格不能低于1元');
							Helper::response($data);
						}

						$m = array('name'=>$name,'price'=>$price,'active'=>$active);
						
						$u = $this->m_products->Where(array('userid'=>$this->userid,'id'=>$id))->Update($m);
						if($u){
							$product = $this->m_products->SelectByID('',$id);
							$this->_setCache($product['hashid'],$product);
							$data = array('code' => 1, 'msg' => '更新成功');
						}else{
							$data = array('code' => 1003, 'msg' => '更新失败');
						}
					}else{
						$data = array('code' => 1000, 'msg' => '丢失参数');
					}
				}elseif($method == 'add'){
					$name = $this->getPost('name',false);
					$price = $this->getPost('price',false);
					$active = $this->getPost('active',false);
					$isfaka = $this->getPost('price',false);
					$kami = $this->getPost('kami',false);
					if($name AND is_numeric($price) AND is_numeric($active) AND is_numeric($isfaka) AND $kami AND $csrf_token){
						if($price<1){
							$data = array('code' => 1000, 'msg' => '价格不能低于1元');
							Helper::response($data);
						}
						$m=array(
							'typeid'=>1,
							'name'=>$name,
							'userid'=>$this->userid,
							'contact'=>$this->uinfo['email'],
							'description'=>'',
							'qty'=>0,
							'price'=>$price,
							'active'=>$active,
							'addtime'=>time(),
						);

						$pid = $this->m_products->Insert($m);
						if($pid>0){
							$hashid = generate_code($pid+1000);
							$this->m_products->UpdateByID(array('hashid'=>$hashid),$pid);
							
							//插入卡密
							if($isfaka>0){
								//批量
								$m = array();
								$huiche=array("\n","\r");
								$replace='\r\n';
								$newTxtFileData=str_replace($huiche,$replace,$kami); 
								$newTxtFileData_array = explode($replace,$newTxtFileData);
								foreach($newTxtFileData_array AS $line){
									if(strlen($line)>0){
										$line = str_replace(array("\r","\n"), "\\n", $line);
										$m[]=array('pid'=>$pid,'card'=>$line,'addtime'=>time());
									}
								}
								if(!empty($m)){
									$u = $this->m_products_card->MultiInsert($m);
									if($u){
										//增加商品数量
										$addNum = count($m);
										$qty_m = array('qty' => 'qty+'.$addNum);
										$this->m_products->Where(array('id'=>$pid,'isfaka'=>1))->Update($qty_m,TRUE);
										$data = array('code' => 1, 'msg' => '成功','data'=>array('hashid'=>$hashid));
									}else{
										$data = array('code' => 1004, 'msg' => '失败');
									}
								}else{
									$data = array('code' => 1, 'msg' => '资源添加成功，但没有卡密存在','data'=>array('hashid'=>$hashid));
								}
							}else{
								//单个
								$m = array('pid'=>$pid,'card'=>$kami,'addtime'=>time());
								$u = $this->m_products_card->Insert($m);
								$data = array('code' => 1, 'msg' => '成功','data'=>array('hashid'=>$hashid));
							}
						}else{
							$data = array('code' => 1003, 'msg' => '新增失败');
						}
					}else{
						$data = array('code' => 1000, 'msg' => '丢失参数');
					}
				}elseif($method == 'auth_edit' AND $id>0){
					$urls = $this->getPost('urls',false);
					$huiche = array("\n","\r");
					$replace = '\r\n';
					$newTxtFileData = str_replace($huiche,$replace,$urls); 
					$newTxtFileData_array = explode($replace,$newTxtFileData);
					$url = implode(";",$newTxtFileData_array);
					$u = $this->m_products->UpdateByID(array('url'=>$url),$id);
					if($u){
						$product = $this->m_products->SelectByID('',$id);
						$this->_setCache($product['hashid'],$product);
						$data = array('code' => 1, 'msg' => '更新成功');
					}else{
						$data = array('code' => 1003, 'msg' => '更新失败');
					}
				}else{
					$data = array('code' => 1002, 'msg' => '未知方法');
				}
			} else {
                $data = array('code' => 1001, 'msg' => '页面超时，请刷新页面后重试!');
            }
		}else{
			$data = array('code' => 1000, 'msg' => '丢失参数');
		}
		Helper::response($data);
	}
	
    public function getlistbytidAction()
    {
		$tid = $this->getPost('tid');
		$csrf_token = $this->getPost('csrf_token', false);
		
		if($tid AND $csrf_token){
			if ($this->VerifyCsrfToken($csrf_token)) {
				$data = array();
				$field = array('id', 'name');
				$products = $this->m_products->Field($field)->Where(array('typeid'=>$tid,'userid'=>$this->userid,'active'=>1,'isdelete'=>0))->Select();
				$data['products'] = $products;
				$result = array('code' => 1, 'msg' => 'success','data'=>$data);
			} else {
                $result = array('code' => 1001, 'msg' => '页面超时，请刷新页面后重试!');
            }
		}else{
			$result = array('code' => 1000, 'msg' => '参数错误');
		}
        Helper::response($result);
    }
	
}