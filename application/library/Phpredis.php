<?php

/*-------------------------------------------------
	* redis驱动类，需要安装phpredis扩展
	*
	* @athor	zlkb
	* @link	http://pecl.php.net/package/redis
	* @date	2017-08-23
	*-------------------------------------------------
*/

class Phpredis
{

    private $_redis = null;

    public function __construct()
    {
        if (class_exists('Redis')) {
            if ($this->_redis === null) {
                $config = Yaf\Application::app()->getConfig();
                $redis_host = $config['redis_host'] ? $config['redis_host'] : '127.0.0.1';
                $redis_port = $config['redis_port'] ? $config['redis_port'] : '6379';
                $redis_auth = isset($config['redis_auth'])?$config['redis_auth']:'';
                $redis = new Redis();
                $hanedel = $redis->connect($redis_host, $redis_port);
                if ($hanedel) {
                    if ($redis_auth) {
                        $redis->auth($redis_auth);
                    }
                    $this->_redis = $redis;
                } else {
                    throw new Exception("redis连接失败");
                }
            }
        } else {
            throw new Exception("redis服务未安装");
        }
    }

    /*
     * @魔术方法
     * @直接调用Redis扩展的方法
    */
    public function __call($method, $args){
        $result = $this->_redis->$method(...$args);
        return $result;
    }

    /*
     * 获取redis资源对象
     *
    */
    public function getHandel()
    {
        return $this->_redis;
    }
}