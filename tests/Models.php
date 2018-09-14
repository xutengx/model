<?php

class RelationshipStudentTeacher extends \Xutengx\Model\Model {
	public $createTable = <<<SQL
CREATE TABLE `relationship_student_teacher` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(1) unsigned NOT NULL DEFAULT 0 COMMENT '学生id',
  `teacher_id` int(1) unsigned NOT NULL DEFAULT 0 COMMENT '教师id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学生表';
SQL;
}

class Student extends RelationshipStudentTeacher {
	public $createTable = <<<SQL
CREATE TABLE `student` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `age` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别1男2女',
  `teacher_id` int(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '教师id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='学生表';
SQL;
}

class Teacher extends RelationshipStudentTeacher {
	public $createTable = <<<SQL
CREATE TABLE `teacher` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `age` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别1男2女',
  `subject` varchar(20) NOT NULL DEFAULT '' COMMENT '科目',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='教师表';
SQL;
}
class TestModel extends \Xutengx\Model\Model {
	public $createTable = <<<SQL
CREATE TABLE `test` (
  `id` varchar(12) NOT NULL DEFAULT 'no_id' ,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `age` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '年龄',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别1男2女',
  `subject` varchar(20) NOT NULL DEFAULT '' COMMENT '科目',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='test';
SQL;
}

