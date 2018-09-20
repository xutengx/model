<?php

use PHPUnit\Framework\TestCase;

abstract class GenericTestsDatabaseTestCase extends TestCase {

	protected $databases = [
		'model_test_master_0',
		'model_test_master_1',
		'model_test_slave_0',
		'model_test_slave_1',
		'model_test_slave_2',
		'model_test_slave_3'
	];

	/**
	 * 初始化数据库, 数据表
	 */
	protected function setUp() {
		foreach ($this->databases as $database) {
			$this->createDatabase($database);
			$this->createTable($database);
		}
	}

	protected function createDatabase($db) {
		$host = $GLOBALS['DB_HOST'];
		$port = $GLOBALS['DB_PORT'];
		$pdo  = new \PDO("mysql:host=$host;port=$port", $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
		$pdo->query("CREATE DATABASE $db");
		$pdo = null;
	}

	protected function createTable($db) {
		$host     = $GLOBALS['DB_HOST'];
		$port     = $GLOBALS['DB_PORT'];
		$pdo      = new \PDO("mysql:dbname=$db;host=$host;port=$port", $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
		$sqlFile  = dirname(__FILE__) . '/ini/test.sql';
		$sqlArray = explode(';', file_get_contents($sqlFile));
		foreach ($sqlArray as $sql) {
			$pdo->query($sql);
		}
		$pdo = null;
	}

}