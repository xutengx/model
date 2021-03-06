<?php

declare(strict_types = 1);
namespace Xutengx\Model\Connection;

use PDO;

/**
 * Class PersistentConnection
 * @package Xutengx\Model\Connection
 */
class PersistentConnection extends AbstractConnection {

	/**
	 * 由操作类型(读/写), 返回已存在的PDO实现
	 * @return PDO
	 */
	protected function PDO(): PDO {
		return $this->connect();
	}
}
