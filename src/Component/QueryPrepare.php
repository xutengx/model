<?php

declare(strict_types = 1);
namespace Xutengx\Model\Component;

use PDOStatement;
use Xutengx\Model\Connection\AbstractConnection;

/**
 * Class QueryPrepare 参数绑定, 重复调用
 * @package Xutengx\Model\Component
 */
class QueryPrepare {

	/**
	 * @var PDOStatement
	 */
	protected $PDOStatement;
	/**
	 * @var AbstractConnection
	 */
	protected $db;
	/**
	 * @var array
	 */
	protected $bindings = [];

	/**
	 * QueryPrepare constructor.
	 * @param PDOStatement $PDOStatement
	 * @param AbstractConnection $db
	 * @param array $bindings 自动绑定参数数组
	 */
	public function __construct(PDOStatement $PDOStatement, AbstractConnection $db, array $bindings = []) {
		$this->PDOStatement = $PDOStatement;
		$this->db           = $db;
		$this->bindings     = $bindings;
	}

	/**
	 * 查询一行
	 * @param array $bindings 手动绑定参数数组
	 * @return array
	 */
	public function getRow(array $bindings = []): array {
		$this->execute($bindings);
		$re = $this->PDOStatement->fetch(\PDO::FETCH_ASSOC) ?? [];
		return $re ? $re : [];
	}

	/**
	 * 查询多行
	 * @param array $bindings 手动绑定参数数组
	 * @return array
	 */
	public function getAll(array $bindings = []): array {
		$this->execute($bindings);
		return $this->PDOStatement->fetchall(\PDO::FETCH_ASSOC) ?? [];
	}

	/**
	 * 插入
	 * @param array $bindings 手动绑定参数数组
	 * @return int 影响的行数
	 */
	public function insert(array $bindings = []): int {
		$this->execute($bindings);
		return $this->PDOStatement->rowCount();
	}

	/**
	 * 删除
	 * @param array $bindings 手动绑定参数数组
	 * @return int 影响的行数
	 */
	public function delete(array $bindings = []): int {
		return $this->update($bindings);
	}

	/**
	 * 更新
	 * @param array $bindings 手动绑定参数数组
	 * @return int 影响的行数
	 */
	public function update(array $bindings = []): int {
		$this->execute($bindings);
		return $this->PDOStatement->rowCount();
	}

	/**
	 * 插入or更新
	 * @param array $bindings 手动绑定参数数组
	 * @return int 影响的行数
	 */
	public function replace(array $bindings = []): int {
		return $this->update($bindings);
	}

	/**
	 * 执行 PDOStatement
	 * @param array $bindings 手动绑定参数数组
	 * @return void
	 */
	protected function execute(array $bindings = []): void {
		// 本次执行的`绑定参数数组`
		$realBindings = [];
		// 合并`手动绑定参数数组`与`自动绑定参数数组`
		// 还原`自动绑定参数数组`中的`手动键`
		foreach ($this->bindings as $k => $v) {
			$realBindings[$k] = $bindings[$v] ?? $v;
		}
		// 在 AbstractConnection 中执行, 以便统一管理
		$this->db->execute($this->PDOStatement, $realBindings);
	}

}
