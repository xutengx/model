<?php

use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;

abstract class GenericTestsDatabaseTestCase extends TestCase {
	use TestCaseTrait;

	// only instantiate pdo once for test clean-up/fixture load
	static private $pdo = null;

	// only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
	private $conn = null;

	/**
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet() {
		return $this->createFlatXMLDataSet(dirname(__FILE__) . '/ini/sql.xml');
	}

	public function getConnection() {
		if ($this->conn === null) {
			if (self::$pdo == null) {
				self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
			}
			$this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
		}

		return $this->conn;
	}
}