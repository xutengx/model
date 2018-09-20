<?php

declare(strict_types = 1);
namespace Xutengx\Model\Connection;

use PDO;

/**
 * Class Connection 建议http环境下使用
 * @package Xutengx\Model\Connection
 */
class Connection extends AbstractConnection {

	/**
	 * 当前数据库 读 连接
	 * @var
	 */
	protected $dbReadSingle;
	/**
	 * 当前数据库 写 连接
	 * @var
	 */
	protected $dbWriteSingle;

	/**
	 * 由操作类型(读/写), 返回已存在的PDO实现
	 * @return PDO
	 */
	protected function PDO(): PDO {
		// 查询操作且不属于事务,使用读连接
		if ($this->type === 'select' && !$this->transaction && $this->masterSlave) {
			if (is_object($this->dbReadSingle) || ($this->dbReadSingle = $this->connect()))
				return $this->dbReadSingle;
		}
		// 写连接
		elseif (is_object($this->dbWriteSingle) || ($this->dbWriteSingle = $this->connect()))
			return $this->dbWriteSingle;

	}
}
