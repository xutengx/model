<?php

declare(strict_types = 1);
namespace Xutengx\Model\QueryBuilder;

use RuntimeException;
use Xutengx\Model\Component\QueryChunk;

/**
 * Trait Execute 执行
 * @package Xutengx\Model\QueryBuilder
 */
trait Execute {

	/**
	 * 查询一行
	 * @return array
	 */
	public function getRow(): array {
		$this->sqlType = 'select';
		$this->limitTake(1);
		$sql = $this->toSql($this->bindings);
		return $this->db->getRow($sql, $this->bindings);
	}

	/**
	 * 查询多行
	 * 以下写法无法使用自定义索引
	 * $this->sqlType = 'select';
	 * $sql = $this->toSql($pars);
	 * return $this->db->getAll($sql, $pars);
	 * 以上写法无法使用自定义索引
	 * @return array
	 */
	public function getAll(): array {
		$QueryChunk = $this->getChunk();
		$data       = [];
		foreach ($QueryChunk as $k => $v) {
			$data[$k] = $v;
		}
		return $data;
	}

	/**
	 * 块状获取
	 * @return QueryChunk
	 */
	public function getChunk(): QueryChunk {
		$this->sqlType = 'select';
		$sql           = $this->toSql($this->bindings);
		$PDOStatement  = $this->db->getChunk($sql, $this->bindings);
		return new QueryChunk($PDOStatement, $this->index);
	}

	/**
	 * 更新数据, 返回受影响的行数
	 * @return int
	 */
	public function update(): int {
		$this->sqlType = 'update';
		if (empty($this->data))
			throw new RuntimeException('For UPDATE operation, you need to set the updated value using the method[data].');
		$sql = $this->toSql($this->bindings);
		return $this->db->update($sql, $this->bindings);
	}

	/**
	 * 插入数据, 返回插入的键
	 * @return int
	 */
	public function insertGetId(): int {
		if(!$this->model->hasPrimaryKey())
			throw new RuntimeException('The method[InsertGetId] can not be properly executed without primaryKey[AUTO_INCREMENT].');
		$this->sqlType = 'insert';
		$sql = $this->toSql($this->bindings);
		return $this->db->insertGetId($sql, $this->bindings);
	}

	/**
	 * 插入数据
	 * @return int
	 */
	public function insert(): int {
		$this->sqlType = 'insert';
		$sql = $this->toSql($this->bindings);
		return $this->db->insert($sql, $this->bindings);
	}

	/**
	 * 删除数据, 返回受影响的行数
	 * @return int
	 */
	public function delete(): int {
		$this->sqlType = 'delete';
		if (empty($this->where))
			throw new RuntimeException('For DELETE operations. Without corresponding where constraints, `where(1)` will be enforced.');
		$sql = $this->toSql($this->bindings);
		return $this->db->update($sql, $this->bindings);
	}

	/**
	 * 插入or更新数据, 返回受影响的行数
	 * @return int
	 */
	public function replace(): int {
		$this->sqlType = 'replace';
		if (is_null($this->data))
			throw new RuntimeException('For REPLACE operation, you need to set the value of the new or modification using the method[data].');
		$sql = $this->toSql($this->bindings);
		return $this->db->update($sql, $this->bindings);
	}

}
