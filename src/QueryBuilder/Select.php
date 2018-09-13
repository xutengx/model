<?php

declare(strict_types = 1);
namespace Xutengx\Model\QueryBuilder;

use Closure;
use Xutengx\Model\Component\QueryBuilder;

/**
 * Trait Select
 * @package Xutengx\Model\QueryBuilder
 */
trait Select {

	/**
	 * 加入不做处理的字段
	 * @param string $sql
	 * @return QueryBuilder
	 */
	public function selectRaw(string $sql): QueryBuilder {
		return $this->selectPush($sql);
	}

	/**
	 * 将一个数组加入查询
	 * @param array $arr
	 * @return QueryBuilder
	 */
	public function selectArray(array $arr): QueryBuilder {
		$str = '';
		foreach ($arr as $field) {
			$str .= $this->fieldFormat($field) . ',';
		}
		$sql = rtrim($str, ',');
		return $this->selectPush($sql);
	}

	/**
	 * 将一个string加入查询
	 * @param string $str
	 * @param string $delimiter
	 * @return QueryBuilder
	 */
	public function selectString(string $str, string $delimiter = ','): QueryBuilder {
		return $this->selectArray(explode($delimiter, $str));
	}

	/**
	 * 将db函数加入select
	 * eg : count()
	 * @param string $function
	 * @param Closure $callback
	 * @param string $alias
	 * @return QueryBuilder
	 */
	public function selectFunction(string $function, Closure $callback, string $alias = null): QueryBuilder {
		$res = $callback($QueryBuilder = $this->getSelf());
		// 调用方未调用return
		if (is_null($res)) {
			$sql = $QueryBuilder->getAllToSql();
		}
		// 调用方未调用toSql
		elseif ($res instanceof QueryBuilder) {
			$sql = $res->getAllToSql();
		}
		// 调用正常
		else
			$sql = $res;
		$aliasString = is_null($alias) ? '' : " as '$alias'";
		// 合并绑定数组
		$this->bindings += $QueryBuilder->getBindings();
		return $this->selectRaw($function . $this->bracketFormat($sql) . $aliasString);
	}

	/**
	 * 将select片段加入select, 返回当前对象
	 * @param string $part
	 * @return QueryBuilder
	 */
	protected function selectPush(string $part): QueryBuilder {
		if (is_null($this->select)) {
			$this->select = $part;
		}
		else
			$this->select .= ',' . $part;
		return $this;
	}

}
