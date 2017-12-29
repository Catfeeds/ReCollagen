/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : oscshop2

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2017-12-29 14:01:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `osc_category`
-- ----------------------------
DROP TABLE IF EXISTS `osc_category`;
CREATE TABLE `osc_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `image` varchar(100) NOT NULL DEFAULT '',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `update_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COMMENT='商品分类';

-- ----------------------------
-- Records of osc_category
-- ----------------------------
INSERT INTO `osc_category` VALUES ('1', '0', '五谷杂粮', 'images/osc1/category/category-rice.png', '2', '1513157092');
INSERT INTO `osc_category` VALUES ('2', '0', '正宗好茶', 'images/osc1/category/category-tea.png', '5', '1513157076');
INSERT INTO `osc_category` VALUES ('3', '0', '美味零食', 'images/osc1/category/category-dryfruit.png', '1', '1513568720');
INSERT INTO `osc_category` VALUES ('5', '0', '时令蔬果', 'images/osc1/category/category-vg.png', '6', '1513157636');
INSERT INTO `osc_category` VALUES ('13', '0', '精美茶具', 'images/osc1/category/category-fry-a.png', '3', '1513219382');
INSERT INTO `osc_category` VALUES ('29', '3', '美味零食一', 'images/osc1/category/category-cake.png', '4', '1513157182');
INSERT INTO `osc_category` VALUES ('35', '13', '精美茶具一', 'images/osc1/category/category-tea.png', '0', '1514524084');
INSERT INTO `osc_category` VALUES ('36', '3', '美味零食二', 'images/osc1/category/banner-3a.png', '0', '1514518548');
