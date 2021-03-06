/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : bm_yumzeed_huds

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2015-10-22 17:06:45
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for groupon_grab
-- ----------------------------
DROP TABLE IF EXISTS `groupon_grab`;
CREATE TABLE `groupon_grab` (
  `bulk_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL COMMENT '抢购名称',
  `category_id` varchar(255) DEFAULT NULL COMMENT '分类 ID 多个以 , 分隔',
  `start_time` datetime DEFAULT NULL COMMENT '抢购开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '抢购结束时间',
  `inventory_sum` int(11) DEFAULT NULL COMMENT '抢购总库存',
  `inventory` int(11) DEFAULT NULL COMMENT '库存准量：每一次下单减去购买个数',
  `price` float(11,2) DEFAULT NULL COMMENT '抢购价格',
  `sku` varchar(255) DEFAULT NULL COMMENT '商品 sku',
  `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `integral` int(11) DEFAULT '0' COMMENT '可否使用积分，0代表不可使用积分',
  `buynumber` int(11) DEFAULT NULL COMMENT '抢购：一个用户只能购买N件商品并且只能提交一次成功订单，取消订单可以再次抢购',
  `ctime` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`bulk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='抢购表';
