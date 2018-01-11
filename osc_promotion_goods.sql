/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : oscshop2

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2018-01-11 22:06:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `osc_promotion_goods`
-- ----------------------------
DROP TABLE IF EXISTS `osc_promotion_goods`;
CREATE TABLE `osc_promotion_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotion_id` int(11) NOT NULL DEFAULT '0',
  `goods_id` int(11) NOT NULL DEFAULT '0',
  `goods_option_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COMMENT='促销商品';

-- ----------------------------
-- Records of osc_promotion_goods
-- ----------------------------
INSERT INTO `osc_promotion_goods` VALUES ('30', '8', '20', '0');
INSERT INTO `osc_promotion_goods` VALUES ('31', '8', '19', '0');
INSERT INTO `osc_promotion_goods` VALUES ('32', '8', '30', '8');
INSERT INTO `osc_promotion_goods` VALUES ('35', '2', '30', '8');
INSERT INTO `osc_promotion_goods` VALUES ('36', '2', '21', '0');
INSERT INTO `osc_promotion_goods` VALUES ('37', '4', '32', '0');
INSERT INTO `osc_promotion_goods` VALUES ('38', '4', '30', '9');
INSERT INTO `osc_promotion_goods` VALUES ('39', '4', '30', '8');
INSERT INTO `osc_promotion_goods` VALUES ('40', '4', '21', '0');
INSERT INTO `osc_promotion_goods` VALUES ('41', '4', '20', '0');
INSERT INTO `osc_promotion_goods` VALUES ('42', '4', '19', '0');
INSERT INTO `osc_promotion_goods` VALUES ('43', '4', '17', '0');
INSERT INTO `osc_promotion_goods` VALUES ('44', '4', '15', '7');
INSERT INTO `osc_promotion_goods` VALUES ('45', '4', '15', '6');
INSERT INTO `osc_promotion_goods` VALUES ('46', '4', '15', '5');
INSERT INTO `osc_promotion_goods` VALUES ('52', '9', '30', '9');
INSERT INTO `osc_promotion_goods` VALUES ('53', '9', '17', '0');
INSERT INTO `osc_promotion_goods` VALUES ('54', '9', '15', '6');
