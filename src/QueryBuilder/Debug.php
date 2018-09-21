<?php

declare(strict_types = 1);
namespace Xutengx\Model\QueryBuilder;

use RuntimeException;

/**
 * Trait Debug
 * @package Xutengx\Model\QueryBuilder
 */
trait Debug {

	/**
	 * 返回此刻的参数绑定数组
	 * @return array
	 */
	public function getBindings(): array {
		return $this->bindings;
	}

	/**
	 * 查询多行 的sql 保留参数绑定的 key
	 * @return string
	 */
	public function getAllToSqlWithBindingsKey(): string {
		$pars          = $this->bindings;
		$this->sqlType = 'select';
		return $this->toSql($pars);
	}

	/**
	 * 查询一行 的sql
	 * @return string
	 */
	public function getRowToSql(): string {
		$this->sqlType = 'select';
		$this->limitTake(1);
		return $this->toCompleteSql($this->bindings);
	}

	/**
	 * 查询多行 的sql
	 * @return string
	 */
	public function getAllToSql(): string {
		$this->sqlType = 'select';
		return $this->toCompleteSql($this->bindings);
	}

	/**
	 * 更新数据, 返回受影响的行数 的sql
	 * @return string
	 */
	public function updateToSql(): string {
		$this->sqlType = 'update';
		if (empty($this->data))
			throw new RuntimeException('For UPDATE operation, you need to set the updated value using the method[data].');
		return $this->toCompleteSql($this->bindings);
	}

	/**
	 * 插入数据, 返回插入的主键 的sql
	 * @return string
	 */
	public function insertGetIdToSql(): string {
		$this->sqlType = 'insert';
		return $this->toCompleteSql($this->bindings);
	}

	/**
	 * 插入数据 的sql
	 * @return string
	 */
	public function insertToSql(): string {
		$this->sqlType = 'insert';
		return $this->toCompleteSql($this->bindings);
	}

	/**
	 * 删除数据, 返回受影响的行数 的sql
	 * @return string
	 */
	public function deleteToSql(): string {
		$this->sqlType = 'delete';
		if (empty($this->data))
			throw new RuntimeException('For DELETE operations. Without corresponding where constraints, `where(1)` will be enforced.');
		return $this->toCompleteSql($this->bindings);
	}

	/**
	 * 插入or更新数据, 返回受影响的行数 的sql
	 * @return string
	 */
	public function replaceToSql(): string {
		$this->sqlType = 'replace';
		return $this->toCompleteSql($this->bindings);
	}

	/**
	 * 自动识别语句类型 的sql
	 * @return string
	 */
	public function sql(): string {
		if (!is_null($this->data))
			$this->sqlType = 'update';
		else
			$this->sqlType = 'select';
		return $this->toCompleteSql($this->bindings);
	}

}
