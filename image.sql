/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : recollagen

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2017-12-08 11:14:41
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `image`
-- ----------------------------
DROP TABLE IF EXISTS `image`;
CREATE TABLE `image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL COMMENT '图片路径',
  `from` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 来自本地，2 来自公网',
  `delete_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COMMENT='图片总表';

-- ----------------------------
-- Records of image
-- ----------------------------
INSERT INTO `image` VALUES ('1', 'http://www.wcmall.com/images/banner-1a.png', '1', null, null);
INSERT INTO `image` VALUES ('2', 'http://www.wcmall.com/images/banner-2a.png', '1', null, null);
INSERT INTO `image` VALUES ('3', 'http://www.wcmall.com/images/banner-3a.png', '1', null, null);
INSERT INTO `image` VALUES ('4', 'http://www.wcmall.com/images/category-cake.png', '1', null, null);
INSERT INTO `image` VALUES ('5', 'http://www.wcmall.com/images/category-vg.png', '1', null, null);
INSERT INTO `image` VALUES ('6', 'http://www.wcmall.com/images/category-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('7', 'http://www.wcmall.com/images/category-fry-a.png', '1', null, null);
INSERT INTO `image` VALUES ('8', 'http://www.wcmall.com/images/category-tea.png', '1', null, null);
INSERT INTO `image` VALUES ('9', 'http://www.wcmall.com/images/category-rice.png', '1', null, null);
INSERT INTO `image` VALUES ('10', 'http://www.wcmall.com/images/product-dryfruit@1.png', '1', null, null);
INSERT INTO `image` VALUES ('13', 'http://www.wcmall.com/images/product-vg@1.png', '1', null, null);
INSERT INTO `image` VALUES ('14', 'http://www.wcmall.com/images/product-rice@6.png', '1', null, null);
INSERT INTO `image` VALUES ('16', 'http://www.wcmall.com/images/1@theme.png', '1', null, null);
INSERT INTO `image` VALUES ('17', 'http://www.wcmall.com/images/2@theme.png', '1', null, null);
INSERT INTO `image` VALUES ('18', 'http://www.wcmall.com/images/3@theme.png', '1', null, null);
INSERT INTO `image` VALUES ('19', 'http://www.wcmall.com/images/detail-1@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('20', 'http://www.wcmall.com/images/detail-2@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('21', 'http://www.wcmall.com/images/detail-3@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('22', 'http://www.wcmall.com/images/detail-4@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('23', 'http://www.wcmall.com/images/detail-5@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('24', 'http://www.wcmall.com/images/detail-6@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('25', 'http://www.wcmall.com/images/detail-7@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('26', 'http://www.wcmall.com/images/detail-8@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('27', 'http://www.wcmall.com/images/detail-9@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('28', 'http://www.wcmall.com/images/detail-11@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('29', 'http://www.wcmall.com/images/detail-10@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('31', 'http://www.wcmall.com/images/product-rice@1.png', '1', null, null);
INSERT INTO `image` VALUES ('32', 'http://www.wcmall.com/images/product-tea@1.png', '1', null, null);
INSERT INTO `image` VALUES ('33', 'http://www.wcmall.com/images/product-dryfruit@2.png', '1', null, null);
INSERT INTO `image` VALUES ('36', 'http://www.wcmall.com/images/product-dryfruit@3.png', '1', null, null);
INSERT INTO `image` VALUES ('37', 'http://www.wcmall.com/images/product-dryfruit@4.png', '1', null, null);
INSERT INTO `image` VALUES ('38', 'http://www.wcmall.com/images/product-dryfruit@5.png', '1', null, null);
INSERT INTO `image` VALUES ('39', 'http://www.wcmall.com/images/product-dryfruit-a@6.png', '1', null, null);
INSERT INTO `image` VALUES ('40', 'http://www.wcmall.com/images/product-dryfruit@7.png', '1', null, null);
INSERT INTO `image` VALUES ('41', 'http://www.wcmall.com/images/product-rice@2.png', '1', null, null);
INSERT INTO `image` VALUES ('42', 'http://www.wcmall.com/images/product-rice@3.png', '1', null, null);
INSERT INTO `image` VALUES ('43', 'http://www.wcmall.com/images/product-rice@4.png', '1', null, null);
INSERT INTO `image` VALUES ('44', 'http://www.wcmall.com/images/product-fry@1.png', '1', null, null);
INSERT INTO `image` VALUES ('45', 'http://www.wcmall.com/images/product-fry@2.png', '1', null, null);
INSERT INTO `image` VALUES ('46', 'http://www.wcmall.com/images/product-fry@3.png', '1', null, null);
INSERT INTO `image` VALUES ('47', 'http://www.wcmall.com/images/product-tea@2.png', '1', null, null);
INSERT INTO `image` VALUES ('48', 'http://www.wcmall.com/images/product-tea@3.png', '1', null, null);
INSERT INTO `image` VALUES ('49', 'http://www.wcmall.com/images/1@theme-head.png', '1', null, null);
INSERT INTO `image` VALUES ('50', 'http://www.wcmall.com/images/2@theme-head.png', '1', null, null);
INSERT INTO `image` VALUES ('51', 'http://www.wcmall.com/images/3@theme-head.png', '1', null, null);
INSERT INTO `image` VALUES ('52', 'http://www.wcmall.com/images/product-cake@1.png', '1', null, null);
INSERT INTO `image` VALUES ('53', 'http://www.wcmall.com/images/product-cake@2.png', '1', null, null);
INSERT INTO `image` VALUES ('54', 'http://www.wcmall.com/images/product-cake-a@3.png', '1', null, null);
INSERT INTO `image` VALUES ('55', 'http://www.wcmall.com/images/product-cake-a@4.png', '1', null, null);
INSERT INTO `image` VALUES ('56', 'http://www.wcmall.com/images/product-dryfruit@8.png', '1', null, null);
INSERT INTO `image` VALUES ('57', 'http://www.wcmall.com/images/product-fry@4.png', '1', null, null);
INSERT INTO `image` VALUES ('58', 'http://www.wcmall.com/images/product-fry@5.png', '1', null, null);
INSERT INTO `image` VALUES ('59', 'http://www.wcmall.com/images/product-rice@5.png', '1', null, null);
INSERT INTO `image` VALUES ('60', 'http://www.wcmall.com/images/product-rice@7.png', '1', null, null);
INSERT INTO `image` VALUES ('62', 'http://www.wcmall.com/images/detail-12@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('63', 'http://www.wcmall.com/images/detail-13@1-dryfruit.png', '1', null, null);
INSERT INTO `image` VALUES ('65', 'http://www.wcmall.com/images/banner-4a.png', '1', null, null);
INSERT INTO `image` VALUES ('66', 'http://www.wcmall.com/images/product-vg@4.png', '1', null, null);
INSERT INTO `image` VALUES ('67', 'http://www.wcmall.com/images/product-vg@5.png', '1', null, null);
INSERT INTO `image` VALUES ('68', 'http://www.wcmall.com/images/product-vg@2.png', '1', null, null);
INSERT INTO `image` VALUES ('69', 'http://www.wcmall.com/images/product-vg@3.png', '1', null, null);
