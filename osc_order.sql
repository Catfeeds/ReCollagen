/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : oscshop2

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2017-12-22 17:30:02
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `osc_order`
-- ----------------------------
DROP TABLE IF EXISTS `osc_order`;
CREATE TABLE `osc_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_num_alias` varchar(40) NOT NULL DEFAULT '' COMMENT '订单编号',
  `order_status` tinyint(4) NOT NULL DEFAULT '1',
  `pay_subject_img` varchar(100) NOT NULL DEFAULT '' COMMENT '订单快照图片',
  `pay_subject` varchar(255) NOT NULL DEFAULT '' COMMENT '订单快照名称',
  `uid` int(11) NOT NULL DEFAULT '0',
  `shipping_name` varchar(32) NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `shipping_tel` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人电话',
  `shipping_addr` varchar(255) NOT NULL DEFAULT '' COMMENT '收货人地址',
  `dispatch_id` int(11) NOT NULL DEFAULT '0' COMMENT '发货仓id',
  `shipping_method` varchar(128) NOT NULL DEFAULT '' COMMENT '物流公司',
  `shipping_num` varchar(30) NOT NULL DEFAULT '' COMMENT '物流单号',
  `mainPay` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '主账户付款金额',
  `secondPay` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '辅账户付款金额',
  `mainGoodsPrice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `otherGoodsPrice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `shippingPrice` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `total` decimal(11,2) NOT NULL DEFAULT '0.00',
  `promotionName` varchar(60) NOT NULL DEFAULT '' COMMENT '促销活动名称',
  `promotionType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '促销活动类型',
  `expression` varchar(60) NOT NULL DEFAULT '' COMMENT '促销优惠体现',
  `pay_time` int(11) NOT NULL DEFAULT '0' COMMENT '付款时间',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of osc_order
-- ----------------------------
INSERT INTO `osc_order` VALUES ('3', 'wx2017120753575710', '2', 'images/osc1/5/1.jpg', '旅行套装便携功夫茶具等', '2', '哈哈哈22', '18121029523', '北京市国家图书馆111号', '1', '邮政快递', '9891770403677', '98.70', '10.00', '98.70', '0.00', '10.00', '108.70', '周年庆限时满300返现30', '2', '30', '0', '1513402181', '1513759963');
INSERT INTO `osc_order` VALUES ('4', 'wx2017121253555353', '4', 'images/osc1/product/2@theme.png', '青花白瓷手绘荷花', '2', '哈哈哈22', '18121029523', '江苏省南京市玄武区人民路XX小区1栋101', '3', '顺丰速递', '426530659301', '7235.00', '15.00', '7235.00', '0.00', '15.00', '7250.00', '', '0', '', '0', '1513411181', '1513479809');
