/*
Navicat MySQL Data Transfer

Source Server         : 192.168.188.128
Source Server Version : 50635
Source Host           : 192.168.188.128:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50635
File Encoding         : 65001

Date: 2018-09-20 11:37:26
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for test
-- ----------------------------
DROP TABLE IF EXISTS `test`;
CREATE TABLE `test` (
  `id` varchar(12) NOT NULL DEFAULT 'no_id',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `age` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别1男2女',
  `subject` varchar(20) NOT NULL DEFAULT '' COMMENT '科目',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='test';

-- ----------------------------
-- Records of test
-- ----------------------------
INSERT INTO `test` VALUES ('po213jwqb-1', '小明', '16', '2', '数学', '2009-03-14 17:15:23', '2010-04-24 22:11:03');
INSERT INTO `test` VALUES ('qwe-1', '谭明佳', '44', '1', '数学', '2009-03-14 17:15:23', '2010-04-22 07:11:03');
INSERT INTO `test` VALUES ('1-22', '小佳', '16', '2', '数学', '2009-03-14 17:15:23', '2010-04-24 21:11:03');
DROP TABLE IF EXISTS `relationship_student_teacher`;
CREATE TABLE `relationship_student_teacher` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '学生id',
  `teacher_id` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '教师id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='学生表';

-- ----------------------------
-- Records of relationship_student_teacher
-- ----------------------------
INSERT INTO `relationship_student_teacher` VALUES ('1', '1', '1', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('2', '1', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('3', '2', '1', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('4', '2', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('5', '3', '1', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('6', '3', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('7', '4', '6', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('8', '4', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('9', '5', '6', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('10', '5', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('11', '6', '6', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('12', '6', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('13', '7', '6', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('14', '7', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('15', '8', '6', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('16', '8', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('17', '9', '6', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('18', '9', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('19', '10', '8', '2009-03-14 22:15:23', '2009-04-24 22:22:03');
INSERT INTO `relationship_student_teacher` VALUES ('20', '10', '2', '2009-03-14 22:15:23', '2009-04-24 22:22:03');

-- ----------------------------
-- Table structure for teacher
-- ----------------------------
DROP TABLE IF EXISTS `teacher`;
CREATE TABLE `teacher` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `age` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别1男2女',
  `subject` varchar(20) NOT NULL DEFAULT '' COMMENT '科目',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='教师表';

-- ----------------------------
-- Records of teacher
-- ----------------------------
INSERT INTO `teacher` VALUES ('1', '张淑明', '22', '2', '会计', '2009-03-14 20:15:23', '2009-04-24 22:11:03');
INSERT INTO `teacher` VALUES ('2', '腾腾', '26', '1', '计算机', '2009-03-15 20:15:23', '2009-04-24 22:11:03');
INSERT INTO `teacher` VALUES ('6', '谭明佳', '22', '2', '会计', '2009-03-16 20:15:23', '2009-04-24 22:11:03');
INSERT INTO `teacher` VALUES ('8', '文松', '22', '2', '会计', '2009-03-17 20:15:23', '2009-04-24 22:11:03');

-- ----------------------------
-- Table structure for student
-- ----------------------------
DROP TABLE IF EXISTS `student`;
CREATE TABLE `student` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `age` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别1男2女',
  `teacher_id` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '教师id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='学生表';

-- ----------------------------
-- Records of student
-- ----------------------------
INSERT INTO `student` VALUES ('1', '小明', '6', '2', '0', '2009-03-14 17:15:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('2', '小张', '11', '2', '0', '2009-03-14 15:15:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('3', '小腾', '16', '1', '0', '2009-03-14 15:11:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('4', '小云', '11', '2', '0', '2009-03-14 15:15:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('5', '小卡卡', '11', '2', '0', '2009-03-14 17:15:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('6', '非卡', '16', '1', '0', '2009-03-14 17:15:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('7', '狄龙', '17', '1', '0', '2009-03-14 18:15:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('8', '金庸', '17', '1', '0', '2009-03-14 18:18:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('9', '莫西卡', '17', '1', '0', '2009-03-15 22:15:23', '2010-04-24 22:11:03');
INSERT INTO `student` VALUES ('10', '象帕', '15', '1', '0', '2009-03-15 12:15:23', '2010-04-24 22:11:03');