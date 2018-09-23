<?php

/*
 * 功能：后台中心－卡密管理
 * Author:资料空白
 * Date:20180509
 */

class ProductscardController extends PcBasicController
{
	private $m_products_card;
	private $m_products_type;
	private $m_products;
    public function init()
    {
        parent::init();
		$this->m_products_card = $this->load('products_card');
		$this->m_products = $this->load('products');
		$this->m_products_type = $this->load('products_type');
    }

    public function indexAction()
    {
        if ($this->login==FALSE AND !$this->userid) {
            $this->redirect("/member/login");
            return FALSE;
        }

		$data = array();
		$products=$this->m_products->Where(array('isdelete'=>0))->Order(array('id'=>'DESC'))->Select();
		$data['products'] = $products;
		$data['title'] = "付费内容";
		$this->getView()->assign($data);
    }

	//ajax
	public function ajaxAction()
	{
        if ($this->login==FALSE AND !$this->userid) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }
		$card = $this->get('card',false);
		$active = $this->get('active');
		$pid = $this->get('pid');
        //查询条件
        $get_params = [
            'card' => $card,
			'active' => $active,
			'pid' => $pid,
        ];   
        $where = $this->conditionSQL($get_params);
		$where1 = $this->conditionSQL($get_params,'p1.');
		
		$page = $this->get('page');
		$page = is_numeric($page) ? $page : 1;
		
		$limit = $this->get('limit');
		$limit = is_numeric($limit) ? $limit : 10;
		
		$total=$this->m_products_card->Where(array('isdelete'=>0))->Where($where)->Total();
		
        if ($total > 0) {
            if ($page > 0 && $page < (ceil($total / $limit) + 1)) {
                $pagenum = ($page - 1) * $limit;
            } else {
                $pagenum = 0;
            }
			
            $limits = "{$pagenum},{$limit}";
			$sql ="SELECT p1.*,p2.name FROM `t_products_card` as p1 left join `t_products` as p2 on p1.pid=p2.id Where p1.isdelete=0 and {$where1} Order by p1.id desc LIMIT {$limits}";
			$items=$this->m_products_card->Query($sql);
			
            if (empty($items)) {
                $data = array('code'=>1002,'count'=>0,'data'=>array(),'msg'=>'无数据');
            } else {
                $data = array('code'=>0,'count'=>$total,'data'=>$items,'msg'=>'有数据');
            }
        } else {
            $data = array('code'=>1001,'count'=>0,'data'=>array(),'msg'=>'无数据');
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
		
		$order = array('sort_num' => 'ASC');
		$products_type = $this->m_products_type->Where(array('active'=>1,'isdelete'=>0))->Order($order)->Select();
		$data['products_type'] = $products_type;
		
		$this->getView()->assign($data);
    }

    public function addplusAction()
    {
        if ($this->login==FALSE AND !$this->userid) {
            $this->redirect("/member/login");
            return FALSE;
        }
		$data = array();
		
		$order = array('sort_num' => 'ASC');
		$products_type = $this->m_products_type->Where(array('active'=>1,'isdelete'=>0))->Order($order)->Select();
		$data['products_type'] = $products_type;
		
		$this->getView()->assign($data);
    }	
	
	public function addajaxAction()
	{
		$method = $this->getPost('method',false);
		$pid = $this->getPost('pid',false);
		$card = $this->getPost('card',false);
		$csrf_token = $this->getPost('csrf_token', false);
		
		$data = array();
		
        if ($this->login==FALSE AND !$this->userid) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }	
		
		if($method AND $pid AND $card AND $csrf_token){
			if ($this->VerifyCsrfToken($csrf_token)) {
				if($method == 'add'){
					$card = str_replace(array("\r","\n"), "\\n", $card);
					$m=array(
						'pid'=>$pid,
						'card'=>$card,
						'addtime'=>time(),
					);
					$u = $this->m_products_card->Insert($m);
					if($u){
						//新增商品数量
						$qty_m = array('qty' => 'qty+1');
						$this->m_products->Where(array('id'=>$pid,'stockcontrol'=>1))->Update($qty_m,TRUE);
						$data = array('code' => 1, 'msg' => '新增成功');
					}else{
						$data = array('code' => 1003, 'msg' => '新增失败');
					}
				}elseif($method == 'addplus'){
					//开始处理
					$m = array();
					$huiche=array("\n","\r");
					$replace='\r\n';
					$newTxtFileData=str_replace($huiche,$replace,$card); 
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
							$this->m_products->Where(array('id'=>$pid,'stockcontrol'=>1))->Update($qty_m,TRUE);
							$data = array('code' => 1, 'msg' => '成功');
						}else{
							$data = array('code' => 1004, 'msg' => '失败');
						}
					}else{
						$data = array('code' => 1003, 'msg' => '没有卡密存在','data'=>array());
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

	public function deleteAction()
	{
		$id = $this->get('id',false);
		$csrf_token = $this->getPost('csrf_token', false);
		
		$data = array();
		
        if ($this->login==FALSE AND !$this->userid) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }	
		
		if($csrf_token){
			if ($this->VerifyCsrfToken($csrf_token)) {
				if($id AND is_numeric($id) AND $id>0){
					$delete = $this->m_products_card->UpdateByID(array('isdelete'=>1),$id);
					if($delete){
						//减少商品数量
						$cards = $this->m_products_card->SelectByID('pid',$id);
						$qty_m = array('qty' => 'qty-1');
						$this->m_products->Where(array('id'=>$cards['pid'],'stockcontrol'=>1))->Update($qty_m,TRUE);
						$data = array('code' => 1, 'msg' => '成功');
					}else{
						$data = array('code' => 1003, 'msg' => '删除失败');
					}
				}else{
					$ids = json_decode($id,true);
					if(isset($ids['ids']) AND !empty($ids['ids'])){
						$idss = implode(",",$ids['ids']);
						$where = "id in ({$idss})";
						$delete = $this->m_products_card->Where($where)->Update(array('isdelete'=>1));
						if($delete){
							foreach($ids['ids'] AS $idd){
								//减少商品数量
								$cards = $this->m_products_card->SelectByID('pid',$idd);
								$qty_m = array('qty' => 'qty-1');
								$this->m_products->Where(array('id'=>$cards['pid'],'stockcontrol'=>1))->Update($qty_m,TRUE);
							}
							$data = array('code' => 1, 'msg' => '成功');
						}else{
							$data = array('code' => 1003, 'msg' => '删除失败');
						}
					}else{
						$data = array('code' => 1000, 'msg' => '请选中需要删除的卡密');
					}
				}
			} else {
                $data = array('code' => 1001, 'msg' => '页面超时，请刷新页面后重试!');
            }
		}else{
			$data = array('code' => 1000, 'msg' => '丢失参数');
		}
		Helper::response($data);
	}


	
    private function conditionSQL($param,$alias='')
    {
        $condition = "1";
        if (isset($param['card']) AND empty($param['card']) === FALSE) {
            $condition .= " AND {$alias}`card` LIKE '%{$param['card']}%'";
        }
        if (isset($param['active']) AND $param['active']>-1 ) {
            $condition .= " AND {$alias}`active` = {$param['active']}";
        }
        if (isset($param['pid']) AND empty($param['pid']) === FALSE AND $param['pid']>0 ) {
            $condition .= " AND {$alias}`pid` = {$param['pid']}";
        }		
        return ltrim($condition, " AND ");
    }
}