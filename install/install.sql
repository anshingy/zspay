-- phpMyAdmin SQL Dump
-- version 4.8.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2018-09-23 12:57:03
-- 服务器版本： 5.5.60-log
-- PHP Version: 7.2.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pay`
--

-- --------------------------------------------------------

--
-- 表的结构 `t_admin_login_log`
--

CREATE TABLE `t_admin_login_log` (
  `id` int(11) NOT NULL,
  `adminid` int(11) NOT NULL COMMENT '管理员id',
  `ip` varchar(15) NOT NULL DEFAULT '' COMMENT '登录ip',
  `addtime` int(11) NOT NULL COMMENT '登录时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员登录日志';


-- --------------------------------------------------------

--
-- 表的结构 `t_admin_user`
--

CREATE TABLE `t_admin_user` (
  `id` int(11) NOT NULL,
  `email` varchar(55) NOT NULL,
  `password` varchar(255) NOT NULL,
  `secret` varchar(55) NOT NULL,
  `updatetime` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `t_admin_user`
--

INSERT INTO `t_admin_user` (`id`, `email`, `password`, `secret`, `updatetime`) VALUES
(1, '43036456@qq.com', '395e7618c964f60bcbb21afa65fe28f2', 'b830d8', 0);

-- --------------------------------------------------------

--
-- 表的结构 `t_config`
--

CREATE TABLE `t_config` (
  `id` int(11) NOT NULL,
  `catid` int(11) NOT NULL COMMENT '分类ID',
  `name` varchar(32) NOT NULL COMMENT '配置名',
  `value` text COMMENT '配置内容',
  `tag` text NOT NULL COMMENT '备注',
  `lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '锁',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '最后修改时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='基础配置';

--
-- 转存表中的数据 `t_config`
--

INSERT INTO `t_config` (`id`, `catid`, `name`, `value`, `tag`, `lock`, `updatetime`) VALUES
(1, 1, 'web_url', 'https://zspay.zlkb.net', '当前站点地址,用于支付站点异步返回，务必修改正确', 1, 1453452674),
(2, 1, 'admin_email', '43036456@qq.com', '管理员邮箱，用于接收邮件提醒用。', 1, 1453452674),
(3, 1, 'web_name', '知识付费平台', '当前站点名称', 1, 1453452674),
(4, 1, 'web_description', '正在由资料空白开发', '当前站点描述', 1, 1453452674);

-- --------------------------------------------------------

--
-- 表的结构 `t_config_cat`
--

