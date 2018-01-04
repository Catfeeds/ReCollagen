/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : oscshop2

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2018-01-04 13:47:51
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `osc_promotion_info`
-- ----------------------------
DROP TABLE IF EXISTS `osc_promotion_info`;
CREATE TABLE `osc_promotion_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(60) NOT NULL DEFAULT '',
  `description` text,
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='促销信息';

-- ----------------------------
-- Records of osc_promotion_info
-- ----------------------------
INSERT INTO `osc_promotion_info` VALUES ('10', 'images/osc1/category/category-dryfruit.png', '&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;http://www.wcadmin.com/public/uploads/cache/images/ckeditor/20180104/wx2018010454100495-600x300.jpg&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;span style=&quot;color:rgba(0, 0, 0, 0.8); font-size:medium&quot;&gt;浙江温州，浙江温州，最大皮革厂，江南皮革厂倒闭了！忘八端老板黄鹤吃喝嫖赌，欠下了3.5个亿，带着他的小姨子跑了。我们没有没有办法，拿着钱包抵公司。原价都是三百多、二百多、一百多的钱包，通通二十块，通通二十块！黄鹤忘八端，你不是人，我们辛辛苦苦给你干了大半年，你不发工资，你还我血汗钱，还我血汗钱！&lt;/span&gt;&lt;/p&gt;\r\n', '1515043952');
