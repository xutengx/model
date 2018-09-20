<?php

declare(strict_types = 1);
namespace Xutengx\Model\Connection\Traits;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

trait Execute {

	/**
	 * 执行
	 * @param PDOStatement $PDOStatement
	 * @param array $bindings
	 * @return void
	 * @throws PDOException
	 */
	public function execute(PDOStatement $PDOStatement, array $bindings): void {
		$sql = $PDOStatement->queryString;
		try {
			// 执行一条预处理语句
			$PDOStatement->execute($bindings);
			// 普通 sql 记录
			$this->logInfo($sql, $bindings, true);
		} catch (PDOException $pdoException) {
			// 错误 sql 记录
			$this->logError($pdoException->getMessage(), $sql, $bindings, true);
			// 异常抛出
			throw $pdoException;
		}
	}

	/**
	 * 返回PDOStatement, 可做分块解析
	 * @param string $sql
	 * @param array $pars
	 * @return PDOStatement
	 */
	public function getChunk(string $sql, array $pars = []): PDOStatement {
		$this->type = 'select';
		return $this->prepareExecute($sql, $pars);
	}

	/**
	 * 查询一行
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return array 一维数组
	 */
	public function getRow(string $sql, array $pars = []): array {
		$this->type = 'select';
		$re         = $this->prepareExecute($sql, $pars)->fetch(PDO::FETCH_ASSOC);
		return $re ? $re : [];
	}

	/**
	 * 查询多行
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return array 二维数组
	 */
	public function getAll(string $sql, array $pars = []): array {
		$this->type = 'select';
		return $this->prepareExecute($sql, $pars)->fetchall(PDO::FETCH_ASSOC);
	}

	/**
	 * 更新数据, 返回受影响的行数
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return int 受影响的行数
	 */
	public function update(string $sql, array $pars = []): int {
		$this->type = 'update';
		return $this->prepareExecute($sql, $pars)->rowCount();
	}

	/**
	 * 插入数据, 返回插入的主键
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return string 插入的主键
	 */
	public function insertGetId(string $sql, array $pars = []): string {
		$this->type = 'insert';
		$res        = $this->prepareExecute($sql, $pars, true, $pdo)->rowCount();
		// mysql_insert_id函数返回的是储存在有AUTO_INCREMENT约束的字段的值.
		// 如果表中的字段不使用AUTO_INCREMENT约束，那么该函数不会返回你所存储的值，而是返回NULL或0
		if ($res)
			return $pdo->lastInsertId();
		throw new PDOException('Insert failed.');
	}

	/**
	 * 插入数据
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return int 受影响的行数
	 */
	public function insert(string $sql, array $pars = []): int {
		$this->type = 'insert';
		return $this->prepareExecute($sql, $pars)->rowCount();
	}

	/**
	 * 使用PDO->prepare(), 返回的对象可用$res->execute($pars)重复调用
	 * @param string $sql
	 * @param string $type
	 * @return PDOStatement
	 */
	public function prepare(string $sql, string $type = 'update'): PDOStatement {
		if (!in_array($type, ['select', 'update', 'delete', 'insert', 'replace']))
			throw new InvalidArgumentException("The type mast in_array(select update delete insert replace), but [$type] given.");
		$this->type = $type;
		return $this->prepareExecute($sql, [], false);
	}

	/**
	 * 内部执行, 返回原始数据对象, 触发异常处理
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @param bool $auto 自动执行绑定
	 * @param PDO $PDO 用作`insertGetId`的return
	 * @return PDOStatement
	 */
	protected function prepareExecute(string $sql, array $pars = [], bool $auto = true, &$PDO = null): PDOStatement {
		try {
			// 链接数据库
			$PDO = $this->PDO();
			// 备要执行的SQL语句并返回一个 PDOStatement 对象
			$PDOStatement = $PDO->prepare($sql);
			if ($auto)
				// 执行一条预处理语句
				$PDOStatement->execute($pars);
			// 普通 sql 记录
			$this->logInfo($sql, $pars, !$auto);
			return $PDOStatement;
		} catch (PDOException $pdoException) {
			// 错误 sql 记录
			$this->logError($pdoException->getMessage(), $sql, $pars, !$auto);
			// 异常抛出
			throw $pdoException;
		}
	}
}