<?php

declare(strict_types = 1);
namespace Xutengx\Model\QueryBuilder;

use Xutengx\Model\Component\QueryBuilder;

/**
 * Trait Value
 * @package Xutengx\Model\QueryBuilder
 */
trait Value {

	/**
	 * 将没有键的数组插入
	 * @param array $arr 一维数组 eg:['Alice', 18]
	 * @return QueryBuilder
	 */
	public function valueArrayWithoutColumn(array $arr): QueryBuilder {
		$str = '';
		foreach ($arr as $value)
			$str .= ',' . $this->valueFormat($value);
		return $this->valuePush(ltrim($str, ','));
	}

	/**
	 * 将与键一一对应的数组插入
	 * @param array $arr 一维数组 eg:['name'=>'Alice','age' => 18]
	 * @return QueryBuilder
	 */
	public function valueArrayWithColumn(array $arr): QueryBuilder {
		$str = '';
		foreach ($arr as $column => $value) {
			$this->columnString((string)$column);
			$str .= ',' . $this->valueFormat((string)$value);
		}
		return $this->valuePush(ltrim($str, ','));
	}

	/**
	 * 插入一维数组
	 * @param array $arr 一维数组
	 * @return QueryBuilder
	 */
	public function valueOneDimensionalArray(array $arr): QueryBuilder {
		return $this->isAssocArray($arr) ? $this->valueArrayWithoutColumn($arr) : $this->valueArrayWithColumn($arr);
	}

	/**
	 * 插入二维数组
	 * @param array $arr 二维数组
	 * @return QueryBuilder
	 */
	public function valueTwoDimensionalArray(array $arr): QueryBuilder {
		foreach ($arr as $theArray)
			$this->valueOneDimensionalArray($theArray);
		return $this;
	}

	/**
	 * 将data片段加入data, 返回当前对象
	 * @param string $part
	 * @return QueryBuilder
	 */
	protected function valuePush(string $part): QueryBuilder {
		$this->value[] = $part;
		return $this;
	}

	/**
	 * 判断数组是否是数值数组
	 * @param array $arr
	 * @return bool
	 */
	protected function isAssocArray(array $arr): bool {
		return $arr === array_values($arr);
	}

}