CREATE TABLE `t_config_cat` (
  `id` int(11) NOT NULL,
  `catname` varchar(32) NOT NULL COMMENT '配置分类名',
  `key` varchar(32) NOT NULL COMMENT '配置分类KEY'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='基础配置分类';

--
-- 转存表中的数据 `t_config_cat`
--

INSERT INTO `t_config_cat` (`id`, `catname`, `key`) VALUES
(1, '基础设置', 'basic'),
(2, '其他设置', 'other');

-- --------------------------------------------------------

--
-- 表的结构 `t_email`
--

CREATE TABLE `t_email` (
  `id` int(11) NOT NULL,
  `mailaddress` varchar(55) NOT NULL COMMENT '邮箱地址',
  `mailpassword` varchar(255) NOT NULL COMMENT '邮箱密码',
  `sendmail` varchar(55) NOT NULL COMMENT '	发件人emal	',
  `sendname` varchar(55) NOT NULL COMMENT '发送人昵称',
  `port` varchar(55) NOT NULL COMMENT '端口号',
  `host` varchar(55) NOT NULL COMMENT '发送邮件服务端'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `t_email`
--

-- --------------------------------------------------------

--
-- 表的结构 `t_email_code`
--

CREATE TABLE `t_email_code` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT '操作类型',
  `userid` int(11) NOT NULL COMMENT '用户id',
  `email` varchar(50) NOT NULL COMMENT '邮箱',
  `code` varchar(50) NOT NULL COMMENT '内容',
  `ip` varchar(50) NOT NULL COMMENT 'IP',
  `result` varchar(255) NOT NULL COMMENT '结果',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '结果0未发送 1已发送',
  `checkedStatus` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未校验，1已校验'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `t_email_queue`
--

CREATE TABLE `t_email_queue` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL COMMENT ' 收件人',
  `subject` varchar(55) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间',
  `sendtime` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间',
  `sendresult` text NOT NULL COMMENT '发送错误',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0,未发送 ，1已发送，-1,失败',
  `isdelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未删除,1已删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `t_help`
--


--
-- 表的结构 `t_order`
--

CREATE TABLE `t_order` (
  `id` int(11) NOT NULL,
  `orderid` varchar(55) NOT NULL COMMENT '订单号',
  `description` varchar(255) NOT NULL COMMENT '订单描述',
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `email` varchar(55) NOT NULL COMMENT '邮箱',
  `pid` int(11) NOT NULL COMMENT '产品id',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `ip` varchar(55) NOT NULL COMMENT 'ip',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态0下单',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '下单时间',
  `paytime` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `tradeid` varchar(255) NOT NULL COMMENT '外部订单id',
  `paymethod` varchar(255) NOT NULL COMMENT '支付渠道',
  `paymoney` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '支付总金额',
  `kami` text NOT NULL COMMENT '卡密',
  `isdelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未删除,1已删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- 表的结构 `t_payment`
--

CREATE TABLE `t_payment` (
  `id` int(11) NOT NULL,
  `payment` varchar(55) NOT NULL COMMENT '支付名',
  `payname` varchar(55) NOT NULL COMMENT '显示名称',
  `payimage` varchar(200) NOT NULL COMMENT '图片',
  `alias` varchar(55) NOT NULL COMMENT '别名',
  `sign_type` enum('RSA','RSA2','MD5') NOT NULL DEFAULT 'RSA2',
  `app_id` varchar(55) NOT NULL,
  `app_secret` varchar(100) NOT NULL,
  `ali_public_key` text NOT NULL,
  `rsa_private_key` text NOT NULL,
  `overtime` int(11) NOT NULL DEFAULT '0' COMMENT '支付超时,0是不限制',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未激活,1已激活',
  `configure3` text NOT NULL COMMENT '配置3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `t_payment`
--

INSERT INTO `t_payment` (`id`, `payment`, `payname`, `payimage`, `alias`, `sign_type`, `app_id`, `app_secret`, `ali_public_key`, `rsa_private_key`, `overtime`, `active`, `configure3`) VALUES
(1, '支付宝当面付', '支付宝', '/res/images/pay/alipay.jpg', 'zfbf2f', 'RSA2', '', '', '', '', 0, 1, ''),
(2, '有赞接口', '有赞', '', 'yzpay', 'RSA2', '', '', '', '', 0, 1, '');

-- --------------------------------------------------------

--
-- 表的结构 `t_products`
--

CREATE TABLE `t_products` (
  `id` int(11) NOT NULL,
  `typeid` int(11) NOT NULL COMMENT '类型id',
  `hashid` varchar(60) NOT NULL COMMENT '商品标识',
  `userid` int(11) NOT NULL COMMENT '用户id',
  `contact` varchar(60) NOT NULL COMMENT '联系人邮箱',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未激活 1激活',
  `name` varchar(55) NOT NULL COMMENT '产品名',
  `description` text NOT NULL COMMENT '描述',
  `isfaka` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0普通,1发卡',
  `qty` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `sellnum` int(11) NOT NULL DEFAULT '0' COMMENT '销售数量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `url` text NOT NULL COMMENT '授权显示地址',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `requireinput` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0不要求 1要求 ',
  `isdelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未删除,1已删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `t_products`
--

INSERT INTO `t_products` (`id`, `typeid`, `hashid`, `userid`, `contact`, `active`, `name`, `description`, `isfaka`, `qty`, `sellnum`, `price`, `url`, `addtime`, `requireinput`, `isdelete`) VALUES
(1, 1, 'u8', 1, '43036456@qq.com', 1, '测试商品', '测试商品，本系统正式上线前的第一条信息，送给有缘人!', 0, 0, 34, '0.10', '', 1536649513, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `t_products_card`
--

CREATE TABLE `t_products_card` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `card` text NOT NULL COMMENT '卡密',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0可用 1已使用',
  `isdelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未删除,1已删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `t_products_card`
--

INSERT INTO `t_products_card` (`id`, `pid`, `card`, `addtime`, `active`, `isdelete`) VALUES
(1, 1, 'XD，感谢您的测试！', 1536312619, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `t_products_type`
--

CREATE TABLE `t_products_type` (
  `id` int(11) NOT NULL,
  `name` varchar(55) NOT NULL COMMENT '类型命名',
  `sort_num` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未激活,1已激活',
  `isdelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未删除,1已删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `t_products_type`
--

INSERT INTO `t_products_type` (`id`, `name`, `sort_num`, `active`, `isdelete`) VALUES
(1, '默认分类', 2, 1, 0);

-- --------------------------------------------------------


--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_admin_login_log`
--
ALTER TABLE `t_admin_login_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_admin_user`
--
ALTER TABLE `t_admin_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_config`
--
ALTER TABLE `t_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_config_cat`
--
ALTER TABLE `t_config_cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_email`
--
ALTER TABLE `t_email`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_email_code`
--
ALTER TABLE `t_email_code`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_email_queue`
--
ALTER TABLE `t_email_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_help`
--
--
-- Indexes for table `t_order`
--
ALTER TABLE `t_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_payment`
--
ALTER TABLE `t_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_products`
--
ALTER TABLE `t_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_products_card`
--
ALTER TABLE `t_products_card`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_products_type`
--
ALTER TABLE `t_products_type`
  ADD PRIMARY KEY (`id`);


--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `t_admin_login_log`
--
ALTER TABLE `t_admin_login_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- 使用表AUTO_INCREMENT `t_admin_user`
--
ALTER TABLE `t_admin_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `t_config`
--
ALTER TABLE `t_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `t_config_cat`
--
ALTER TABLE `t_config_cat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `t_email`
--
ALTER TABLE `t_email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- 使用表AUTO_INCREMENT `t_email_code`
--
ALTER TABLE `t_email_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `t_email_queue`
--
ALTER TABLE `t_email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `t_order`
--
ALTER TABLE `t_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- 使用表AUTO_INCREMENT `t_payment`
--
ALTER TABLE `t_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `t_products`
--
ALTER TABLE `t_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `t_products_card`
--
ALTER TABLE `t_products_card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `t_products_type`
--
ALTER TABLE `t_products_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
