<?php

declare(strict_types = 1);
namespace Xutengx\Model\Traits;

/**
 * Trait Attribute
 * @package Xutengx\Model\Traits
 */
trait Attribute {

	/**
	 * model共性属性
	 * @var array
	 */
	protected static $modelScopes = [];

	/**
	 * 记录最近次执行的sql
	 * @param string $sql
	 * @return string
	 */
	public function setLastSql(string $sql): string {
		return static::$modelScopes['lastSql'] = $sql;
	}

	/**
	 * 返回完整sql, 已执行sql
	 * @return string
	 */
	public function getLastSql(): string {
		return static::$modelScopes['lastSql'];
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
	 * @return array
	 */
	public function getFields(): array {
		return $this->fields;
	}

}
