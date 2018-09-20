<?php

declare(strict_types = 1);
namespace Xutengx\Model\Connection\Traits;

trait Transaction {

	/**
	 * 开启事务
	 * @return bool
	 */
	public function begin(): bool {
		$this->transaction = true;
		$PDO               = $this->PDO();
		return $PDO->beginTransaction();
	}

	/**
	 * 提交事务
	 * @return bool
	 */
	public function commit(): bool {
		$PDO               = $this->PDO();
		$this->transaction = false;
		return $PDO->commit();
	}

	/**
	 * 是否在事务中
	 * @return bool
	 */
	public function inTransaction(): bool {
		return $this->transaction;
	}

	/**
	 * 回滚事务
	 * @return bool
	 */
	public function rollBack(): bool {
		$PDO               = $this->PDO();
		$this->transaction = false;
		return $PDO->rollBack();
	}

}