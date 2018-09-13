<?php

declare(strict_types = 1);
namespace Xutengx\Model\QueryBuilder;

use Xutengx\Model\Component\QueryBuilder;

/**
 * Trait Column
 * @package Xutengx\Model\QueryBuilder
 */
trait Column {

	/**
	 * 加入一个不做处理的column
	 * @param string $sql
	 * @return QueryBuilder
	 */
	public function columnRaw(string $sql): QueryBuilder {
		return $this->columnPush($sql);
	}

	/**
	 * @param string $column
	 * @return QueryBuilder
	 */
	public function columnString(string $column): QueryBuilder {
		return $this->columnPush($this->fieldFormat($column));
	}

	/**
	 * 批量数组赋值
	 * @param array $arr 一位数组
	 * @return QueryBuilder
	 */
	public function columnArray(array $arr): QueryBuilder {
		foreach ($arr as $column)
			$this->columnString((string)$column);
		return $this;
	}

	/**
	 * 将data片段加入data, 返回当前对象
	 * @param string $part
	 * @return QueryBuilder
	 */
	protected function columnPush(string $part): QueryBuilder {
		$this->column = is_null($this->column) ? $part : ($this->column . ',' . $part);
		return $this;
	}

}
