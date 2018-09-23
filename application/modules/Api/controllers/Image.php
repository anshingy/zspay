<?php
/*
 * 功能：会员首页
 * Author:资料空白
 * Date:20180604
 */
class ImageController extends PcBasicController
{

	public function init()
	{
        parent::init();
	}

	
	public function indexAction()
	{
        $url = $this->get('url',true);
		if($url){
			try{
				\PHPQRCode\QRcode::png($url);
				exit();
			} catch (\Exception $e) {
				header('HTTP/1.1 403 Forbidden');
				exit();
			}
		}else{
			header('HTTP/1.1 403 Forbidden');
			exit();
		}
	}
}