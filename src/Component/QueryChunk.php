<?php

declare(strict_types = 1);
namespace Xutengx\Model\Component;

use Closure;
use Iterator;
use PDOStatement;

/**
 * 块状数据对象
 */
class QueryChunk implements Iterator {

	protected $PDOStatement;
	/**
	 * 当前位置是否有效
	 * @var bool
	 */
	protected $isValid = true;
	/**
	 * 当前元素的键的来源
	 * @var string|Closure
	 */
	protected $index = null;
	/**
	 * 当前元素的键
	 * @var string|int
	 */
	protected $key = null;
	/**
	 * 当前元素的值
	 * @var array
	 */
	protected $value = [];

	/**
	 * QueryChunk constructor.
	 * @param PDOStatement $PDOStatement
	 * @param string|Closure $index 字段名or闭包算法
	 */
	public function __construct(PDOStatement $PDOStatement, $index = null) {
		$this->PDOStatement = $PDOStatement;
		$this->index        = $index;
	}

	/**
	 * 直接操作 PDOStatement
	 * 手动关闭 PDOStatement::closeCursor();
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call(string $method, array $parameters = []) {
		return $this->PDOStatement->$method(...$parameters);
	}

	/*********************** 以下 Iterator 实现 ************************/

	public function rewind(): void {
		$this->key     = null;
		$this->isValid = true;
		$this->fetchData();
	}

	public function current(): array {
		return $this->value;
	}

	public function key() {
		return $this->key;
	}

	public function next(): void {
		$this->fetchData();
	}

	public function valid(): bool {
		if (!$this->isValid) {
			$this->PDOStatement->closeCursor();
		}
		return $this->isValid;
	}

	/**
	 * 获取结果集value, 键key自增, 判断is_valid
	 * @return void
	 */
	protected function fetchData(): void {
		$value = $this->PDOStatement->fetch(\PDO::FETCH_ASSOC);
		if ($value === false) {
			$this->isValid = false;
		}
		else {
			if (is_null($this->index))
				$this->key = is_null($this->key) ? 0 : ++$this->key;
			elseif ($this->index instanceof Closure) {
				$this->key = call_user_func($this->index, $value);
			}
			else {
				$this->key = $value[$this->index];
			}
			$this->value = $value;
		}
	}

}
