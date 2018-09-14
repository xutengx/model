<?php

declare(strict_types = 1);
namespace Xutengx\Model\Traits;

/**
 * Trait Debug
 * @package Xutengx\Model\Traits
 */
trait Debug {

	/**
	 * 记录最近次执行的sql
	 * @param string $sql
	 * @return string
	 */
	public function setLastSql(string $sql): string {
		return $this->lastSql = $sql;
	}

	/**
	 * 返回完整sql, 已执行sql
	 * @return string
	 */
	public function getLastSql(): string {
		return $this->lastSql;
	}

	/**
	 * @return string
	 */
	public function getTable(): string {
		return $this->table;
	}

	/**
	 * @return bool
	 */
	public function hasPrimaryKey(): bool {
		return !is_null($this->primaryKey);
	}

	/**
	 * @return string
	 */
	public function getPrimaryKey(): string {
		return $this->primaryKey;
	}

	/**
	 * @return string
	 */
	public function getFields(): string {
		return $this->fields;
	}

}
