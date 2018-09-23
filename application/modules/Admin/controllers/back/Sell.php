<?php

/*
 * 功能：会员中心－日志中心
 * Author:资料空白
 * Date:20150902
 */

class SellController extends PcBasicController
{
	private $m_order;
    public function init()
    {
        parent::init();
		$this->m_order = $this->load('order');
    }

    public function indexAction()
    {
        if ($this->login==FALSE AND !$this->userid) {
            $this->redirect("/member/login");
            return FALSE;
        }
		$data = array();
		$data['title'] = "销售记录";
        $this->getView()->assign($data);
    }

	
	//ajax
	public function ajaxAction()
	{
        if ($this->login==FALSE AND !$this->userid) {
            $data = array('code' => 1000, 'msg' => '请登录');
			Helper::response($data);
        }
		
		$where = "p2.userid = {$this->userid} AND p1.status>0";
		
		$page = $this->get('page');
		$page = is_numeric($page) ? $page : 1;
		
		$limit = $this->get('limit');
		$limit = is_numeric($limit) ? $limit : 10;
		
		$sql ="SELECT count(*) AS total FROM `t_order` as p1 left join `t_products` as p2 on p1.pid=p2.id WHERE {$where}";
		$total_result = $this->m_order->Query($sql);
		if(is_array($total_result) AND !empty($total_result)){
			$total = $total_result[0]['total'];
		}else{
			$total = 0;
		}
		
        if ($total > 0) {
            if ($page > 0 && $page < (ceil($total / $limit) + 1)) {
                $pagenum = ($page - 1) * $limit;
            } else {
                $pagenum = 0;
            }
			
            $limits = "{$pagenum},{$limit}";
			
			$sql = "SELECT p1.*,p2.hashid,p2.name AS productname FROM `t_order` as p1 left join `t_products` as p2 on p1.pid=p2.id WHERE {$where} Order by p1.id desc LIMIT {$limits}";
			$items=$this->m_order->Query($sql);
			
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
}