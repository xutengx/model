<?php

declare(strict_types = 1);
namespace Xutengx\Model\Connection\Traits;

trait Log {

	/**
	 * 普通sql记录
	 * @param string $sql
	 * @param array $bindings
	 * @param bool $manual
	 * @return bool
	 */
	protected function logInfo(string $sql, array $bindings = [], bool $manual = false): bool {
		return $this->log->dbInfo('', [
			'sql'            => $sql,
			'bindings'       => $bindings,
			'manual'         => $manual,
			'connection'     => $this->connection,
			'masterSlave'    => $this->masterSlave,
			'type'           => $this->type,
			'transaction'    => $this->transaction,
			'conn'           => static::class,
			'identification' => $this->identification
		]);
	}

	/**
	 * 异常sql记录
	 * @param string $msg
	 * @param string $sql
	 * @param array $bindings
	 * @param bool $manual
	 * @return bool
	 */
	protected function logError(string $msg, string $sql, array $bindings = [], bool $manual = false): bool {
		return $this->log->dbError($msg, [
			'sql'            => $sql,
			'bindings'       => $bindings,
			'manual'         => $manual,
			'connection'     => $this->connection,
			'masterSlave'    => $this->masterSlave,
			'type'           => $this->type,
			'transaction'    => $this->transaction,
			'conn'           => static::class,
			'identification' => $this->identification
		]);
	}
}